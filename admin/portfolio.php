<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Descarta imagens de bloco que ficaram para trás em edições abandonadas.
post_blocks_cleanup_orphan_images(86400, 'project');

$projects = db()->query('SELECT id, title, project_name, category, image, featured, created_at FROM projects ORDER BY display_order ASC, created_at DESC, id DESC')->fetchAll();

$pageTitle = 'Portfólio';
$activeNav = 'portfolio';
require __DIR__ . '/../includes/header.php';
?>

<div class="admin-content-header">
  <p style="font-family: var(--font-altone); color: var(--color-gray-500); font-size: 0.9rem;">
    <?= count($projects) ?> projeto(s) cadastrado(s)
  </p>
  <a href="portfolio_add.php" class="admin-btn">Novo projeto</a>
</div>

<div class="admin-table-wrap">
  <?php if (!$projects): ?>
    <div class="admin-empty">Nenhum projeto cadastrado ainda.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Imagem</th>
          <th>Título principal</th>
          <th>Nome do projeto</th>
          <th>Tags</th>
          <th>Destaque</th>
          <th>Cadastro</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $project): ?>
          <tr>
            <td>
              <?php if ($project['image']): ?>
                <img class="admin-table-thumb" src="../includes/image.php?type=portfolio&id=<?= (int) $project['id'] ?>" alt="">
              <?php endif; ?>
            </td>
            <td><?= e($project['title']) ?></td>
            <td><?= e($project['project_name']) ?></td>
            <td><?= e($project['category']) ?></td>
            <td><?= $project['featured'] ? '<span class="admin-badge admin-badge-featured">Sim</span>' : '—' ?></td>
            <td><?= e(date('d/m/Y', strtotime($project['created_at']))) ?></td>
            <td>
              <div class="admin-table-actions">
                <a class="admin-btn admin-btn-ghost admin-btn-sm" href="portfolio_edit.php?id=<?= (int) $project['id'] ?>">Editar</a>
                <form method="post" action="portfolio_delete.php" onsubmit="return confirm('Excluir este projeto? Esta ação não pode ser desfeita.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
                  <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Excluir</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
