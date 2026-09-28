<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$errors = [];
$input = [
    'title'         => '',
    'project_name'  => '',
    'slug'          => '',
    'category'      => '',
    'display_order' => '0',
    'featured'      => false,
    'summary'       => '',
    'description'   => '',
];

// Métricas: até PROJECT_MAX_METRICS pares "valor + rótulo".
$metrics = array_fill(0, PROJECT_MAX_METRICS, ['value' => '', 'label' => '']);

// Blocos do conteúdo abaixo do carrossel.
$blocks = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();

    foreach ($input as $key => $default) {
        if ($key === 'featured') {
            continue;
        }
        $input[$key] = trim($_POST[$key] ?? '');
    }
    $input['featured'] = !empty($_POST['featured']);

    $postedValues = $_POST['metric_value'] ?? [];
    $postedLabels = $_POST['metric_label'] ?? [];
    foreach ($metrics as $i => $metric) {
        $metrics[$i] = [
            'value' => trim($postedValues[$i] ?? ''),
            'label' => trim($postedLabels[$i] ?? ''),
        ];
    }
    $filledMetrics = array_values(array_filter($metrics, fn ($m) => $m['value'] !== ''));

    $parsed = post_blocks_from_request($_POST['blocks'] ?? []);
    $blocks = array_map(fn ($b) => [
        'type'     => $b['type'],
        'content'  => $b['content'] ?? '',
        'image'    => $b['image'] ?? '',
        'caption'  => $b['caption'] ?? '',
        'settings' => post_block_settings($b['type'], $b['settings']),
    ], $parsed['blocks']);

    if ($input['title'] === '') {
        $errors[] = 'Informe o título principal.';
    }
    if ($input['summary'] === '') {
        $errors[] = 'Informe o resumo do projeto.';
    }
    if ($input['project_name'] === '') {
        $errors[] = 'Informe o nome do projeto.';
    }
    if ($input['display_order'] !== '' && filter_var($input['display_order'], FILTER_VALIDATE_INT) === false) {
        $errors[] = 'Ordem de exibição deve ser um número inteiro.';
    }
    if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Envie a imagem destaque do projeto.';
    }

    $filename = null;
    $galleryUploads = [];

    if (!$errors) {
        try {
            $filename = handle_image_upload($_FILES['image'], 'portfolio');
            foreach (normalize_file_array($_FILES['gallery_images'] ?? null) as $file) {
                $galleryUploads[] = handle_image_upload($file, 'portfolio_gallery');
            }
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors) {
        $slug = unique_slug('projects', $input['slug'] !== '' ? $input['slug'] : $input['title']);

        $stmt = db()->prepare('INSERT INTO projects
            (title, slug, summary, image, project_name, category, description, featured, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['title'],
            $slug,
            $input['summary'],
            $filename,
            $input['project_name'],
            $input['category'],
            $input['description'] !== '' ? $input['description'] : null,
            $input['featured'] ? 1 : 0,
            $input['display_order'] !== '' ? (int) $input['display_order'] : 0,
        ]);

        $projectId = (int) db()->lastInsertId();

        $insMetric = db()->prepare('INSERT INTO project_metrics (project_id, metric_value, metric_label, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($filledMetrics as $i => $metric) {
            $insMetric->execute([$projectId, $metric['value'], $metric['label'], $i]);
        }

        $insImage = db()->prepare('INSERT INTO project_gallery (project_id, image, sort_order) VALUES (?, ?, ?)');
        foreach ($galleryUploads as $i => $galleryFilename) {
            $insImage->execute([$projectId, $galleryFilename, $i]);
        }

        post_blocks_save($projectId, $parsed['blocks'], $parsed['usedImages'], 'project');

        flash_set('success', 'Projeto cadastrado com sucesso.');
        header('Location: portfolio.php');
        exit;
    }

    // Deu erro depois de subir arquivos: remove o que já foi gravado em disco.
    if ($filename) {
        delete_image_file('portfolio', $filename);
    }
    foreach ($galleryUploads as $galleryFilename) {
        delete_image_file('portfolio_gallery', $galleryFilename);
    }
}

$pageTitle = 'Novo Projeto';
$activeNav = 'portfolio';
require __DIR__ . '/../includes/header.php';
?>

<?php if ($errors): ?>
  <div class="admin-alert admin-alert-error">
    <?php foreach ($errors as $err): ?>
      <div><?= e($err) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" action="portfolio_add.php" enctype="multipart/form-data" novalidate id="portfolioForm">
  <?= csrf_field() ?>

  <fieldset class="admin-fieldset">
    <legend class="admin-fieldset-title">Topo da página</legend>

    <div class="admin-field">
      <label class="admin-label" for="title">Título principal <span class="admin-hint">(obrigatório)</span></label>
      <input class="admin-input" type="text" id="title" name="title" value="<?= e($input['title']) ?>" placeholder="Ex: Modern Single House" required>
    </div>

    <div class="admin-field">
      <label class="admin-label" for="summary">Resumo do projeto <span class="admin-hint">(obrigatório — aparece logo abaixo do título)</span></label>
      <textarea class="admin-textarea admin-textarea-sm" id="summary" name="summary" required><?= e($input['summary']) ?></textarea>
    </div>

    <div class="admin-field">
      <label class="admin-label" for="image">Imagem destaque <span class="admin-hint">(JPG, PNG ou WEBP)</span></label>
      <input class="admin-file" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
    </div>

    <div class="admin-field">
      <label class="admin-label" for="project_name">Nome do projeto <span class="admin-hint">(obrigatório)</span></label>
      <input class="admin-input" type="text" id="project_name" name="project_name" value="<?= e($input['project_name']) ?>" placeholder="Ex: GBN Apt Rezende" required>
    </div>

    <div class="admin-field">
      <label class="admin-label" for="category">Tags <span class="admin-hint">(separadas por vírgula — são os filtros da página do portfólio)</span></label>
      <input class="admin-input" type="text" id="category" name="category" value="<?= e($input['category']) ?>" placeholder="Ex: Projeto, Interiores, Retrofit">
    </div>
  </fieldset>

  <fieldset class="admin-fieldset">
    <legend class="admin-fieldset-title">Descrição e métricas</legend>

    <div class="admin-field">
      <label class="admin-label" for="description">Descrição do projeto</label>
      <textarea class="admin-textarea" id="description" name="description" placeholder="Linha em branco separa os parágrafos."><?= e($input['description']) ?></textarea>
    </div>

    <div class="admin-field">
      <label class="admin-label">Métricas <span class="admin-hint">(até <?= PROJECT_MAX_METRICS ?>, exibidas ao lado da descrição — a página se ajusta à quantidade preenchida)</span></label>
      <div class="admin-metrics-grid">
        <?php foreach ($metrics as $i => $metric): ?>
          <div class="admin-metric-row">
            <span class="admin-metric-index"><?= $i + 1 ?></span>
            <input class="admin-input" type="text" name="metric_value[]" value="<?= e($metric['value']) ?>" maxlength="50" placeholder="Valor — ex: R$ 5M, 1.200 m², 18 meses">
            <input class="admin-input" type="text" name="metric_label[]" value="<?= e($metric['label']) ?>" maxlength="80" placeholder="Rótulo — ex: Investimento">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </fieldset>

  <fieldset class="admin-fieldset">
    <legend class="admin-fieldset-title">Carrossel de imagens</legend>

    <div class="admin-field">
      <label class="admin-label" for="gallery_images">Imagens do carrossel <span class="admin-hint">(pode selecionar várias — a ordem de envio é a ordem de exibição)</span></label>
      <input class="admin-file" type="file" id="gallery_images" name="gallery_images[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
    </div>
  </fieldset>

  <?php require __DIR__ . '/portfolio_blocks_field.php'; ?>

  <fieldset class="admin-fieldset">
    <legend class="admin-fieldset-title">Publicação</legend>

    <div class="admin-field-row">
      <div class="admin-field">
        <label class="admin-label" for="slug">Slug <span class="admin-hint">(opcional)</span></label>
        <input class="admin-input" type="text" id="slug" name="slug" value="<?= e($input['slug']) ?>" placeholder="Gerado a partir do título se ficar vazio">
      </div>
      <div class="admin-field">
        <label class="admin-label" for="display_order">Ordem de exibição</label>
        <input class="admin-input" type="number" step="1" id="display_order" name="display_order" value="<?= e($input['display_order']) ?>">
      </div>
    </div>

    <div class="admin-field admin-checkbox-field">
      <label class="admin-checkbox-label">
        <input type="checkbox" name="featured" value="1" <?= $input['featured'] ? 'checked' : '' ?>>
        Projeto em destaque (aparece na seção de destaques da home)
      </label>
    </div>
  </fieldset>

  <div class="admin-form-actions">
    <button type="submit" class="admin-btn">Salvar projeto</button>
    <button type="submit" class="admin-btn admin-btn-ghost" formaction="portfolio_preview.php" formtarget="_blank" formnovalidate>Visualizar</button>
    <a href="portfolio.php" class="admin-btn admin-btn-ghost">Cancelar</a>
  </div>
</form>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
<script src="../assets/js/admin-blocks.js?v=<?= asset_version('assets/js/admin-blocks.js') ?>"></script>
