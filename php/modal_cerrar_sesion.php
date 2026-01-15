<div id="modal-overlay-sesion" class="modal-overlay" style="display:none;">
  <div id="modal-sesion" class="modal modal-confirm">
    <div class="modal-header">
      <span class="modal-spacer"></span>
      <h2>Confirmar cierre de sesión</h2>
      <button id="cerrarModalSesion" class="modal-close" type="button">×</button>
    </div>
    <div class="modal-body">
      <p>¿Estás seguro de que quieres cerrar sesión?</p>
    </div>
    <div class="modal-footer">
      <button class="boton-cancelar" onclick="ocultarModal()">Cancelar</button>
      <button class="boton-registrarse" onclick="cerrarSesion()">Cerrar sesión</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var overlay = document.getElementById('modal-overlay-sesion');
  var cerrar = document.getElementById('cerrarModalSesion');

  cerrar.onclick = function () {
    ocultarModal();
  };

  overlay.onclick = function (e) {
    if (e.target === overlay) {
      ocultarModal();
    }
  };
});

function mostrarModal() {
  document.getElementById('modal-overlay-sesion').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function ocultarModal() {
  document.getElementById('modal-overlay-sesion').style.display = 'none';
  document.body.style.overflow = '';
}
function cerrarSesion() {
  window.location.href = 'cerrarSesion.php';
}
</script>