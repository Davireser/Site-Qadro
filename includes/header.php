<?php
/**
 * Layout comum (sidebar + topbar) de todas as páginas internas do painel.
 * Espera opcionalmente as variáveis: $pageTitle, $activeNav, $contentClass.
 * Exige sessão autenticada.
 */

require_once __DIR__ . '/functions.php';
require_login();

$pageTitle   = $pageTitle ?? 'Painel';
$activeNav   = $activeNav ?? '';
$contentClass = $contentClass ?? '';
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Painel Admin Qadro</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= asset_version('assets/css/style.css') ?>">
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= asset_version('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
  <div class="admin-shell">
    <aside class="admin-sidebar">
      <img src="../assets/logos/PNG/Simbolo Branco.png" alt="Qadro" class="admin-sidebar-logo">
      <nav class="admin-nav">
        <a href="home.php" class="admin-nav-link <?= $activeNav === 'home' ? 'active' : '' ?>">Dashboard</a>
        <a href="blog.php" class="admin-nav-link <?= $activeNav === 'blog' ? 'active' : '' ?>">Blog</a>
        <a href="portfolio.php" class="admin-nav-link <?= $activeNav === 'portfolio' ? 'active' : '' ?>">Portfólio</a>
      </nav>
      <div class="admin-sidebar-footer">
        <p class="admin-sidebar-user">Logado como<br><strong><?= e(current_admin_username()) ?></strong></p>
        <a href="logout.php" class="admin-logout-link">Sair</a>
      </div>
    </aside>
    <div class="admin-main">
      <header class="admin-topbar">
        <h1 class="admin-page-title"><?= e($pageTitle) ?></h1>
      </header>
      <main class="admin-content <?= e($contentClass) ?>">
        <?php if ($flash): ?>
          <div class="admin-alert admin-alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
