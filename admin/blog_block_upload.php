<?php
/**
 * Recebe a imagem de um bloco assim que o usuário a escolhe no editor.
 * O arquivo é salvo imediatamente e o nome volta para o formulário, o que
 * permite que a prévia mostre a imagem antes de o artigo ser salvo.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    echo json_encode(['error' => 'Sessão expirada. Recarregue a página e tente novamente.']);
    exit;
}

try {
    $filename = handle_image_upload($_FILES['image'] ?? [], 'blog_blocks');
} catch (RuntimeException $e) {
    http_response_code(422);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

echo json_encode([
    'filename' => $filename,
    'url'      => 'blog_block_image.php?file=' . urlencode($filename),
]);
