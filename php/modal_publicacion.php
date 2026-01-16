<div id="modalPublicacion" class="modal-overlay" aria-hidden="true">
  <div class="modal modal-post">
    <div class="modal-header modal-header-post">
      <div class="post-head">
        <div class="post-head-txt">
          <a id="m-user" class="post-user" href="#" role="link">@usuario</a>
          <div id="m-fecha" class="post-meta"></div>
        </div>
      </div>

      <button id="cerrarModal" type="button" class="btn-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body">
      <article class="post-modal">

        <!-- Texto (arriba, como Twitter) -->
        <p id="m-texto" class="post-text"></p>

        <!-- Ubicación opcional -->
        <div id="m-ubicacion" class="post-location" style="display:none;"></div>

        <!-- Imagen -->
        <div class="post-media" id="m-img-wrap" style="display:none;">
          <img id="m-img" alt="Imagen publicación">
        </div>

        <!-- Pie de foto (caption) -->
        <p id="m-pie" class="post-caption"></p>

        <!-- Acciones -->
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
  function escapeHtml(str) {
    return (str ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    } [m]));
  }

  function openPostModal(article) {
    const modal = document.getElementById('modalPublicacion');
    if (!modal) return;

    const usuario = article.dataset.usuario || '';
    const fecha = article.dataset.fecha || '';
    const ubicacion = article.dataset.ubicacion || '';
    const texto = article.dataset.texto || '';
    const img = article.dataset.img || '';
    const pie = article.dataset.pie || '';
    const postId = article.dataset.id || '';

    // Header: usuario + fecha
    // Header: usuario + fecha
    const userEl = document.getElementById('m-user');
    if (userEl) {
      const uname = usuario || '';
      userEl.textContent = uname ? `@${uname}` : '@usuario';

      // fallback por si JS falla (normal)
      userEl.href = uname ? `../php/perfil_usuario.php?u=${encodeURIComponent(uname)}` : '#';

      // AJAX (sin recarga) si existe la función global
      userEl.onclick = (ev) => {
        if (!uname) return;
        if (typeof window.loadUserProfile === 'function') {
          ev.preventDefault();
          closePostModal();
          window.loadUserProfile(uname);
        }
      };
    }


    const fechaEl = document.getElementById('m-fecha');
    if (fechaEl) {
      fechaEl.textContent = fecha || '';
      fechaEl.style.display = fecha ? '' : 'none';
    }

    // Texto (arriba)
    const textoEl = document.getElementById('m-texto');
    if (textoEl) {
      textoEl.innerHTML = escapeHtml(texto).replace(/\n/g, '<br>');
      textoEl.style.display = texto ? '' : 'none';
    }

    // Ubicación
    const ubiEl = document.getElementById('m-ubicacion');
    if (ubiEl) {
      if (ubicacion) {
        ubiEl.style.display = '';
        ubiEl.innerHTML = `📍 ${escapeHtml(ubicacion)}`;
      } else {
        ubiEl.style.display = 'none';
        ubiEl.textContent = '';
      }
    }

    // Imagen
    const imgWrap = document.getElementById('m-img-wrap');
    const imgEl = document.getElementById('m-img');
    if (imgWrap && imgEl) {
      if (img) {
        imgWrap.style.display = '';
        imgEl.src = img + (img.includes('?') ? '&' : '?') + 't=' + Date.now();
      } else {
        imgWrap.style.display = 'none';
        imgEl.removeAttribute('src');
      }
    }

    // Pie de foto (debajo de la imagen)
    const pieEl = document.getElementById('m-pie');
    if (pieEl) {
      pieEl.textContent = pie || '';
      pieEl.style.display = pie ? '' : 'none';
    }

    // Borrar
    const borrarBtn = document.getElementById('borrarPublicacion');
    if (borrarBtn) borrarBtn.dataset.id = postId;

    modal.classList.add('abierto');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }

  function closePostModal() {
    const modal = document.getElementById('modalPublicacion');
    if (!modal) return;
    modal.classList.remove('abierto');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }

  // Click en publicación del feed (article.publicaciones)
  document.addEventListener('click', (e) => {
    const article = e.target.closest('article.publicaciones');
    if (article) {
      openPostModal(article);
      return;
    }

    if (e.target.id === 'modalPublicacion' || e.target.id === 'cerrarModal') {
      closePostModal();
    }
  }, true);

  // ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePostModal();
  });

  // eliminar
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