<?php
// Preparar variables visuales (BLOB/Base64)
$nombreActual = $_SESSION['nombre'] ?? '';
$bioActual    = $_SESSION['biografia'] ?? '';

// 1. AVATAR
$fotoSession = $_SESSION['foto_perfil'] ?? null;
if ($fotoSession) {
    // Asumimos que en sesión ya está guardado en Base64 (sin prefijo)
    $srcAvatar = 'data:image/jpeg;base64,' . $fotoSession;
} else {
    $srcAvatar = '../multimedia/file.svg'; // Imagen por defecto
}

// 2. PORTADA
$portadaSession = $_SESSION['portada'] ?? null;
if ($portadaSession) {
    // Si la portada es un archivo antiguo (texto corto), mantenemos compatibilidad, si es largo es Base64
    if (strlen($portadaSession) < 255 && strpos($portadaSession, '.') !== false) {
         $srcPortada = '../multimedia/' . $portadaSession;
    } else {
         $srcPortada = 'data:image/jpeg;base64,' . $portadaSession;
    }
} else {
    $srcPortada = '../multimedia/file.svg'; // Imagen por defecto
}
?>

<div id="modalEditarPerfil" class="modal-overlay" aria-hidden="true">
  <div class="modal modal-edit">
    <div class="modal-header">
      <h3>Editar perfil</h3>
      <button type="button" id="cerrarModalEditarPerfil" class="btn-close">×</button>
    </div>

    <form id="formEditarPerfil" method="POST" action="../php/editarPerfil.php" enctype="multipart/form-data">
      <div class="edit-visuals">
        
        <div class="edit-cover-wrap">
          <div class="edit-cover-overlay"><label for="inputPortada" class="camera-btn">📷</label></div>
          <img id="previewPortada" src="<?php echo $srcPortada; ?>" class="edit-cover-img" style="object-fit:cover;"> 
          <input type="file" id="inputPortada" name="portada" accept="image/*" hidden>
        </div>

        <div class="edit-avatar-wrap">
          <div class="edit-avatar-overlay"><label for="inputAvatar" class="camera-btn">📷</label></div>
          <img id="previewAvatar" src="<?php echo $srcAvatar; ?>" class="edit-avatar-img" style="object-fit:cover;">
          <input type="file" id="inputAvatar" name="foto_perfil" accept="image/*" hidden>
        </div>

      </div>

      <div class="modal-body edit-body">
        <div class="input-group-neon">
          <label>Nombre</label>
          <input type="text" name="nombre" id="ep-nombre" value="<?php echo htmlspecialchars($nombreActual); ?>" required maxlength="50">
        </div>
        <div class="input-group-neon">
          <label>Biografía</label>
          <textarea name="bio" id="ep-bio" rows="3" maxlength="160"><?php echo htmlspecialchars($bioActual); ?></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-danger" id="cancelarEditarPerfil" style="background:transparent; border:1px solid var(--border); color:var(--text); margin-right:auto;">Cancelar</button>
        <button type="submit" class="boton-registrarse" id="guardarEditarPerfil">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
// UI: Abrir/Cerrar Modal
function toggleModalEdit(show) {
    const m = document.getElementById('modalEditarPerfil');
    if(m) {
        m.classList.toggle('abierto', show);
        document.body.classList.toggle('modal-open', show);
    }
}

// LISTENERS
document.addEventListener('click', e => {
    if(e.target.closest('#botonEditarPerfil')) toggleModalEdit(true);
    if(e.target.closest('#cerrarModalEditarPerfil, #cancelarEditarPerfil') || e.target.id === 'modalEditarPerfil') toggleModalEdit(false);
});

// PREVIEW IMÁGENES (FileReader local)
['inputPortada', 'inputAvatar'].forEach(id => {
    const el = document.getElementById(id);
    if(el) {
        el.addEventListener('change', function() {
            if(this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const imgId = id === 'inputPortada' ? 'previewPortada' : 'previewAvatar';
                    document.getElementById(imgId).src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});

// ENVÍO AJAX Y ACTUALIZACIÓN DOM
const formEdit = document.getElementById('formEditarPerfil');
if(formEdit) {
    formEdit.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('guardarEditarPerfil');
        const txtOriginal = btn.textContent;
        btn.textContent = 'Guardando...';
        btn.disabled = true;

        try {
            const formData = new FormData(this);
            const res = await fetch(this.action, { method: 'POST', body: formData });
            const data = await res.json();

            if(data.ok) {
                // 1. ACTUALIZAR TEXTOS
                const nuevoNombre = document.getElementById('ep-nombre').value;
                const nuevaBio = document.getElementById('ep-bio').value;

                document.querySelectorAll('.nombre-real').forEach(el => el.textContent = nuevoNombre);
                document.querySelectorAll('.bio-perfil, #perfilBio').forEach(el => el.textContent = nuevaBio);

                // 2. ACTUALIZAR AVATAR (Si volvió del servidor)
                if(data.foto_perfil) {
                    // Ahora data.foto_perfil es el string Base64, no una ruta
                    const newSrc = 'data:image/jpeg;base64,' + data.foto_perfil;
                    
                    document.querySelectorAll('.avatar img, .info-perfil img, .edit-avatar-img').forEach(img => img.src = newSrc);
                    // Actualizar variable global si existe
                    if(typeof window.USER_AVATAR !== 'undefined') window.USER_AVATAR = newSrc;
                }

                // 3. ACTUALIZAR PORTADA (Si volvió del servidor)
                if(data.portada) {
                    const newCover = 'data:image/jpeg;base64,' + data.portada;
                    const banner = document.querySelector('.banner');
                    if(banner) banner.style.backgroundImage = `url('${newCover}')`;
                    // Actualizar también el preview del modal por si lo vuelve a abrir
                    const previewP = document.getElementById('previewPortada');
                    if(previewP) previewP.src = newCover;
                }

                toggleModalEdit(false);
            } else {
                alert('Error: ' + (data.error || 'No se pudo guardar'));
            }
        } catch(err) {
            console.error(err);
            alert('Error de conexión');
        } finally {
            btn.textContent = txtOriginal;
            btn.disabled = false;
        }
    });
}
</script>