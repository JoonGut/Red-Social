<div id="modalOverlay" class="modal-overlay" style="display:none;" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
    <div class="modal-header">
      <button id="cerrarModal" class="modal-close" type="button" aria-label="Cerrar">✕</button>
      <span class="modal-spacer"></span>
    </div>

    <form action="../php/guardar_publicacion.php" method="POST" enctype="multipart/form-data" class="modal-body">
      <textarea id="textoPub" name="texto" placeholder="¿Qué está pasando?" required maxlength="250"></textarea>

      <div class="modal-row">
        <input type="text" name="ubicacion" placeholder="Ubicación" maxlength="250">
      </div>

      <div class="modal-row">
        <input type="text" name="pie_foto" placeholder="Pie de foto" maxlength="250">
      </div>

      <div class="modal-row">
        <input type="file" name="imagen" id="file-upload" accept="image/jpeg,image/png,image/webp" hidden>
        <label for="file-upload" class="custom-file-upload">
          <i class="fas fa-cloud-upload-alt"></i> 
        </label>
      </div>

      <div class="modal-footer">
        <small id="contador">0/250</small>
        <button class="boton-registrarse" type="submit">Publicar</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    var abrir = document.getElementById('abrirModal');
    var overlay = document.getElementById('modalOverlay');
    var cerrar = document.getElementById('cerrarModal');
    var texto = document.getElementById('textoPub');
    var contador = document.getElementById('contador');

    abrir.onclick = function() {
      overlay.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      texto.focus();
    };

    cerrar.onclick = function() {
      overlay.style.display = 'none';
      document.body.style.overflow = '';
    };

    overlay.onclick = function(e) {
      if (e.target === overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
      }
    };

    texto.oninput = function() {
      contador.innerText = texto.value.length + '/250';
    };

  });
</script>