<?php
/**
 * Header/nav do site público (compartilhado pelas páginas dinâmicas de
 * Blog e Portfólio). Espera opcionalmente: $pageHtmlTitle, $metaDescription,
 * $activePage ('portfolio'|'blog'|''), $baseHref (usado pela prévia do admin,
 * que roda de dentro de /admin e precisa resolver os caminhos do site).
 */

$pageHtmlTitle   = $pageHtmlTitle ?? 'Qadro Construtora e Projetos';
$metaDescription = $metaDescription ?? 'Qadro Construtora & Projetos - Excelência em projetos comerciais, industriais e residenciais de alto padrão.';
$metaKeywords    = $metaKeywords ?? '';
$activePage      = $activePage ?? '';
$baseHref        = $baseHref ?? '';

function nav_active(string $page, string $activePage): string
{
    return $page === $activePage ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <?php if ($baseHref !== ''): ?>
  <base href="<?= e($baseHref) ?>">
  <?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($metaDescription) ?>">
  <?php if ($metaKeywords !== ''): ?>
  <meta name="keywords" content="<?= e($metaKeywords) ?>">
  <?php endif; ?>
  <meta name="author" content="Q_ADRO">
  <title><?= e($pageHtmlTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= asset_version('assets/css/style.css') ?>">
</head>
<body>

  <header class="header" id="inicio">
    <div class="container header-container">
      <div class="header-top">
        <div class="menu-toggle" id="menuToggle">
          <span></span>
          <span></span>
          <span></span>
        </div>

        <a href="index.php#inicio" class="logo-link">
          <img src="assets/logos/PNG/Horizontal Preto.png" alt="Qadro Construtora" class="logo-img">
        </a>

        <div class="nav-lang-selector">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
          </svg>
          <span>PT</span>
        </div>
      </div>

      <nav class="nav-menu" id="navMenu">
        <ul class="nav-list">
          <li><a href="index.php#inicio" class="nav-link">Início</a></li>
          <li><a href="servicos.html" class="nav-link">Serviços</a></li>
          <li><a href="portfolio.php" class="nav-link<?= nav_active('portfolio', $activePage) ?>">Portfolio</a></li>
          <li><a href="sobre.html" class="nav-link">Sobre</a></li>
          <li><a href="blog.php" class="nav-link<?= nav_active('blog', $activePage) ?>">Blog</a></li>
          <li><a href="parceiros.html" class="nav-link">Parceiros</a></li>
          <li><a href="contatos.html" class="nav-link">Contatos</a></li>
        </ul>
      </nav>
    </div>
  </header>
