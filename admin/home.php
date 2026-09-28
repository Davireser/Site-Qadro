<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$postCount    = (int) db()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
$projectCount = (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn();

$pageTitle = 'Dashboard';
$activeNav = 'home';
require __DIR__ . '/../includes/header.php';
?>

<div class="admin-content-header">
  <p style="font-family: var(--font-altone); color: var(--color-gray-500); font-size: 0.9rem;">
    Bem-vindo(a), <?= e(current_admin_username()) ?>.
  </p>
</div>

<div class="admin-table-wrap" style="margin-bottom: 24px;">
  <table class="admin-table">
    <tbody>
      <tr>
        <td>Posts do blog</td>
        <td style="text-align:right; font-weight:600;"><?= $postCount ?></td>
        <td style="text-align:right;"><a href="blog.php" class="admin-btn admin-btn-ghost admin-btn-sm">Gerenciar</a></td>
      </tr>
      <tr>
        <td>Projetos do portfólio</td>
        <td style="text-align:right; font-weight:600;"><?= $projectCount ?></td>
        <td style="text-align:right;"><a href="portfolio.php" class="admin-btn admin-btn-ghost admin-btn-sm">Gerenciar</a></td>
      </tr>
    </tbody>
  </table>
</div>

<div style="display:flex; gap:14px;">
  <a href="blog_add.php" class="admin-btn">Novo post</a>
  <a href="portfolio_add.php" class="admin-btn admin-btn-ghost">Novo projeto</a>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
