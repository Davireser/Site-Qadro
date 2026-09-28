<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$errors = [];
$input = [
    'title'     => '',
    'category'  => '',
    'summary'   => '',
    'tags'      => '',
    'slug'      => '',
    'post_date' => date('Y-m-d'),
    'featured'  => false,
];
$blocks = [];

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
    if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Envie a imagem de capa do artigo.';
    }

    $filename = null;
    if (!$errors) {
        try {
            $filename = handle_image_upload($_FILES['image'], 'blog');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        $slug = unique_slug('posts', $input['slug'] !== '' ? $input['slug'] : $input['title']);

        $stmt = db()->prepare('INSERT INTO posts (title, slug, category, summary, tags, image, featured, post_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['title'],
            $slug,
            $input['category'],
            $input['summary'],
            $input['tags'],
            $filename,
            $input['featured'] ? 1 : 0,
            $input['post_date'],
        ]);

        $postId = (int) db()->lastInsertId();
        post_blocks_save($postId, $parsed['blocks'], $parsed['usedImages']);

        flash_set('success', 'Artigo cadastrado com sucesso.');
        header('Location: blog.php');
        exit;
    }

    if ($filename) {
        delete_image_file('blog', $filename);
    }
}

$pageTitle = 'Novo Artigo';
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

<form method="post" action="blog_add.php" enctype="multipart/form-data" novalidate id="blogForm">
  <?= csrf_field() ?>

  <?php require __DIR__ . '/blog_form_fields.php'; ?>

  <div class="admin-form-actions">
    <button type="submit" class="admin-btn">Salvar artigo</button>
    <button type="submit" class="admin-btn admin-btn-ghost" formaction="blog_preview.php" formtarget="_blank" formnovalidate>Visualizar</button>
    <a href="blog.php" class="admin-btn admin-btn-ghost">Cancelar</a>
  </div>
</form>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
<script src="../assets/js/admin-blocks.js?v=<?= asset_version('assets/js/admin-blocks.js') ?>"></script>
