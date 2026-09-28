<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Descarta imagens de bloco que ficaram para trás em edições abandonadas.
post_blocks_cleanup_orphan_images();

$posts = db()->query('SELECT p.id, p.title, p.category, p.post_date, p.image, p.featured,
    (SELECT COUNT(*) FROM post_blocks b WHERE b.post_id = p.id) AS block_count
    FROM posts p ORDER BY p.post_date DESC, p.id DESC')->fetchAll();

$pageTitle = 'Artigos';
$activeNav = 'blog';
require __DIR__ . '/../includes/header.php';
?>

<div class="admin-content-header">
  <p style="font-family: var(--font-altone); color: var(--color-gray-500); font-size: 0.9rem;">
    <?= count($posts) ?> artigo(s) cadastrado(s)
  </p>
  <a href="blog_add.php" class="admin-btn">Novo artigo</a>
</div>

<div class="admin-table-wrap">
  <?php if (!$posts): ?>
    <div class="admin-empty">Nenhum artigo cadastrado ainda.</div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Capa</th>
          <th>Título</th>
          <th>Categoria</th>
          <th>Blocos</th>
          <th>Destaque</th>
          <th>Data</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td>
              <?php if ($post['image']): ?>
                <img class="admin-table-thumb" src="../includes/image.php?type=blog&id=<?= (int) $post['id'] ?>" alt="">
              <?php endif; ?>
            </td>
            <td><?= e($post['title']) ?></td>
            <td><?= e($post['category']) ?></td>
            <td><?= (int) $post['block_count'] ?></td>
            <td><?= $post['featured'] ? '<span class="admin-badge admin-badge-featured">Sim</span>' : '—' ?></td>
            <td><?= e(date('d/m/Y', strtotime($post['post_date']))) ?></td>
            <td>
              <div class="admin-table-actions">
                <a class="admin-btn admin-btn-ghost admin-btn-sm" href="blog_edit.php?id=<?= (int) $post['id'] ?>">Editar</a>
                <form method="post" action="blog_delete.php" onsubmit="return confirm('Excluir este artigo e todos os seus blocos? Esta ação não pode ser desfeita.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
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
