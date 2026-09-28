<?php
/**
 * Prévia do projeto antes de publicar.
 *
 * Recebe o formulário do editor (sem gravar nada no banco) e renderiza o
 * projeto com o mesmo layout, fontes e espaçamentos da página publicada,
 * usando os mesmos arquivos do carrossel e da renderização dos blocos.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: portfolio.php');
    exit;
}

require_valid_csrf();

$projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);

$project = [
    'id'           => $projectId ?: 0,
    'title'        => trim($_POST['title'] ?? '') !== '' ? trim($_POST['title']) : 'Título do projeto',
    'summary'      => trim($_POST['summary'] ?? ''),
    'project_name' => trim($_POST['project_name'] ?? ''),
    'category'     => trim($_POST['category'] ?? ''),
    'description'  => trim($_POST['description'] ?? ''),
];

$tags        = project_tags($project['category']);
$description = text_paragraphs($project['description']);

// Métricas: mesma regra da gravação (entra quem tem valor preenchido).
$postedValues = $_POST['metric_value'] ?? [];
$postedLabels = $_POST['metric_label'] ?? [];
$metrics = [];
foreach (array_slice($postedValues, 0, PROJECT_MAX_METRICS) as $i => $value) {
    $value = trim((string) $value);
    if ($value === '') {
        continue;
    }
    $metrics[] = ['metric_value' => $value, 'metric_label' => trim((string) ($postedLabels[$i] ?? ''))];
}

$parsed = post_blocks_from_request($_POST['blocks'] ?? []);
$renderBlocks = $parsed['blocks'];

// No preview a imagem do bloco vem pelo nome do arquivo (ainda não tem id).
$renderImageBase = fn (array $block): string =>
    'admin/portfolio_block_image.php?file=' . urlencode($block['image']);

// Imagem destaque e carrossel mostram o que já está salvo. Arquivos ainda não
// enviados não têm como aparecer aqui — os avisos abaixo deixam isso claro.
$heroUrl = null;
$gallery = [];
if ($projectId) {
    $stmt = db()->prepare('SELECT image FROM projects WHERE id = ?');
    $stmt->execute([$projectId]);
    if ($stmt->fetchColumn()) {
        $heroUrl = 'includes/image.php?type=portfolio&id=' . $projectId;
    }

    // Respeita as imagens marcadas para remover no formulário.
    $removeGallery = array_map('intval', $_POST['remove_gallery'] ?? []);
    $galleryStmt = db()->prepare('SELECT id FROM project_gallery WHERE project_id = ? ORDER BY sort_order ASC, id ASC');
    $galleryStmt->execute([$projectId]);
    foreach ($galleryStmt->fetchAll() as $img) {
        if (!in_array((int) $img['id'], $removeGallery, true)) {
            $gallery[] = $img;
        }
    }
}

$othersStmt = db()->prepare('SELECT id, title, project_name, slug, category, image FROM projects WHERE id != ? ORDER BY display_order ASC, created_at DESC, id DESC LIMIT 4');
$othersStmt->execute([$projectId ?: 0]);
$others = $othersStmt->fetchAll();

$pageHtmlTitle   = 'Prévia: ' . $project['title'];
$metaDescription = mb_substr($project['summary'], 0, 160);
$activePage      = 'portfolio';
$baseHref        = '../';
require __DIR__ . '/../includes/site_header.php';
?>

<div class="preview-bar">
  <span class="preview-bar-tag">Prévia — este projeto ainda não foi salvo</span>
  <button type="button" class="preview-bar-close" onclick="window.close()">Fechar prévia</button>
</div>

<!-- Título principal + resumo do projeto -->
<section class="page-title-section">
  <div class="container">
    <h1 class="page-title project-detail-title"><?= e($project['title']) ?></h1>
    <?php if ($project['summary'] !== ''): ?>
      <p class="page-subtitle project-summary"><?= nl2br(e($project['summary'])) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section section-project-detail">
  <div class="container">
    <!-- Imagem destaque -->
    <?php if ($heroUrl): ?>
      <div class="project-detail-hero">
        <img src="<?= e($heroUrl) ?>" alt="<?= e($project['project_name'] ?: $project['title']) ?>">
      </div>
    <?php else: ?>
      <div class="preview-cover-note">
        A imagem destaque selecionada só aparece depois de salvar o projeto.
      </div>
    <?php endif; ?>

    <!-- Nome do projeto + tags | descrição do projeto + métricas ao lado -->
    <div class="project-body-grid<?= $metrics ? ' has-metrics metrics-' . count($metrics) : '' ?>">
      <div class="project-body-main">
        <h2 class="project-name"><?= e($project['project_name']) ?></h2>

        <?php if ($tags): ?>
          <div class="project-detail-tags">
            <?php foreach ($tags as $tag): ?>
              <span class="item-tag"><?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($description): ?>
          <div class="project-detail-body">
            <?php foreach ($description as $paragraph): ?>
              <p><?= nl2br(e($paragraph)) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($metrics): ?>
        <div class="project-metrics" data-count="<?= count($metrics) ?>">
          <?php foreach ($metrics as $metric): ?>
            <div class="project-metric">
              <div class="project-metric-value"><?= e($metric['metric_value']) ?></div>
              <?php if ($metric['metric_label'] !== ''): ?>
                <div class="project-metric-label"><?= e($metric['metric_label']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($gallery): ?>
      <?php require __DIR__ . '/../includes/project_carousel.php'; ?>
    <?php else: ?>
      <div class="preview-cover-note">
        As imagens do carrossel selecionadas só aparecem depois de salvar o projeto.
      </div>
    <?php endif; ?>

    <!-- Conteúdo em blocos (abaixo do carrossel) -->
    <div class="project-blocks">
      <?php if ($renderBlocks): ?>
        <?php require __DIR__ . '/../includes/post_blocks_render.php'; ?>
      <?php else: ?>
        <p>Nenhum bloco de conteúdo foi adicionado ainda.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Links para os outros projetos do portfólio -->
<?php if ($others): ?>
  <section class="section">
    <div class="container">
      <div class="related-grid-title">
        <span class="section-title-tag" style="margin-bottom:0;">Outros Projetos</span>
        <a href="portfolio.php" class="btn-link btn-ver-todos">Ver Todos</a>
      </div>
      <div class="related-projects-grid">
        <?php foreach ($others as $other): ?>
          <a href="portfolio_detalhe.php?slug=<?= urlencode($other['slug']) ?>" class="related-project-card">
            <div class="related-project-img">
              <?php if ($other['image']): ?>
                <img src="includes/image.php?type=portfolio&id=<?= (int) $other['id'] ?>" alt="<?= e($other['project_name'] ?: $other['title']) ?>">
              <?php endif; ?>
            </div>
            <h3 class="related-project-title"><?= e($other['project_name'] ?: $other['title']) ?></h3>
            <?php $otherTags = project_tags($other['category']); ?>
            <?php if ($otherTags): ?>
              <div class="related-project-tags">
                <?php foreach ($otherTags as $tag): ?>
                  <span class="item-tag"><?= e($tag) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
