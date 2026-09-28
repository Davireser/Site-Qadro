<?php
/**
 * Renderiza o corpo de um artigo a partir dos seus blocos.
 *
 * Espera:
 *   $renderBlocks    array de blocos (colunas de post_blocks ou array equivalente)
 *   $renderImageBase (opcional) como montar a URL da imagem de um bloco.
 *                    Recebe o bloco e devolve a URL; se não vier, usa o
 *                    includes/image.php pelo id da linha (site publicado).
 *
 * O mesmo arquivo é usado pela página pública e pela prévia do admin, para
 * que o que o usuário vê antes de publicar seja exatamente o resultado final.
 */

if (!isset($renderImageBase) || !is_callable($renderImageBase)) {
    $renderImageBase = function (array $block): string {
        return 'includes/image.php?type=blog_block&id=' . (int) $block['id'];
    };
}
?>
<?php foreach ($renderBlocks as $block): ?>
  <?php
  $blockType = $block['block_type'] ?? $block['type'] ?? '';
  $settings  = post_block_settings($blockType, $block['settings'] ?? null);
  $imageUrl  = !empty($block['image']) ? $renderImageBase($block) : '';
  ?>

  <?php if ($blockType === 'text'): ?>
    <div class="post-block post-block-text post-block-text-<?= e($settings['width']) ?>">
      <?php foreach (text_paragraphs($block['content'] ?? '') as $paragraph): ?>
        <p><?= nl2br(e($paragraph)) ?></p>
      <?php endforeach; ?>
    </div>

  <?php elseif ($blockType === 'image' && $imageUrl !== ''): ?>
    <figure class="post-block post-block-image post-block-image-<?= e($settings['width']) ?> post-block-align-<?= e($settings['align']) ?>">
      <img src="<?= e($imageUrl) ?>" alt="<?= e($block['caption'] ?? '') ?>">
      <?php if (!empty($block['caption'])): ?>
        <figcaption><?= e($block['caption']) ?></figcaption>
      <?php endif; ?>
    </figure>

  <?php elseif ($blockType === 'split'): ?>
    <div class="post-block post-block-split post-block-split-<?= e($settings['ratio']) ?><?= $settings['invert'] === '1' ? ' post-block-split-invert' : '' ?>">
      <div class="post-block-split-img">
        <?php if ($imageUrl !== ''): ?>
          <img src="<?= e($imageUrl) ?>" alt="">
        <?php endif; ?>
      </div>
      <div class="post-block-split-text">
        <?php foreach (text_paragraphs($block['content'] ?? '') as $paragraph): ?>
          <p><?= nl2br(e($paragraph)) ?></p>
        <?php endforeach; ?>
      </div>
    </div>

  <?php elseif ($blockType === 'quote'): ?>
    <blockquote class="post-block post-block-quote">
      <?= nl2br(e($block['content'] ?? '')) ?>
    </blockquote>

  <?php elseif ($blockType === 'spacer'): ?>
    <div class="post-block post-block-spacer post-block-spacer-<?= e($settings['size']) ?>"></div>

  <?php endif; ?>
<?php endforeach; ?>
