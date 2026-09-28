<?php
require_once __DIR__ . '/includes/functions.php';

const BLOG_PER_PAGE = 6;

$totalPosts = (int) db()->query('SELECT COUNT(*) FROM posts')->fetchColumn();

// Artigo em destaque: escolha do administrador; sem nenhum marcado, usa o mais recente.
$featured = db()->query('SELECT id, title, slug, category, image, post_date FROM posts WHERE featured = 1 ORDER BY post_date DESC, id DESC LIMIT 1')->fetch();
if (!$featured) {
    $featured = db()->query('SELECT id, title, slug, category, image, post_date FROM posts ORDER BY post_date DESC, id DESC LIMIT 1')->fetch();
}

$totalListPosts = max(0, $totalPosts - ($featured ? 1 : 0));
$totalPages = max(1, (int) ceil($totalListPosts / BLOG_PER_PAGE));

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
if ($page < 1) {
    $page = 1;
}
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * BLOG_PER_PAGE;

$posts = [];
if ($featured) {
    $stmt = db()->prepare('SELECT id, title, slug, category, summary, image, post_date FROM posts WHERE id != ? ORDER BY post_date DESC, id DESC LIMIT ' . BLOG_PER_PAGE . ' OFFSET ' . (int) $offset);
    $stmt->execute([$featured['id']]);
    $posts = $stmt->fetchAll();
}

/** Encurta o resumo do artigo para a listagem. */
function blog_excerpt(?string $summary, int $length = 140): string
{
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags($summary ?? '')));
    if (mb_strlen($plain) <= $length) {
        return $plain;
    }
    return mb_substr($plain, 0, $length) . '...';
}

$pageHtmlTitle   = 'Blog | Qadro Construtora e Projetos';
$metaDescription = 'Artigos sobre arquitetura, engenharia e construção pela Qadro.';
$activePage      = 'blog';
require __DIR__ . '/includes/site_header.php';
?>

  <section class="page-title-section">
    <div class="container">
      <h1 class="page-title">BLOG</h1>
      <p class="page-subtitle">Conteúdo sobre projetos, orçamentação e execução de obras, direto da equipe Qadro.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php if (!$featured && !$posts): ?>
        <p style="font-family: var(--font-altone); color: var(--color-gray-500);">Nenhum post publicado no momento.</p>
      <?php endif; ?>

      <?php if ($featured): ?>
        <span class="blog-featured-label">Último Artigo</span>
        <a href="blog_detalhe.php?slug=<?= urlencode($featured['slug']) ?>" class="blog-featured">
          <?php if ($featured['image']): ?>
            <div class="blog-featured-img">
              <img src="includes/image.php?type=blog&id=<?= (int) $featured['id'] ?>" alt="<?= e($featured['title']) ?>">
            </div>
          <?php endif; ?>
          <div class="blog-featured-footer">
            <div>
              <h2 class="blog-featured-title"><?= e($featured['title']) ?></h2>
              <span class="blog-featured-meta"><?= e($featured['category']) ?></span>
              <span class="blog-featured-date"><?= e(date('d/m/Y', strtotime($featured['post_date']))) ?></span>
            </div>
            <span class="blog-list-more">Ler mais</span>
          </div>
        </a>
      <?php endif; ?>

      <?php if ($posts): ?>
        <span class="blog-list-label">Todos Artigos</span>
        <?php foreach ($posts as $post): ?>
          <a href="blog_detalhe.php?slug=<?= urlencode($post['slug']) ?>" class="blog-list-item">
            <div class="blog-list-thumb">
              <?php if ($post['image']): ?>
                <img src="includes/image.php?type=blog&id=<?= (int) $post['id'] ?>" alt="<?= e($post['title']) ?>">
              <?php endif; ?>
            </div>
            <div>
              <span class="blog-list-date"><?= e(date('d/m/Y', strtotime($post['post_date']))) ?></span>
              <h3 class="blog-list-title"><?= e($post['title']) ?></h3>
              <p class="blog-list-excerpt"><?= e(blog_excerpt($post['summary'])) ?></p>
              <span class="blog-list-more">Ler mais</span>
            </div>
          </a>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
          <nav class="blog-pagination">
            <?php if ($page > 1): ?>
              <a href="?page=<?= $page - 1 ?>">‹</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
              <?php else: ?>
                <a href="?page=<?= $p ?>"><?= $p ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
              <a href="?page=<?= $page + 1 ?>">›</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
