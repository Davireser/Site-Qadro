<?php
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: blog.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM posts WHERE slug = ?');
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
}

if ($post) {
    $renderBlocks = post_blocks((int) $post['id']);
    $postTags = project_tags($post['tags'] ?? '');

    $othersStmt = db()->prepare('SELECT id, title, slug, category, image FROM posts WHERE id != ? ORDER BY post_date DESC, id DESC LIMIT 4');
    $othersStmt->execute([$post['id']]);
    $others = $othersStmt->fetchAll();
}

$pageHtmlTitle   = $post ? $post['title'] . ' | Blog Qadro' : 'Artigo não encontrado | Qadro';
$metaDescription = $post ? mb_substr(strip_tags($post['summary'] ?? ''), 0, 160) : 'Artigo não encontrado.';
$metaKeywords    = $post ? implode(', ', $postTags) : '';
$activePage      = 'blog';
require __DIR__ . '/includes/site_header.php';
?>

<?php if (!$post): ?>
  <section class="page-title-section">
    <div class="container">
      <h1 class="page-title">Artigo não<br>encontrado</h1>
      <p class="page-subtitle"><a href="blog.php" class="btn-link">Voltar ao blog</a></p>
    </div>
  </section>
<?php else: ?>

  <section class="page-title-section">
    <div class="container">
      <h1 class="page-title"><?= e($post['title']) ?></h1>
      <?php if ($post['category'] !== ''): ?>
        <span class="article-category"><?= e($post['category']) ?> &middot; <?= e(date('d/m/Y', strtotime($post['post_date']))) ?></span>
      <?php endif; ?>
      <?php if (!empty($post['summary'])): ?>
        <p class="article-summary"><?= nl2br(e($post['summary'])) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php if ($post['image']): ?>
        <div class="article-image">
          <img src="includes/image.php?type=blog&id=<?= (int) $post['id'] ?>" alt="<?= e($post['title']) ?>">
        </div>
      <?php endif; ?>

      <?php if ($postTags): ?>
        <div class="article-tags">
          <?php foreach ($postTags as $tag): ?>
            <span class="item-tag"><?= e($tag) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Corpo montado no editor de blocos do painel admin -->
      <div class="article-body">
        <?php require __DIR__ . '/includes/post_blocks_render.php'; ?>
      </div>
    </div>
  </section>

  <?php if ($others): ?>
    <section class="section">
      <div class="container">
        <div class="related-grid-title">
          <span class="section-title-tag" style="margin-bottom:0;">Artigos Recentes</span>
          <a href="blog.php" class="btn-link btn-ver-todos">Ver Todos</a>
        </div>
        <div class="blog-grid">
          <?php foreach ($others as $other): ?>
            <a href="blog_detalhe.php?slug=<?= urlencode($other['slug']) ?>" class="blog-card">
              <div class="blog-img-wrapper">
                <?php if ($other['image']): ?>
                  <img src="includes/image.php?type=blog&id=<?= (int) $other['id'] ?>" alt="<?= e($other['title']) ?>">
                <?php endif; ?>
              </div>
              <h3 class="blog-title"><?= e($other['title']) ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
