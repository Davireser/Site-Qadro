<?php
/**
 * Prévia do artigo antes de publicar.
 *
 * Recebe o formulário do editor (sem gravar nada no banco) e renderiza o
 * artigo com o mesmo layout, fontes e espaçamentos da página publicada,
 * usando o mesmo arquivo de renderização dos blocos.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: blog.php');
    exit;
}

require_valid_csrf();

$postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

$post = [
    'id'        => $postId ?: 0,
    'title'     => trim($_POST['title'] ?? '') !== '' ? trim($_POST['title']) : 'Título do artigo',
    'category'  => trim($_POST['category'] ?? ''),
    'summary'   => trim($_POST['summary'] ?? ''),
    'tags'      => trim($_POST['tags'] ?? ''),
    'post_date' => trim($_POST['post_date'] ?? '') ?: date('Y-m-d'),
];

$parsed = post_blocks_from_request($_POST['blocks'] ?? []);
$renderBlocks = $parsed['blocks'];

// No preview a imagem vem pelo nome do arquivo (o bloco ainda não tem id).
$renderImageBase = function (array $block): string {
    return 'admin/blog_block_image.php?file=' . urlencode($block['image']);
};

// Capa: usa a que já está salva; se for um arquivo novo ainda não enviado,
// não há como exibi-lo aqui — o aviso abaixo deixa isso claro.
$coverUrl = null;
$coverPending = false;
if ($postId) {
    $stmt = db()->prepare('SELECT image FROM posts WHERE id = ?');
    $stmt->execute([$postId]);
    $savedCover = $stmt->fetchColumn();
    if ($savedCover) {
        $coverUrl = 'includes/image.php?type=blog&id=' . (int) $postId;
    }
}
if (!$coverUrl) {
    $coverPending = true;
}

// Artigos recentes (seção fixa do rodapé da página de artigo).
$othersStmt = db()->prepare('SELECT id, title, slug, image FROM posts WHERE id != ? ORDER BY post_date DESC, id DESC LIMIT 4');
$othersStmt->execute([$postId ?: 0]);
$others = $othersStmt->fetchAll();

$pageHtmlTitle   = 'Prévia: ' . $post['title'];
$metaDescription = mb_substr($post['summary'], 0, 160);
$activePage      = 'blog';
$baseHref        = '../';
require __DIR__ . '/../includes/site_header.php';
?>

<div class="preview-bar">
  <span class="preview-bar-tag">Prévia — este artigo ainda não foi salvo</span>
  <button type="button" class="preview-bar-close" onclick="window.close()">Fechar prévia</button>
</div>

<section class="page-title-section">
  <div class="container">
    <h1 class="page-title"><?= e($post['title']) ?></h1>
    <?php if ($post['category'] !== ''): ?>
      <span class="article-category"><?= e($post['category']) ?> &middot; <?= e(date('d/m/Y', strtotime($post['post_date']))) ?></span>
    <?php endif; ?>
    <?php if ($post['summary'] !== ''): ?>
      <p class="article-summary"><?= nl2br(e($post['summary'])) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($coverUrl): ?>
      <div class="article-image">
        <img src="<?= e($coverUrl) ?>" alt="<?= e($post['title']) ?>">
      </div>
    <?php elseif ($coverPending): ?>
      <div class="preview-cover-note">
        A imagem de capa selecionada só aparece depois de salvar o artigo.
      </div>
    <?php endif; ?>

    <?php if ($post['tags'] !== ''): ?>
      <div class="article-tags">
        <?php foreach (project_tags($post['tags']) as $tag): ?>
          <span class="item-tag"><?= e($tag) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="article-body">
      <?php if ($renderBlocks): ?>
        <?php require __DIR__ . '/../includes/post_blocks_render.php'; ?>
      <?php else: ?>
        <p>Nenhum bloco de conteúdo foi adicionado ainda.</p>
      <?php endif; ?>
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
          <a href="blog_detalhe.php?slug=<?= urlencode($other['slug'] ?? '') ?>" class="blog-card">
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

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
