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
        <p id="p-desc" class="post-text"></p>

        <div class="post-media" id="p-img-wrap" style="display:none;">
          <img id="p-img" alt="Imagen publicación">
        </div>

        <p id="p-pie" class="post-caption"></p>
        <div class="post-actions">
          <button type="button" id="borrarPublicacion" class="btn-danger">
            Eliminar publicación
          </button>
        </div>
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

    if (descEl) {
      descEl.textContent = desc;
      descEl.style.display = desc ? '' : 'none';
    }

    if (imgWrap && img) {
      if (imgUrl) {
        imgWrap.style.display = '';
        img.src = imgUrl + (imgUrl.includes('?') ? '&' : '?') + 't=' + Date.now();
      } else {
        imgWrap.style.display = 'none';
        img.removeAttribute('src');
      }
    }

    if (pieEl) {
      pieEl.textContent = pie;
      pieEl.style.display = pie ? '' : 'none';
    }

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
  document.getElementById('borrarPublicacion')?.addEventListener('click', () => {
    const btn = document.getElementById('borrarPublicacion');
    const postId = btn?.dataset?.id;
    if (!postId) return;

    fetch('../php/eliminar_publicacion.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${encodeURIComponent(postId)}`
      })
      .then(res => res.text())
      .then(res => {
        if (res === 'ok') {
          document.querySelector(`article.publicaciones[data-id="${postId}"]`)?.remove();
          closePostModal();
        } else {
          alert('Error al eliminar');
        }
      })
      .catch(() => alert('Error de conexión'));
  });
</script>
