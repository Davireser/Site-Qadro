<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: blog.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    flash_set('error', 'Artigo não encontrado.');
    header('Location: blog.php');
    exit;
}

$errors = [];
$input = [
    'title'     => $post['title'],
    'category'  => $post['category'],
    'summary'   => $post['summary'] ?? '',
    'tags'      => $post['tags'] ?? '',
    'slug'      => $post['slug'],
    'post_date' => $post['post_date'],
    'featured'  => (bool) $post['featured'],
];

// Blocos já salvos, no formato que o editor entende.
$blocks = [];
foreach (post_blocks($id) as $row) {
    $blocks[] = [
        'type'     => $row['block_type'],
        'content'  => $row['content'] ?? '',
        'image'    => $row['image'] ?? '',
        'caption'  => $row['caption'] ?? '',
        'settings' => post_block_settings($row['block_type'], $row['settings']),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();

    foreach ($input as $key => $default) {
        if ($key === 'featured') {
            continue;
        }
        $input[$key] = trim($_POST[$key] ?? '');
    }
    $input['featured'] = !empty($_POST['featured']);

    $parsed = post_blocks_from_request($_POST['blocks'] ?? []);
    $blocks = array_map(fn ($b) => [
        'type'     => $b['type'],
        'content'  => $b['content'] ?? '',
        'image'    => $b['image'] ?? '',
        'caption'  => $b['caption'] ?? '',
        'settings' => post_block_settings($b['type'], $b['settings']),
    ], $parsed['blocks']);

    if ($input['title'] === '') {
        $errors[] = 'Informe o título principal.';
    }
    if ($input['summary'] === '') {
        $errors[] = 'Informe o resumo do artigo.';
    }
    if (!$input['post_date'] || !DateTime::createFromFormat('Y-m-d', $input['post_date'])) {
        $errors[] = 'Informe uma data de publicação válida.';
    }
    if (!$parsed['blocks']) {
        $errors[] = 'Adicione ao menos um bloco de conteúdo ao artigo.';
    }

    $newFilename = null;
    $hasNewImage = !empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;
    if (!$errors && $hasNewImage) {
        try {
            $newFilename = handle_image_upload($_FILES['image'], 'blog');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        $slug = unique_slug('posts', $input['slug'] !== '' ? $input['slug'] : $input['title'], $id);
        $imageToSave = $newFilename ?? $post['image'];

        $upd = db()->prepare('UPDATE posts SET
            title = ?, slug = ?, category = ?, summary = ?, tags = ?, image = ?, featured = ?, post_date = ?
            WHERE id = ?');
        $upd->execute([
            $input['title'],
            $slug,
            $input['category'],
            $input['summary'],
            $input['tags'],
            $imageToSave,
            $input['featured'] ? 1 : 0,
            $input['post_date'],
            $id,
        ]);

        post_blocks_save($id, $parsed['blocks'], $parsed['usedImages']);

        if ($newFilename) {
            delete_image_file('blog', $post['image']);
        }

        flash_set('success', 'Artigo atualizado com sucesso.');
        header('Location: blog.php');
        exit;
    }

    if ($newFilename) {
        delete_image_file('blog', $newFilename);
    }
}

$pageTitle = 'Editar Artigo';
$activeNav = 'blog';
require __DIR__ . '/../includes/header.php';
?>

<?php if ($errors): ?>
  <div class="admin-alert admin-alert-error">
    <?php foreach ($errors as $err): ?>
      <div><?= e($err) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" action="blog_edit.php?id=<?= (int) $id ?>" enctype="multipart/form-data" novalidate id="blogForm">
  <?= csrf_field() ?>
  <input type="hidden" name="post_id" value="<?= (int) $id ?>">

  <?php require __DIR__ . '/blog_form_fields.php'; ?>

  <div class="admin-form-actions">
    <button type="submit" class="admin-btn">Salvar alterações</button>
    <button type="submit" class="admin-btn admin-btn-ghost" formaction="blog_preview.php" formtarget="_blank" formnovalidate>Visualizar</button>
    <a href="blog.php" class="admin-btn admin-btn-ghost">Cancelar</a>
  </div>
</form>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
<script src="../assets/js/admin-blocks.js?v=<?= asset_version('assets/js/admin-blocks.js') ?>"></script>
