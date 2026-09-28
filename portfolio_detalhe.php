<?php
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: portfolio.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM projects WHERE slug = ?');
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
}

if ($project) {
    $tags = project_tags($project['category']);

    $metricsStmt = db()->prepare('SELECT metric_value, metric_label FROM project_metrics WHERE project_id = ? ORDER BY sort_order ASC, id ASC LIMIT ' . PROJECT_MAX_METRICS);
    $metricsStmt->execute([$project['id']]);
    $metrics = $metricsStmt->fetchAll();

    $galleryStmt = db()->prepare('SELECT id FROM project_gallery WHERE project_id = ? ORDER BY sort_order ASC, id ASC');
    $galleryStmt->execute([$project['id']]);
    $gallery = $galleryStmt->fetchAll();

    $othersStmt = db()->prepare('SELECT id, title, project_name, slug, category, image FROM projects WHERE id != ? ORDER BY display_order ASC, created_at DESC, id DESC LIMIT 4');
    $othersStmt->execute([$project['id']]);
    $others = $othersStmt->fetchAll();

    $description = text_paragraphs($project['description']);

    // Conteúdo abaixo do carrossel, montado no admin com o editor de blocos.
    $projectBlocks = post_blocks($project['id'], 'project');
}

$pageHtmlTitle   = $project ? ($project['title'] . ' | Portfólio Qadro') : 'Projeto não encontrado | Qadro';
$metaDescription = $project ? mb_substr(strip_tags($project['summary']), 0, 160) : 'Projeto não encontrado.';
$metaKeywords    = $project ? implode(', ', $tags) : '';
$activePage      = 'portfolio';
require __DIR__ . '/includes/site_header.php';
?>

<?php if (!$project): ?>
  <section class="page-title-section">
    <div class="container">
      <h1 class="page-title">Projeto não<br>encontrado</h1>
      <p class="page-subtitle"><a href="portfolio.php" class="btn-link">Voltar ao portfólio</a></p>
    </div>
  </section>
<?php else: ?>

  <!-- Título principal + resumo do projeto -->
  <section class="page-title-section">
    <div class="container">
      <h1 class="page-title project-detail-title"><?= e($project['title']) ?></h1>
      <p class="page-subtitle project-summary"><?= nl2br(e($project['summary'])) ?></p>
    </div>
  </section>

  <section class="section section-project-detail">
    <div class="container">
      <!-- Imagem destaque -->
      <?php if ($project['image']): ?>
        <div class="project-detail-hero">
          <img src="includes/image.php?type=portfolio&id=<?= (int) $project['id'] ?>" alt="<?= e($project['project_name'] ?: $project['title']) ?>">
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

      <?php require __DIR__ . '/includes/project_carousel.php'; ?>

      <!-- Conteúdo em blocos (abaixo do carrossel) -->
      <?php if ($projectBlocks): ?>
        <div class="project-blocks">
          <?php
          $renderBlocks = $projectBlocks;
          $renderImageBase = fn (array $block): string => 'includes/image.php?type=portfolio_block&id=' . (int) $block['id'];
          require __DIR__ . '/includes/post_blocks_render.php';
          ?>
        </div>
      <?php endif; ?>
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

<?php endif; ?>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
