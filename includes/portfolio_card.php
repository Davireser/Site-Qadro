<?php
/**
 * Card de projeto da página do portfólio.
 * Espera $card (linha da tabela projects) e, opcionalmente, $cardClass e
 * $cardOrder — a posição do projeto na lista, usada para remontar a ordem
 * correta quando as duas colunas viram uma só no mobile.
 */
$cardTags = project_tags($card['category']);
?>
<a href="portfolio_detalhe.php?slug=<?= urlencode($card['slug']) ?>"
   class="portfolio-card<?= !empty($cardClass) ? ' ' . $cardClass : '' ?>"
   <?= isset($cardOrder) ? 'style="order: ' . (int) $cardOrder . ';"' : '' ?>
   data-tags="<?= e(implode(',', $cardTags)) ?>">
  <div class="portfolio-card-img">
    <?php if ($card['image']): ?>
      <img src="includes/image.php?type=portfolio&id=<?= (int) $card['id'] ?>" alt="<?= e($card['project_name'] ?: $card['title']) ?>">
    <?php endif; ?>
  </div>

  <div class="portfolio-card-head">
    <h3 class="portfolio-card-title"><?= e($card['project_name'] ?: $card['title']) ?></h3>
    <span class="portfolio-card-arrow" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 4 17 12 9 20"></polyline></svg>
    </span>
  </div>

  <?php if ($cardTags): ?>
    <div class="portfolio-card-tags">
      <?php foreach ($cardTags as $cardTag): ?>
        <span class="item-tag"><?= e($cardTag) ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</a>
