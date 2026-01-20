<?php
// Recuperamos datos actuales de la sesión para rellenar el formulario
$nombreActual = $_SESSION['nombre'] ?? '';
$bioActual = $_SESSION['biografia'] ?? '';
$fotoActual = $_SESSION['foto_perfil'] ?? null;
// Si tienes portada en la BD, recupérala aquí. Si no, usa un default.
$portadaActual = $_SESSION['portada'] ?? 'fondo_default.jpg'; 
?>

<div id="modalEditarPerfil" class="modal-overlay" aria-hidden="true">
  <div class="modal modal-edit">
    
    <div class="modal-header">
      <h3 style="margin:0; font-size:18px; color: #fff;">Editar perfil</h3>
      <button type="button" id="cerrarModalEditarPerfil" class="btn-close" aria-label="Cerrar">×</button>
    </div>

    <form id="formEditarPerfil" method="POST" action="../php/editarPerfil.php" enctype="multipart/form-data">
      
      <div class="edit-visuals">
        
        <div class="edit-cover-wrap">
          <div class="edit-cover-overlay">
            <label for="inputPortada" class="camera-btn" title="Cambiar portada">📷</label>
          </div>
          <img id="previewPortada" 
               src="../multimedia/<?php echo htmlspecialchars($portadaActual); ?>" 
               class="edit-cover-img" 
               alt="Portada"
               onerror="this.src='../multimedia/fondo_default.jpg'">
          <input type="file" id="inputPortada" name="portada" accept="image/*" hidden>
        </div>

        <div class="edit-avatar-wrap">
          <div class="edit-avatar-overlay">
            <label for="inputAvatar" class="camera-btn" title="Cambiar foto">📷</label>
          </div>
          <img id="previewAvatar" 
               src="<?php echo $fotoActual ? '../multimedia/'.htmlspecialchars($fotoActual) : '../multimedia/file.svg'; ?>" 
               class="edit-avatar-img" 
               alt="Avatar">
          <input type="file" id="inputAvatar" name="foto_perfil" accept="image/*" hidden>
        </div>

      </div>

      <div class="modal-body edit-body">
        
        <div class="input-group-neon">
          <label>Nombre</label>
          <input 
            type="text" 
            name="nombre" 
            id="ep-nombre" 
            placeholder="Tu nombre" 
            value="<?php echo htmlspecialchars($nombreActual); ?>" 
            required
            maxlength="50"
          >
        </div>

        <div class="input-group-neon">
          <label>Biografía</label>
          <textarea 
            name="bio" 
            id="ep-bio" 
            placeholder="Cuéntanos algo sobre ti..." 
            rows="3" 
            maxlength="160"
          ><?php echo htmlspecialchars($bioActual); ?></textarea>
        </div>

        </div>

      <div class="modal-footer">
        <button type="button" class="btn-danger" id="cancelarEditarPerfil" style="background:transparent; border:1px solid #333; margin-right:auto;">Cancelar</button>
        <button type="submit" class="boton-registrarse" id="guardarEditarPerfil">Guardar cambios</button>
      </div>
    </form>

  </div>
</div>

<script>
// 1. LÓGICA DE APERTURA / CIERRE
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

// Eventos de click globales
document.addEventListener('click', (e) => {
    // Abrir (si tienes un botón con id 'botonEditarPerfil' en tu perfil.php)
    if (e.target && e.target.closest('#botonEditarPerfil')) {
        openEditarPerfil();
        return;
    }

    // Cerrar
    if (e.target && (
        e.target.id === 'cerrarModalEditarPerfil' ||
        e.target.id === 'cancelarEditarPerfil' ||
        e.target.id === 'modalEditarPerfil'
    )) {
        closeEditarPerfil();
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeEditarPerfil();
});

// 2. PREVISUALIZACIÓN DE IMÁGENES (Nuevo)
function readURL(input, imgId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(imgId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('inputPortada')?.addEventListener('change', function() {
    readURL(this, 'previewPortada');
});
document.getElementById('inputAvatar')?.addEventListener('change', function() {
    readURL(this, 'previewAvatar');
});

// 3. ENVÍO DEL FORMULARIO (AJAX - Tu lógica original adaptada)
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formEditarPerfil');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault(); 
        
        // Efecto visual de "Cargando" en el botón
        const btnGuardar = document.getElementById('guardarEditarPerfil');
        const txtOriginal = btnGuardar.textContent;
        btnGuardar.textContent = 'Guardando...';
        btnGuardar.disabled = true;

        try {
            // FormData captura textos Y archivos automáticamente
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            // Intentamos parsear la respuesta
            let data;
            try {
                data = await res.json();
            } catch (jsonErr) {
                console.error("Error JSON:", await res.text()); // Debug por si PHP falla
                throw new Error("Respuesta inválida del servidor");
            }

            if (!data.ok) {
                alert(data.error || 'Error al guardar');
                return;
            }

            // --- ACTUALIZAR LA UI AL INSTANTE ---
            
            // 1. Textos
            const nombreEl = document.getElementById('perfilNombre'); // Asegúrate que tu perfil.php tiene este ID
            const bioEl = document.getElementById('perfilBio');       // Asegúrate que tu perfil.php tiene este ID
            
            if (nombreEl && data.nombre) nombreEl.textContent = data.nombre;
            if (bioEl && data.biografia !== undefined) bioEl.textContent = data.biografia;

            // 2. Imágenes (Si el servidor devuelve las nuevas rutas)
            if (data.foto_perfil) {
                // Actualiza todas las fotos de perfil que veas en la pantalla
                document.querySelectorAll('.avatar-img-class-o-id').forEach(img => {
                    img.src = '../multimedia/' + data.foto_perfil + '?t=' + new Date().getTime();
                });
            }

            // Cerrar modal
            closeEditarPerfil();

            // Opcional: recargar si quieres asegurar que todo se ve bien (fotos, etc)
            // location.reload(); 

        } catch (err) {
            console.error(err);
            alert('Ocurrió un error al intentar guardar.');
        } finally {
            // Restaurar botón
            btnGuardar.textContent = txtOriginal;
            btnGuardar.disabled = false;
        }
    });
});
</script>