<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: blog.php');
    exit;
}

require_valid_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = db()->prepare('SELECT image FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post) {
        // Imagens usadas pelos blocos do artigo, para limpar do disco.
        $blockImages = db()->prepare('SELECT image FROM post_blocks WHERE post_id = ? AND image IS NOT NULL');
        $blockImages->execute([$id]);
        $blockImages = $blockImages->fetchAll(PDO::FETCH_COLUMN);

        // Os blocos saem junto pelo ON DELETE CASCADE.
        $del = db()->prepare('DELETE FROM posts WHERE id = ?');
        $del->execute([$id]);

        delete_image_file('blog', $post['image']);
        foreach ($blockImages as $blockImage) {
            delete_image_file('blog_blocks', $blockImage);
        }

        flash_set('success', 'Artigo excluído.');
    }
}

header('Location: blog.php');
exit;
