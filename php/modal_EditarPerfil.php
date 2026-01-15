<?php
// modal_EditarPerfil.php
// Asegúrate de que session_start() ya se haya hecho antes (en perfil.php)
$nombreActual = $_SESSION['nombre'] ?? '';
$bioActual = $_SESSION['biografia'] ?? '';
?>
<div id="modalEditarPerfil" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="ep-titulo">

    <form id="formEditarPerfil" method="POST" action="editarPerfil.php">
      <div class="modal-header">
        <div></div>
        <h2 id="ep-titulo">Editar perfil</h2>
        <button type="button" id="cerrarModalEditarPerfil" aria-label="Cerrar">×</button>
      </div>

      <div class="modal-body">
        <label>
          Nombre
          <input
            type="text"
            name="nombre"
            id="ep-nombre"
            placeholder="Tu nombre"
            value="<?php echo htmlspecialchars($nombreActual); ?>"
            required
          >
        </label>

        <label>
          Bio
          <textarea
            name="bio"
            id="ep-bio"
            placeholder="Cuéntanos algo"
          ><?php echo htmlspecialchars($bioActual); ?></textarea>
        </label>

        <div class="modal-footer">
          <button type="button" class="boton-cancelar" id="cancelarEditarPerfil">Cancelar</button>
          <button type="submit" class="boton-registrarse" id="guardarEditarPerfil">Guardar</button>
        </div>
      </div>
    </form>

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
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formEditarPerfil');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault(); // ✅ evita cambio de página

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form)
        });

        const data = await res.json();

        if (!data.ok) {
          alert(data.error || 'Error al guardar');
          return;
        }

        // ✅ opcional: actualizar texto en la página sin recargar
        const nombreEl = document.getElementById('perfilNombre');
        const bioEl = document.getElementById('perfilBio');
        if (nombreEl && data.nombre) nombreEl.textContent = data.nombre;
        if (bioEl && data.biografia !== undefined) bioEl.textContent = data.biografia;

        // ✅ cerrar modal
        closeEditarPerfil();

      } catch (err) {
        console.error(err);
        alert('Error de red');
      }
    });
  });
</script>
