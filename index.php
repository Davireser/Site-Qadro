<?php
require_once __DIR__ . '/includes/functions.php';

// Trabalhos em destaque: escolha do administrador (checkbox "featured" no
// cadastro do projeto), na ordem de exibição definida por ele.
$featuredProjects = db()->query(
    "SELECT id, slug, project_name, title, image FROM projects WHERE featured = 1 ORDER BY display_order ASC, created_at DESC, id DESC"
)->fetchAll();

// Artigos recentes: os 4 últimos publicados, com o marcado como destaque à frente.
$recentPosts = db()->query(
    'SELECT id, slug, title, image FROM posts ORDER BY featured DESC, post_date DESC, id DESC LIMIT 4'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Qadro Construtora & Projetos - Excelência em projetos comerciais, industriais e residenciais de alto padrão, combinando expertise tradicional e tecnologia BIM avançada.">
  <meta name="author" content="Q_ADRO">
  <title>Qadro | Construtora e Projetos de Alto Padrão</title>

  <!-- CSS Principal -->
  <link rel="stylesheet" href="assets/css/style.css?v=<?= asset_version('assets/css/style.css') ?>">
</head>
<body>

  <!-- ==========================================
       INÍCIO HEADER (CABEÇALHO)
       ========================================== -->
  <header class="header" id="inicio">
    <div class="container header-container">
      <div class="header-top">
        <!-- Hamburguer Menu para Mobile -->
        <div class="menu-toggle" id="menuToggle">
          <span></span>
          <span></span>
          <span></span>
        </div>

        <!-- Logo Centralizada -->
        <a href="#inicio" class="logo-link">
          <img src="assets/logos/PNG/Horizontal Preto.png" alt="Qadro Construtora" class="logo-img">
        </a>

        <!-- Seletor de Idioma -->
        <div class="nav-lang-selector">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
          </svg>
          <span>PT</span>
        </div>
      </div>

      <!-- Menu de Navegação -->
      <nav class="nav-menu" id="navMenu">
        <ul class="nav-list">
          <li><a href="#inicio" class="nav-link active">Início</a></li>
          <li><a href="servicos.html" class="nav-link">Serviços</a></li>
          <li><a href="portfolio.php" class="nav-link">Portfolio</a></li>
          <li><a href="sobre.html" class="nav-link">Sobre</a></li>
          <li><a href="blog.php" class="nav-link">Blog</a></li>
          <li><a href="parceiros.html" class="nav-link">Parceiros</a></li>
          <li><a href="contatos.html" class="nav-link">Contatos</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <!-- ==========================================
       FIM HEADER (CABEÇALHO)
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO HERO (LAND PAGE)
       ========================================== -->
  <section class="hero" style="background-image: url('assets/images/sala-qadro.webp');">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">Arquitetura<br>Engenharia<br>Construção</h1>
        <p class="hero-subtitle">Do primeiro traço à obra concluída, reunimos diferentes disciplinas em torno de um mesmo propósito.</p>
        <a href="portfolio.php" class="btn-link btn-solid-escuro">Ver Portfólio</a>
      </div>
    </div>
  </section>
  <!-- ==========================================
       FIM SEÇÃO HERO (LAND PAGE)
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO MANIFESTO & ESTATÍSTICAS
       ========================================== -->
  <section class="section manifesto-section">
    <div class="container manifesto-grid">
      <!-- Lado Esquerdo: Manifesto -->
      <div class="manifesto-left">
        <span class="manifesto-tag">Construtora e Estúdio</span>
        <h2 class="manifesto-text">CONSTRUÍMOS PARA UM FUTURO QUE EXIGE MAIS INTELIGÊNCIA, PRECISÃO E RESPONSABILIDADE EM CADA ESCOLHA.</h2>
      </div>

      <!-- Lado Direito: Estatísticas -->
      <div class="stats-grid">
        <div class="stat-item">
          <span class="stat-number">2019</span>
          <span class="stat-label">Desde</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">+50K</span>
          <span class="stat-label">m² de área projetada</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">+100</span>
          <span class="stat-label">Projetos entregues</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">75%</span>
          <span class="stat-label">De clientes recorrentes</span>
        </div>
      </div>
    </div>
  </section>
  <!-- ==========================================
       FIM SEÇÃO MANIFESTO & ESTATÍSTICAS
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO ÁREAS DE ATUAÇÃO
       ========================================== -->
  <section class="section section-areas">
    <div class="container">
      <div class="section-header-flex">
        <div>
          <span class="section-title-tag">Áreas de Atuação</span>
        </div>
        <a href="servicos.html" class="btn-link btn-ver-todos">Ver Todos</a>
      </div>

      <!-- Grid de Atuações -->
      <div class="areas-grid">
        <div class="area-card">
          <h3 class="area-title">Arquitetura e<br>Design</h3>
          <p class="area-desc">Projetos concebidos para integrar intenção, funcionalidade e viabilidade, considerando desde o início as decisões que orientam sua construção.</p>
        </div>
        <div class="area-card">
          <h3 class="area-title">Engenharia e<br>Instalações</h3>
          <p class="area-desc">Soluções técnicas desenvolvidas de forma coordenada, compatibilizando sistemas para antecipar conflitos e garantir eficiência à execução.</p>
        </div>
        <div class="area-card">
          <h3 class="area-title">Planejamento e<br>Controle</h3>
          <p class="area-desc">Orçamento, cronograma e gestão integrados para organizar recursos, acompanhar resultados e ampliar a previsibilidade durante toda a obra.</p>
        </div>
        <div class="area-card">
          <h3 class="area-title">Construção e<br>Desenvolvimento</h3>
          <p class="area-desc">Execução conduzida com método e controle, coordenando equipes, recursos e processos para transformar planejamento em resultado.</p>
        </div>
      </div>
    </div>
  </section>
  <!-- ==========================================
       FIM SEÇÃO ÁREAS DE ATUAÇÃO
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO POR QUE ESCOLHER A QADRO
       ========================================== -->
  <section class="section why-section" id="sobre">
    <div class="container why-qadro-grid">
      <!-- Coluna da Esquerda: Título & Subtítulo -->
      <div class="why-qadro-left">
        <h2 class="why-title">POR QUE<br>ESCOLHER A QADRO</h2>
        <p class="why-subtitle">Projeto e construção fazem parte do mesmo processo. Integramos conhecimento técnico, planejamento e tecnologia para antecipar decisões, reduzir incertezas e transformar complexidade em resultado.</p>
      </div>

      <!-- Coluna da Direita: Lista de Diferenciais -->
      <div class="why-list-right">
        <!-- Item 1 -->
        <div class="why-item">
          <div class="why-item-title">
            <h3>PROCESSO<br>INTEGRADO</h3>
          </div>
          <div class="why-item-desc">
            <p>Arquitetura, engenharia, planejamento e construção atuam como um único processo, aproximando decisões e execução desde o início.</p>
          </div>
        </div>

        <!-- Item 2 -->
        <div class="why-item">
          <div class="why-item-title">
            <h3>ANTECIPAÇÃO<br>E CONTROLE</h3>
          </div>
          <div class="why-item-desc">
            <p>BIM e ferramentas digitais organizam informações, antecipam conflitos e ampliam a precisão das decisões ao longo do projeto e da execução.</p>
          </div>
        </div>

        <!-- Item 3 -->
        <div class="why-item">
          <div class="why-item-title">
            <h3>RESPONSABILIDADE<br>CONSTRUTIVA</h3>
          </div>
          <div class="why-item-desc">
            <p>Planejamento, orçamento e acompanhamento transformam complexidade em processo, reduzindo incertezas e conduzindo a obra com mais controle.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ==========================================
       FIM SEÇÃO POR QUE ESCOLHER A QADRO
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO TRABALHOS DESTAQUE
       ========================================== -->
  <?php if ($featuredProjects): ?>
  <section class="section section-featured">
    <div class="container">
      <div class="section-header-flex">
        <div>
          <span class="section-title-tag">Trabalhos Destaque</span>
        </div>
        <a href="portfolio.php" class="btn-link btn-ver-todos">Ver Todos</a>
      </div>

      <!-- Carrossel de Projetos: escolha do administrador (campo "Destaque" no cadastro) -->
      <div class="carousel-wrapper">
        <div class="carousel-container" id="carouselContainer">
          <?php foreach ($featuredProjects as $i => $fp): ?>
            <div class="carousel-slide">
              <a href="portfolio_detalhe.php?slug=<?= urlencode($fp['slug']) ?>">
                <img src="includes/image.php?type=portfolio&id=<?= (int) $fp['id'] ?>" alt="<?= e($fp['project_name'] ?: $fp['title']) ?>" class="carousel-img">
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (count($featuredProjects) > 1): ?>
          <!-- Pontos de Navegação -->
          <div class="carousel-nav-dots" id="carouselDots">
            <?php foreach ($featuredProjects as $i => $fp): ?>
              <button class="carousel-dot<?= $i === 0 ? ' active' : '' ?>" data-index="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
  <!-- ==========================================
       FIM SEÇÃO TRABALHOS DESTAQUE
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO ARTIGOS RECENTES (BLOG)
       ========================================== -->
  <?php if ($recentPosts): ?>
  <section class="section section-blog">
    <div class="container">
      <div class="section-header-flex">
        <div>
          <span class="section-title-tag">Artigos Recentes</span>
        </div>
        <a href="blog.php" class="btn-link btn-ver-todos">Ver Todos</a>
      </div>

      <!-- Últimos artigos publicados no painel admin -->
      <div class="blog-grid">
        <?php foreach ($recentPosts as $recent): ?>
          <a href="blog_detalhe.php?slug=<?= urlencode($recent['slug']) ?>" class="blog-card">
            <div class="blog-img-wrapper">
              <?php if ($recent['image']): ?>
                <img src="includes/image.php?type=blog&id=<?= (int) $recent['id'] ?>" alt="<?= e($recent['title']) ?>" class="blog-img">
              <?php endif; ?>
            </div>
            <h3 class="blog-title"><?= e($recent['title']) ?></h3>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
  <!-- ==========================================
       FIM SEÇÃO ARTIGOS RECENTES (BLOG)
       ========================================== -->


  <!-- ==========================================
       INÍCIO SEÇÃO ENTRE EM CONTATO (CTA)
       ========================================== -->
  <section class="section section-cta">
    <div class="container cta-flex">
      <!-- Lado Esquerdo: Título -->
      <div class="cta-left">
        <h2 class="cta-title">ENTRE<br>EM CONTATO</h2>
      </div>

      <!-- Lado Direito: Subtítulo e Link WhatsApp -->
      <div class="cta-right">
        <p class="cta-subtitle">Cada projeto começa com uma conversa. Conte-nos o que você pretende construir e vamos definir o primeiro passo.</p>
        <a href="https://wa.me/5535998185671" target="_blank" rel="noopener" class="btn-link btn-chamar-qadro">Falar com a Qadro</a>
      </div>
    </div>
  </section>



  <!-- ==========================================
       INÍCIO FOOTER (RODAPÉ)
       ========================================== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-top">
        <!-- Logo e Resumo -->
        <div class="footer-logo-col">
          <!-- Logo Símbolo Branca -->
          <img src="assets/logos/PNG/Simbolo Branco.png" alt="Símbolo Qadro" class="footer-logo-img">
          <p class="footer-desc">Serviços profissionais de construção e arquitetura, entregando excelência em projetos comerciais, industriais e residenciais.</p>
        </div>

        <!-- Coluna Links de Mídia -->
        <div>
          <h4 class="footer-col-title">Follow</h4>
          <ul class="footer-links-list">
            <li><a href="https://instagram.com/qadroconstrutora" target="_blank" rel="noopener">Instagram</a></li>
            <li><a href="#" target="_blank" rel="noopener">Facebook</a></li>
          </ul>
        </div>

        <!-- Coluna Links Rápidos -->
        <div>
          <h4 class="footer-col-title">Links Rápidos</h4>
          <ul class="footer-links-list">
            <li><a href="#inicio">Início</a></li>
            <li><a href="sobre.html">Sobre</a></li>
            <li><a href="servicos.html">Serviços</a></li>
            <li><a href="portfolio.php">Projetos</a></li>
            <li><a href="parceiros.html">Parceiros</a></li>
            <li><a href="contatos.html">Contato</a></li>
          </ul>
        </div>

        <!-- Coluna Contato Rápido -->
        <div class="footer-contact-list">
          <h4 class="footer-col-title">Informações de Contato</h4>
          <div class="footer-contact-item">
            <span class="footer-contact-label">E-mail</span>
            <span class="footer-contact-val">contato@qadro.com.br</span>
          </div>
          <div class="footer-contact-item">
            <span class="footer-contact-label">Telefones</span>
            <span class="footer-contact-val">+55 35 9 9818 5671<br>+55 35 9 8709 2908</span>
          </div>
          <div class="footer-contact-item">
            <span class="footer-contact-label">Localização</span>
            <span class="footer-contact-val">Rua Gabriela Rezende Paiva, 350, Térreo<br>Varginha, Minas Gerais, Brasil<br>37026-650</span>
          </div>
        </div>
      </div>

      <!-- Base do Rodapé -->
      <div class="footer-bottom">
        <p class="footer-copy">&copy; 2026 Q_ADRO. Todos os direitos reservados.</p>
        <div class="footer-policy-links">
          <a href="#">Política de Privacidade</a>
          <a href="#">Termos de Serviços</a>
        </div>
      </div>
    </div>
  </footer>
  <!-- ==========================================
       FIM FOOTER (RODAPÉ)
       ========================================== -->

  <!-- Scripts JS -->
  <script src="assets/js/script.js"></script>
</body>
</html>
