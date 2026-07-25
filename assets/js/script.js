/**
 * SCRIPT PRINCIPAL - QADRO CONSTRUTORA & PROJETOS
 * 
 * Este arquivo contém as interações e comportamentos dinâmicos da página inicial:
 * - Menu responsivo (hambúrguer)
 * - Links ativos ao rolar a página (Scroll Spy)
 * - Carrossel de trabalhos destaque
 */

document.addEventListener('DOMContentLoaded', () => {

  // ==========================================================================
  // 1. MENU RESPONSIVO MOBILE
  // ==========================================================================
  const menuToggle = document.getElementById('menuToggle');
  const navMenu = document.getElementById('navMenu');
  const navLinks = document.querySelectorAll('.nav-link');

  /**
   * Alterna a classe de abertura do menu mobile
   */
  const toggleMenu = () => {
    navMenu.classList.toggle('open');
    menuToggle.classList.toggle('active');
    
    // Anima as barras do hambúrguer
    const spans = menuToggle.querySelectorAll('span');
    if (menuToggle.classList.contains('active')) {
      spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
      spans[1].style.opacity = '0';
      spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
    } else {
      spans[0].style.transform = 'none';
      spans[1].style.opacity = '1';
      spans[2].style.transform = 'none';
    }
  };

  if (menuToggle && navMenu) {
    menuToggle.addEventListener('click', toggleMenu);
  }

  // Previne comportamento padrão em links desativados (#) e fecha menu mobile
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');
      if (href === '#' || href === 'javascript:void(0)') {
        e.preventDefault();
      }
      if (navMenu.classList.contains('open')) {
        toggleMenu();
      }
    });
  });


  // ==========================================================================
  // 2. NAVEGAÇÃO DE PÁGINA (LINK ATIVO CONFORME A PÁGINA ATUAL)
  // ==========================================================================
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (!href || href === '#') return;
    const linkPage = href.split('#')[0] || 'index.html';
    if (linkPage === currentPage) {
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    }
  });


  // ==========================================================================
  // 3. CARROSSEL DE TRABALHOS DESTAQUE
  // ==========================================================================
  const carouselContainer = document.getElementById('carouselContainer');
  const carouselDots = document.querySelectorAll('.carousel-dot');
  let currentSlide = 0;
  const slideIntervalTime = 5000; // 5 segundos por slide
  let slideInterval;

  /**
   * Muda para o slide com o índice especificado
   * @param {number} index - Índice do slide a ser mostrado
   */
  const goToSlide = (index) => {
    if (!carouselContainer) return;
    carouselContainer.style.transform = `translateX(-${index * 100}%)`;
    
    // Atualiza a classe ativa dos pontos (dots)
    carouselDots.forEach(dot => dot.classList.remove('active'));
    carouselDots[index].classList.add('active');
    currentSlide = index;
  };

  /**
   * Inicia a rolagem automática dos slides
   */
  const startSlideShow = () => {
    slideInterval = setInterval(() => {
      let nextSlide = (currentSlide + 1) % carouselDots.length;
      goToSlide(nextSlide);
    }, slideIntervalTime);
  };

  /**
   * Para a rolagem automática (usado ao interagir manualmente)
   */
  const stopSlideShow = () => {
    clearInterval(slideInterval);
  };

  if (carouselDots.length > 0) {
    // Adiciona evento de clique manual nos dots
    carouselDots.forEach(dot => {
      dot.addEventListener('click', (e) => {
        const index = parseInt(e.target.getAttribute('data-index'));
        stopSlideShow();
        goToSlide(index);
        startSlideShow(); // Reinicia o timer após interação
      });
    });

    // Inicializa a transição automática
    startSlideShow();
  }


  // ==========================================================================
  // 4. ACORDEÃO DE SERVIÇOS (PÁGINA SERVIÇOS)
  // ==========================================================================
  const servicoItems = document.querySelectorAll('.servico-item');

  /**
   * Fecha um item do acordeão de serviços
   * @param {Element} item - Elemento .servico-item a ser fechado
   */
  const closeServicoItem = (item) => {
    const toggle = item.querySelector('.servico-item-toggle');
    const detail = item.querySelector('.servico-item-detail');
    const label = item.querySelector('.servico-item-label');
    toggle.setAttribute('aria-expanded', 'false');
    label.textContent = 'Ver mais';
    detail.style.maxHeight = null;
  };

  /**
   * Abre um item do acordeão de serviços
   * @param {Element} item - Elemento .servico-item a ser aberto
   */
  const openServicoItem = (item) => {
    const toggle = item.querySelector('.servico-item-toggle');
    const detail = item.querySelector('.servico-item-detail');
    const label = item.querySelector('.servico-item-label');
    toggle.setAttribute('aria-expanded', 'true');
    label.textContent = 'Ver menos';
    detail.style.maxHeight = detail.scrollHeight + 'px';
  };

  servicoItems.forEach(item => {
    const toggle = item.querySelector('.servico-item-toggle');
    toggle.addEventListener('click', () => {
      const isOpen = toggle.getAttribute('aria-expanded') === 'true';

      // Fecha qualquer outro item aberto (apenas um por vez)
      servicoItems.forEach(other => {
        if (other !== item) closeServicoItem(other);
      });

      if (isOpen) {
        closeServicoItem(item);
      } else {
        openServicoItem(item);
      }
    });
  });
});
