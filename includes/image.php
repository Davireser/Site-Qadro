<?php
/**
 * Serve imagens de posts/projetos armazenadas fora da pasta pública.
 * O nome do arquivo NUNCA vem do cliente: é sempre resolvido a partir do
 * ID via consulta preparada, o que evita path traversal / LFI.
 *
 * Uso: includes/image.php?type=blog&id=5
 *      includes/image.php?type=blog_block&id=<id da linha em post_blocks>
 *      includes/image.php?type=portfolio&id=12
 *      includes/image.php?type=portfolio_gallery&id=<id da linha em project_gallery>
 *      includes/image.php?type=portfolio_block&id=<id da linha em project_blocks>
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

$type = $_GET['type'] ?? '';
$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// [tabela, coluna, pasta de upload] — sempre uma constante fixa deste array, nunca vinda do cliente.
$sources = [
    'blog'              => ['posts', 'image', 'blog'],
    'blog_block'        => ['post_blocks', 'image', 'blog_blocks'],
    'portfolio'         => ['projects', 'image', 'portfolio'],
    'portfolio_gallery' => ['project_gallery', 'image', 'portfolio_gallery'],
    'portfolio_block'   => ['project_blocks', 'image', 'portfolio_blocks'],
];

if (!isset($sources[$type]) || !$id) {
    http_response_code(400);
    exit('Requisição inválida.');
}

[$table, $column, $folder] = $sources[$type];

$stmt = db()->prepare("SELECT `$column` AS image FROM `$table` WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || empty($row['image'])) {
    http_response_code(404);
    exit('Imagem não encontrada.');
}

$path = UPLOAD_BASE_DIR . '/' . $folder . '/' . basename($row['image']);

if (!is_file($path)) {
    http_response_code(404);
    exit('Imagem não encontrada.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
$contentType = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($path));
header('Cache-Control: public, max-age=86400');
readfile($path);
