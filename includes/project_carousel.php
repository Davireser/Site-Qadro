<?php
/**
 * Carrossel de imagens de um projeto, com a imagem ampliada (lightbox).
 *
 * Espera:
 *   $gallery   linhas de project_gallery (precisa do id de cada imagem)
 *   $project   projeto em exibição (usa project_name / title no alt)
 *
 * Usado pela página publicada e pela prévia do admin, para que o que o
 * usuário vê antes de salvar seja exatamente o resultado final.
 */
if (empty($gallery)) {
    return;
}
?>
      <!-- Carrossel de imagens -->
      <div class="project-carousel" id="projectCarousel">
        <button type="button" class="project-carousel-stage" id="carouselStage" aria-label="Ampliar imagem">
          <img id="carouselMainImg" src="includes/image.php?type=portfolio_gallery&id=<?= (int) $gallery[0]['id'] ?>" alt="<?= e($project['project_name'] ?: $project['title']) ?>">
        </button>

        <?php if (count($gallery) > 1): ?>
          <div class="project-carousel-thumbs" id="carouselThumbs">
            <?php foreach ($gallery as $i => $img): ?>
              <button type="button" class="project-carousel-thumb<?= $i === 0 ? ' active' : '' ?>" aria-label="Ver imagem <?= $i + 1 ?>">
                <img src="includes/image.php?type=portfolio_gallery&id=<?= (int) $img['id'] ?>" alt="">
              </button>
            <?php endforeach; ?>
          </div>

          <div class="project-carousel-nav">
            <button type="button" class="project-carousel-arrow" id="carouselPrev" aria-label="Imagem anterior">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 4 7 12 15 20"></polyline></svg>
            </button>
            <button type="button" class="project-carousel-arrow" id="carouselNext" aria-label="Próxima imagem">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 4 17 12 9 20"></polyline></svg>
            </button>
          </div>
        <?php endif; ?>
      </div>

      <!-- Imagem ampliada (abre ao clicar na imagem do carrossel) -->
      <div class="project-lightbox" id="projectLightbox" role="dialog" aria-modal="true" aria-label="Imagem ampliada">
        <button type="button" class="project-lightbox-close" id="lightboxClose" aria-label="Fechar">&times;</button>
        <?php if (count($gallery) > 1): ?>
          <button type="button" class="project-lightbox-arrow prev" id="lightboxPrev" aria-label="Imagem anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 4 7 12 15 20"></polyline></svg>
          </button>
          <button type="button" class="project-lightbox-arrow next" id="lightboxNext" aria-label="Próxima imagem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 4 17 12 9 20"></polyline></svg>
          </button>
        <?php endif; ?>
        <img class="project-lightbox-img" id="lightboxImg" src="" alt="<?= e($project['project_name'] ?: $project['title']) ?>">
        <span class="project-lightbox-hint">Clique na imagem para dar zoom</span>
      </div>

<script>
  (function () {
    var images = <?= json_encode(array_map(fn ($img) => 'includes/image.php?type=portfolio_gallery&id=' . (int) $img['id'], $gallery)) ?>;
    var mainImg = document.getElementById('carouselMainImg');
    var thumbs = document.querySelectorAll('#carouselThumbs .project-carousel-thumb');
    var current = 0;

    function show(index) {
      current = (index + images.length) % images.length;
      mainImg.src = images[current];
      thumbs.forEach(function (thumb, i) {
        thumb.classList.toggle('active', i === current);
      });
      if (lightbox.classList.contains('open')) {
        openLightbox(current);
      }
    }

    // --- Imagem ampliada com zoom ---
    var lightbox = document.getElementById('projectLightbox');
    var lightboxImg = document.getElementById('lightboxImg');

    function openLightbox(index) {
      lightboxImg.classList.remove('zoomed');
      lightboxImg.style.transformOrigin = 'center';
      lightboxImg.src = images[index];
      lightbox.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      lightbox.classList.remove('open');
      lightboxImg.classList.remove('zoomed');
      document.body.style.overflow = '';
    }

    document.getElementById('carouselStage').addEventListener('click', function () {
      openLightbox(current);
    });

    document.getElementById('lightboxClose').addEventListener('click', closeLightbox);

    // Clique no fundo (fora da imagem) fecha.
    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) closeLightbox();
    });

    // Clique na imagem alterna o zoom, ancorado no ponto clicado.
    lightboxImg.addEventListener('click', function (event) {
      event.stopPropagation();
      if (lightboxImg.classList.contains('zoomed')) {
        lightboxImg.classList.remove('zoomed');
        lightboxImg.style.transformOrigin = 'center';
        return;
      }
      var rect = lightboxImg.getBoundingClientRect();
      lightboxImg.style.transformOrigin =
        ((event.clientX - rect.left) / rect.width * 100) + '% ' +
        ((event.clientY - rect.top) / rect.height * 100) + '%';
      lightboxImg.classList.add('zoomed');
    });

    // Com zoom ativo, mover o mouse desloca a área visível da imagem.
    lightboxImg.addEventListener('mousemove', function (event) {
      if (!lightboxImg.classList.contains('zoomed')) return;
      var rect = lightboxImg.getBoundingClientRect();
      lightboxImg.style.transformOrigin =
        ((event.clientX - rect.left) / rect.width * 100) + '% ' +
        ((event.clientY - rect.top) / rect.height * 100) + '%';
    });

    <?php if (count($gallery) > 1): ?>
    thumbs.forEach(function (thumb, i) {
      thumb.addEventListener('click', function () { show(i); });
    });

    document.getElementById('carouselPrev').addEventListener('click', function () { show(current - 1); });
    document.getElementById('carouselNext').addEventListener('click', function () { show(current + 1); });

    document.getElementById('lightboxPrev').addEventListener('click', function (event) {
      event.stopPropagation();
      show(current - 1);
    });
    document.getElementById('lightboxNext').addEventListener('click', function (event) {
      event.stopPropagation();
      show(current + 1);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') show(current - 1);
      if (event.key === 'ArrowRight') show(current + 1);
    });
    <?php endif; ?>

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeLightbox();
    });
  })();
</script>
