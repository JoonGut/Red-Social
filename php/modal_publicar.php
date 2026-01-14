<!-- MODAL NUEVA PUBLICACIÓN -->
<div id="modalOverlay"
     class="modal-overlay"
     aria-hidden="true"
     style="
       display:none;
       position:fixed;
       inset:0;
       background:rgba(0,0,0,.55);
       z-index:999999;
       align-items:center;
       justify-content:center;
       padding:16px;
     ">
  <div class="modal"
       role="dialog"
       aria-modal="true"
       aria-labelledby="modalTitulo"
       style="
         width:min(650px, 100%);
         background:#fff;
         border-radius:16px;
         box-shadow:0 10px 40px rgba(0,0,0,.25);
         overflow:hidden;
       ">

    <div class="modal-header" style="
      display:grid;
      grid-template-columns:40px 1fr 40px;
      align-items:center;
      padding:12px 14px;
      border-bottom:1px solid rgba(0,0,0,.08);
    ">
      <button id="cerrarModal"
              type="button"
              aria-label="Cerrar"
              style="
                width:36px;height:36px;
                border:0;background:transparent;
                cursor:pointer;border-radius:999px;
                font-size:18px;
              ">✕</button>

      <h2 id="modalTitulo" style="margin:0;font-size:18px;text-align:center;">Nueva publicación</h2>
      <span></span>
    </div>

    <form action="../php/guardar_publicacion.php"
          method="POST"
          enctype="multipart/form-data"
          class="modal-body"
          style="padding:14px; display:grid; gap:10px;">

      <textarea id="textoPub"
                name="texto"
                placeholder="¿Qué está pasando?"
                required
                maxlength="250"
                style="
                  width:100%;
                  min-height:120px;
                  resize:none;
                  border:1px solid rgba(0,0,0,.15);
                  border-radius:12px;
                  padding:12px;
                  outline:none;
                "></textarea>

      <input type="text" name="ubicacion" placeholder="Ubicación (opcional)" maxlength="250"
             style="width:100%; padding:10px; border:1px solid rgba(0,0,0,.15); border-radius:12px;">

      <input type="text" name="pie_foto" placeholder="Pie de foto (opcional)" maxlength="250"
             style="width:100%; padding:10px; border:1px solid rgba(0,0,0,.15); border-radius:12px;">

      <input type="file" name="imagen" accept="image/*">

      <div class="modal-footer" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <small id="contador">0/250</small>
        <button class="boton-registrarse" type="submit">Publicar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const abrir = document.getElementById('abrirModal');
  const overlay = document.getElementById('modalOverlay');
  const cerrar = document.getElementById('cerrarModal');
  const textarea = document.getElementById('textoPub');
  const contador = document.getElementById('contador');

  function openModal() {
    overlay.style.display = 'flex';
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Focus sin bajar la página (cuando el navegador lo soporta)
    setTimeout(() => {
      try {
        textarea.focus({ preventScroll: true });
      } catch (e) {
        textarea.focus();
      }
    }, 0);
  }

  function closeModal() {
    overlay.style.display = 'none';
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  abrir.addEventListener('click', openModal);
  cerrar.addEventListener('click', closeModal);

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.style.display !== 'none') closeModal();
  });

  textarea.addEventListener('input', () => {
    contador.textContent = `${textarea.value.length}/250`;
  });

  // Si vuelves con error, abre el modal
  const params = new URLSearchParams(window.location.search);
  if (params.has('error')) openModal();
});
</script>
