<div id="modalEditarPerfil" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="ep-titulo">
    <div class="modal-header">
      <div></div>
      <h2 id="ep-titulo">Editar perfil</h2>
      <button type="button" id="cerrarModalEditarPerfil">×</button>
    </div>

    <div class="modal-body">
      <label>
        Nombre
        <input type="text" id="ep-nombre" placeholder="Tu nombre">
      </label>

      <label>
        Bio
        <textarea id="ep-bio" placeholder="Cuéntanos algo"></textarea>
      </label>

      <label>
        Ubicación
        <input type="text" id="ep-ubicacion" placeholder="Ciudad, País">
      </label>

      <div class="modal-footer">
        <button type="button" class="boton-cancelar" id="cancelarEditarPerfil">Cancelar</button>
        <button type="button" class="boton-registrarse" id="guardarEditarPerfil">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
  function openEditarPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (!modal) return;
    modal.classList.add('abierto');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }

  function closeEditarPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (!modal) return;
    modal.classList.remove('abierto');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }

  // ✅ sirve aunque el botón se inserte después por innerHTML
  document.addEventListener('click', (e) => {
    if (e.target && e.target.id === 'botonEditarPerfil') {
      openEditarPerfil();
      return;
    }

    if (
      e.target &&
      (e.target.id === 'cerrarModalEditarPerfil' ||
       e.target.id === 'cancelarEditarPerfil' ||
       e.target.id === 'modalEditarPerfil')
    ) {
      closeEditarPerfil();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeEditarPerfil();
  });
</script>
