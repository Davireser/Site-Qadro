<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: portfolio.php');
    exit;
}

require_valid_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = db()->prepare('SELECT image FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if ($project) {
        $gallery = db()->prepare('SELECT image FROM project_gallery WHERE project_id = ?');
        $gallery->execute([$id]);
        $galleryImages = $gallery->fetchAll(PDO::FETCH_COLUMN);

        $blockImgs = db()->prepare('SELECT image FROM project_blocks WHERE project_id = ? AND image IS NOT NULL');
        $blockImgs->execute([$id]);
        $blockImages = $blockImgs->fetchAll(PDO::FETCH_COLUMN);

        // As métricas, a galeria e os blocos saem junto pelo ON DELETE CASCADE.
        $del = db()->prepare('DELETE FROM projects WHERE id = ?');
        $del->execute([$id]);

        delete_image_file('portfolio', $project['image']);
        foreach ($galleryImages as $image) {
            delete_image_file('portfolio_gallery', $image);
        }
        foreach ($blockImages as $image) {
            delete_image_file('portfolio_blocks', $image);
        }

        flash_set('success', 'Projeto excluído.');
    }
}

header('Location: portfolio.php');
exit;
