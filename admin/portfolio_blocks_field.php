<?php
/**
 * Editor de blocos do projeto, usado por portfolio_add.php e portfolio_edit.php.
 * É o conteúdo que aparece abaixo do carrossel na página do projeto.
 *
 * Espera:
 *   $blocks   blocos já montados (array normalizado do editor)
 */
$blockTypes = post_block_types();
?>

<fieldset class="admin-fieldset">
  <legend class="admin-fieldset-title">Conteúdo abaixo do carrossel <span class="admin-hint">(monte a seção com blocos — arraste pela alça ou use as setas para reordenar)</span></legend>

  <div class="blocks-editor"
       id="blocksEditor"
       data-blocks="<?= e(json_encode($blocks, JSON_UNESCAPED_UNICODE)) ?>"
       data-types="<?= e(json_encode($blockTypes, JSON_UNESCAPED_UNICODE)) ?>"
       data-csrf="<?= e(csrf_token()) ?>"
       data-upload-url="portfolio_block_upload.php"
       data-image-url="portfolio_block_image.php">
    <div class="blocks-list" id="blocksList"></div>

    <div class="blocks-empty" id="blocksEmpty">
      Nenhum bloco ainda. Escolha um tipo abaixo para começar a montar a seção.
    </div>

    <div class="blocks-add-bar">
      <span class="blocks-add-label">Adicionar bloco:</span>
      <?php foreach ($blockTypes as $typeKey => $type): ?>
        <button type="button" class="blocks-add-btn" data-add-type="<?= e($typeKey) ?>" title="<?= e($type['hint']) ?>">
          <span class="blocks-add-icon"><?= e($type['icon']) ?></span>
          <?= e($type['label']) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</fieldset>
