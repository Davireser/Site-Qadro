<?php
/**
 * Campos do formulário de artigo, usados por blog_add.php e blog_edit.php.
 *
 * Espera:
 *   $input        valores dos campos fixos
 *   $blocks       blocos já montados (array normalizado do editor)
 *   $post         (opcional) artigo em edição, para mostrar a capa atual
 */
$blockTypes = post_block_types();
?>

<fieldset class="admin-fieldset">
  <legend class="admin-fieldset-title">Identificação</legend>

  <div class="admin-field">
    <label class="admin-label" for="title">Título principal <span class="admin-hint">(obrigatório)</span></label>
    <input class="admin-input" type="text" id="title" name="title" value="<?= e($input['title']) ?>" required>
  </div>

  <div class="admin-field">
    <label class="admin-label" for="category">Subtítulo / Categoria</label>
    <input class="admin-input" type="text" id="category" name="category" value="<?= e($input['category']) ?>" placeholder="Ex: Planejamento e Controle">
  </div>

  <div class="admin-field">
    <label class="admin-label" for="summary">Resumo do artigo <span class="admin-hint">(obrigatório — aparece na listagem e no topo da página)</span></label>
    <textarea class="admin-textarea admin-textarea-sm" id="summary" name="summary" required><?= e($input['summary']) ?></textarea>
  </div>

  <div class="admin-field">
    <label class="admin-label" for="image">Imagem de capa <span class="admin-hint">(JPG, PNG ou WEBP)</span></label>
    <?php if (!empty($post['image'])): ?>
      <div class="admin-current-image">
        <img src="../includes/image.php?type=blog&id=<?= (int) $post['id'] ?>" alt="">
        <span>Envie um novo arquivo abaixo para substituir</span>
      </div>
    <?php endif; ?>
    <input class="admin-file" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"<?= empty($post) ? ' required' : '' ?>>
  </div>

  <div class="admin-field">
    <label class="admin-label" for="tags">Tags <span class="admin-hint">(separadas por vírgula)</span></label>
    <input class="admin-input" type="text" id="tags" name="tags" value="<?= e($input['tags']) ?>" placeholder="Ex: Obra, Planejamento, BIM">
  </div>

  <div class="admin-field-row">
    <div class="admin-field">
      <label class="admin-label" for="slug">Slug <span class="admin-hint">(opcional)</span></label>
      <input class="admin-input" type="text" id="slug" name="slug" value="<?= e($input['slug']) ?>" placeholder="Gerado a partir do título se ficar vazio">
    </div>
    <div class="admin-field">
      <label class="admin-label" for="post_date">Data de publicação</label>
      <input class="admin-input" type="date" id="post_date" name="post_date" value="<?= e($input['post_date']) ?>" required>
    </div>
  </div>

  <div class="admin-field admin-checkbox-field">
    <label class="admin-checkbox-label">
      <input type="checkbox" name="featured" value="1" <?= $input['featured'] ? 'checked' : '' ?>>
      Artigo em destaque
    </label>
  </div>
</fieldset>

<fieldset class="admin-fieldset">
  <legend class="admin-fieldset-title">Corpo do artigo <span class="admin-hint">(monte a página com blocos — arraste pela alça ou use as setas para reordenar)</span></legend>

  <div class="blocks-editor"
       id="blocksEditor"
       data-blocks="<?= e(json_encode($blocks, JSON_UNESCAPED_UNICODE)) ?>"
       data-types="<?= e(json_encode($blockTypes, JSON_UNESCAPED_UNICODE)) ?>"
       data-csrf="<?= e(csrf_token()) ?>">
    <div class="blocks-list" id="blocksList"></div>

    <div class="blocks-empty" id="blocksEmpty">
      Nenhum bloco ainda. Escolha um tipo abaixo para começar a montar o artigo.
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
