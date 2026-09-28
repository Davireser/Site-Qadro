<?php
require_once __DIR__ . '/includes/functions.php';

// A ordem de exibição definida no admin determina a posição no grid alternado.
$projects = db()->query('SELECT id, title, project_name, slug, category, image FROM projects ORDER BY display_order ASC, created_at DESC, id DESC')->fetchAll();

// Monta a lista de tags únicas (o campo de tags pode conter várias, separadas por vírgula).
$allTags = [];
foreach ($projects as $project) {
    foreach (project_tags($project['category']) as $tag) {
        $allTags[$tag] = true;
    }
}
$allTags = array_keys($allTags);
sort($allTags);

// O primeiro projeto ocupa o card de destaque, mais largo, acima das colunas.
// Os demais seguem o zigue-zague: posição par à direita, ímpar à esquerda.
$highlight = $projects ? $projects[0] : null;
$leftColumn = [];
$rightColumn = [];
foreach (array_slice($projects, 1) as $index => $project) {
    $position = $index + 2; // 2º, 3º, 4º... projeto da lista
    $project['position'] = $position;
    if ($position % 2 === 0) {
        $rightColumn[] = $project;
    } else {
        $leftColumn[] = $project;
    }
}

$pageHtmlTitle   = 'Portfólio | Qadro Construtora e Projetos';
$metaDescription = 'Conheça os projetos de arquitetura, engenharia e construção realizados pela Qadro.';
$activePage      = 'portfolio';
require __DIR__ . '/includes/site_header.php';
?>

  <section class="page-title-section">
    <div class="container portfolio-header">
      <div class="portfolio-header-intro">
        <h1 class="page-title">PORTFÓLIO</h1>
        <p class="portfolio-intro">Uma seleção dos nossos projetos comerciais, industriais e residenciais, do planejamento à entrega.</p>
      </div>

      <?php if ($allTags): ?>
        <div class="portfolio-tags-block">
          <span class="portfolio-tags-label">Tags</span>
          <div class="portfolio-tags-filter" id="portfolioTagsFilter">
            <span class="filter-tag active" data-tag="">Todos</span>
            <?php foreach ($allTags as $tag): ?>
              <span class="filter-tag" data-tag="<?= e($tag) ?>"><?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php if (!$projects): ?>
        <p style="font-family: var(--font-altone); color: var(--color-gray-500);">Nenhum projeto cadastrado no momento.</p>
      <?php else: ?>
        <div id="portfolioGrid">
          <?php
          $card = $highlight;
          $cardClass = 'portfolio-card-highlight';
          $cardOrder = 1;
          require __DIR__ . '/includes/portfolio_card.php';
          ?>

          <?php if ($leftColumn || $rightColumn): ?>
            <div class="portfolio-masonry">
              <div class="portfolio-column portfolio-column-left">
                <?php foreach ($leftColumn as $card): ?>
                  <?php $cardClass = ''; $cardOrder = $card['position']; require __DIR__ . '/includes/portfolio_card.php'; ?>
                <?php endforeach; ?>
              </div>
              <div class="portfolio-column portfolio-column-right">
                <?php foreach ($rightColumn as $card): ?>
                  <?php $cardClass = ''; $cardOrder = $card['position']; require __DIR__ . '/includes/portfolio_card.php'; ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>

<script>
  (function () {
    var filterWrap = document.getElementById('portfolioTagsFilter');
    var grid = document.getElementById('portfolioGrid');
    if (!filterWrap || !grid) return;

    filterWrap.addEventListener('click', function (event) {
      var tagEl = event.target.closest('.filter-tag');
      if (!tagEl) return;

      filterWrap.querySelectorAll('.filter-tag').forEach(function (el) {
        el.classList.remove('active');
      });
      tagEl.classList.add('active');

      var tag = tagEl.getAttribute('data-tag');
      grid.querySelectorAll('.portfolio-card').forEach(function (card) {
        var cardTags = (card.getAttribute('data-tags') || '').split(',');
        card.style.display = (!tag || cardTags.indexOf(tag) !== -1) ? '' : 'none';
      });
    });
  })();
</script>
