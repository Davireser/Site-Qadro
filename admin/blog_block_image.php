<?php
/**
 * Serve a imagem de um bloco pelo nome do arquivo, para o editor e a prévia.
 *
 * Diferente do includes/image.php público (que resolve o arquivo pelo ID no
 * banco), aqui o nome vem do formulário — por isso exige admin autenticado e
 * passa por basename(), que impede sair da pasta de uploads (path traversal).
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$file = basename((string) ($_GET['file'] ?? ''));

if ($file === '' || !preg_match('/^[A-Za-z0-9_.-]+$/', $file)) {
    http_response_code(400);
    exit('Requisição inválida.');
}

$path = UPLOAD_BASE_DIR . '/blog_blocks/' . $file;

if (!is_file($path)) {
    http_response_code(404);
    exit('Imagem não encontrada.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];

if (!isset($mimeMap[$ext])) {
    http_response_code(404);
    exit('Imagem não encontrada.');
}

header('Content-Type: ' . $mimeMap[$ext]);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
