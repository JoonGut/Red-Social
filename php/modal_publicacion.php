<div id="modalPublicacion" class="modal-overlay" aria-hidden="true">
  <div class="modal modal-confirm" role="dialog" aria-modal="true" aria-labelledby="mp-titulo">
    <div class="modal-header">
      <div></div>
      <h2 id="mp-titulo"></h2>
      <button type="button" id="cerrarModalPublicacion">×</button>
    </div>

    <div class="modal-body">
      <small id="mp-fecha" style="display:none;"></small>
      <p id="mp-ubicacion" style="display:none;"></p>

      <p id="mp-texto"></p>

      <div id="mp-imagen-wrap" style="display:none;">
        <img id="mp-imagen" alt="Imagen de la publicación" />
      </div>

      <p id="mp-pie" style="display:none;"></p>
              <button type="button" id="borrarPublicacion" class="boton-eliminar" >Eliminar publicación</button>
    </div>
  </div>
</div>
<script>
    function escapeHtml(str) {
  return (str ?? '').replace(/[&<>"']/g, m => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[m]));
}

function openPostModal(article) {
  const modal = document.getElementById('modalPublicacion');

  const usuario = article.dataset.usuario || '';
  const fecha = article.dataset.fecha || '';
  const ubicacion = article.dataset.ubicacion || '';
  const texto = article.dataset.texto || '';
  const img = article.dataset.img || '';
  const pie = article.dataset.pie || '';

  const postId = article.dataset.id;
document.getElementById('borrarPublicacion').dataset.id = postId;

  document.getElementById('mp-titulo').textContent = usuario;

  const fechaEl = document.getElementById('mp-fecha');
  if (fecha) { fechaEl.style.display = ''; fechaEl.textContent = fecha; }
  else { fechaEl.style.display = 'none'; }

  const ubiEl = document.getElementById('mp-ubicacion');
  if (ubicacion) { ubiEl.style.display = ''; ubiEl.innerHTML = `<strong>📍</strong> ${escapeHtml(ubicacion)}`; }
  else { ubiEl.style.display = 'none'; }

  // Texto con saltos de línea
  document.getElementById('mp-texto').innerHTML = escapeHtml(texto).replace(/\n/g, '<br>');

  const imgWrap = document.getElementById('mp-imagen-wrap');
  const imgEl = document.getElementById('mp-imagen');
  if (img) {
    imgWrap.style.display = '';
    imgEl.src = img;
  } else {
    imgWrap.style.display = 'none';
    imgEl.removeAttribute('src');
  }

  const pieEl = document.getElementById('mp-pie');
  if (pie) { pieEl.style.display = ''; pieEl.innerHTML = `<em>${escapeHtml(pie)}</em>`; }
  else { pieEl.style.display = 'none'; }

  modal.classList.add('abierto');
  modal.setAttribute('aria-hidden', 'false');
}

function closePostModal() {
  const modal = document.getElementById('modalPublicacion');
  modal.classList.remove('abierto');
  modal.setAttribute('aria-hidden', 'true');
}

// Click en cualquier publicación
document.addEventListener('click', (e) => {
  const article = e.target.closest('article.publicaciones');
  if (article) openPostModal(article);

  // Cerrar si click en fondo o botón cerrar
  if (e.target.id === 'modalPublicacion' || e.target.id === 'cerrarModalPublicacion') {
    closePostModal();
  }
});

// ESC para cerrar
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closePostModal();
});
document.getElementById('borrarPublicacion')?.addEventListener('click', () => {
  const btn = document.getElementById('borrarPublicacion');
  const postId = btn.dataset.id;

  if (!postId) return;

  if (!confirm('¿Seguro que quieres eliminar esta publicación?')) return;

  fetch('../php/eliminar_publicacion.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${encodeURIComponent(postId)}`
  })
  .then(res => res.text())
  .then(res => {
    if (res === 'ok') {
      // quitar publicación del feed
      document.querySelector(`article.publicacion[data-id="${postId}"]`)?.remove();
      closePostModal();
    } else {
      alert('Error al eliminar');
    }
  })
  .catch(() => alert('Error de conexión'));
});

</script>

