<div id="modalPublicacionPerfil" class="modal-overlay" aria-hidden="true">
  <div class="modal modal-post">
    <div class="modal-header modal-header-post">
      <div class="post-head">
        <div class="post-head-txt">
          <div class="post-user">Publicación</div>
          <div id="p-fecha" class="post-meta" style="display:none;"></div>
        </div>
      </div>

      <button id="cerrarModalPublicacionPerfil" type="button" class="btn-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body">
      <article class="post-modal">
        <!-- Texto arriba -->
        <p id="p-desc" class="post-text"></p>

        <!-- Imagen -->
        <div class="post-media" id="p-img-wrap" style="display:none;">
          <img id="p-img" alt="Imagen publicación">
        </div>

        <!-- Pie de foto debajo -->
        <p id="p-pie" class="post-caption"></p>
      </article>
    </div>
  </div>
</div>

<script>
(function () {
  function openPerfilPostModal(item) {
    const modal = document.getElementById('modalPublicacionPerfil');
    if (!modal) return;

    const imgUrl = item.dataset.img || '';
    const pie = item.dataset.pie || '';
    const desc = item.dataset.desc || '';
    const fecha = item.dataset.fecha || '';

    const imgWrap = document.getElementById('p-img-wrap');
    const img = document.getElementById('p-img');
    const pieEl = document.getElementById('p-pie');
    const descEl = document.getElementById('p-desc');
    const fechaEl = document.getElementById('p-fecha');

    // Texto arriba
    if (descEl) {
      descEl.textContent = desc;
      descEl.style.display = desc ? '' : 'none';
    }

    // Imagen
    if (imgWrap && img) {
      if (imgUrl) {
        imgWrap.style.display = '';
        img.src = imgUrl + (imgUrl.includes('?') ? '&' : '?') + 't=' + Date.now();
      } else {
        imgWrap.style.display = 'none';
        img.removeAttribute('src');
      }
    }

    // Pie
    if (pieEl) {
      pieEl.textContent = pie;
      pieEl.style.display = pie ? '' : 'none';
    }

    // Fecha
    if (fechaEl) {
      if (fecha) { fechaEl.style.display = ''; fechaEl.textContent = fecha; }
      else { fechaEl.style.display = 'none'; fechaEl.textContent = ''; }
    }

    modal.classList.add('abierto');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }

  function closePerfilPostModal() {
    const modal = document.getElementById('modalPublicacionPerfil');
    if (!modal) return;
    modal.classList.remove('abierto');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }

  // Listener global (sirve aunque el perfil se cargue por fetch)
  document.addEventListener('click', (e) => {
    const target = e.target;
    if (!target || !target.closest) return;

    const item = target.closest('.grid-item');
    if (item) {
      openPerfilPostModal(item);
      return;
    }

    if (target.id === 'modalPublicacionPerfil' || target.id === 'cerrarModalPublicacionPerfil') {
      closePerfilPostModal();
    }
  }, true);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePerfilPostModal();
  });
})();
</script>
