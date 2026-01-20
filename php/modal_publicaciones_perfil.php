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
  // Función para abrir el modal
  function openPerfilPostModal(item) {
    const modal = document.getElementById('modalPublicacionPerfil');
    if (!modal) return;

    // 1. OBTENER DATOS (Incluyendo el ID)
    const imgUrl = item.dataset.img || '';
    const pie = item.dataset.pie || '';
    const desc = item.dataset.desc || '';
    const fecha = item.dataset.fecha || '';
    const id = item.dataset.id || ''; // <--- IMPORTANTE: Recuperar el ID

    // 2. ELEMENTOS DEL DOM
    const imgWrap = document.getElementById('p-img-wrap');
    const img = document.getElementById('p-img');
    const pieEl = document.getElementById('p-pie');
    const descEl = document.getElementById('p-desc');
    const fechaEl = document.getElementById('p-fecha');
    const btnBorrar = document.getElementById('borrarPublicacion');

    // 3. ASIGNAR VALORES
    if (descEl) {
      descEl.textContent = desc;
      descEl.style.display = desc ? '' : 'none';
    }

    if (imgWrap && img) {
      if (imgUrl) {
        imgWrap.style.display = '';
        img.src = imgUrl; // Quitamos el timestamp para evitar recargas innecesarias
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
      else { fechaEl.style.display = 'none'; }
    }

    // 4. PASAR EL ID AL BOTÓN DE BORRAR (Aquí estaba el fallo)
    if (btnBorrar) {
        btnBorrar.dataset.id = id;
    }

    // 5. MOSTRAR MODAL
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

  // --- EVENT LISTENER GLOBAL (Delegación) ---
  document.addEventListener('click', (e) => {
    const target = e.target;
    if (!target) return;

    // Detectar clic en un item de la grilla del perfil
    const item = target.closest('.grid-item');
    if (item) {
      openPerfilPostModal(item);
      return;
    }

    // Detectar clic en cerrar modal o fuera del contenido
    if (target.id === 'modalPublicacionPerfil' || target.id === 'cerrarModalPublicacionPerfil') {
      closePerfilPostModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePerfilPostModal();
  });

  // --- LÓGICA DE BORRADO ---
  const btnBorrar = document.getElementById('borrarPublicacion');
  if (btnBorrar) {
    btnBorrar.addEventListener('click', () => {
        const postId = btnBorrar.dataset.id;
        
        if (!postId) {
            alert("Error: No se ha identificado la publicación.");
            return;
        }

        if(!confirm("¿Estás seguro de que quieres eliminar esta publicación?")) return;

        fetch('../php/eliminar_publicacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(postId)}`
        })
        .then(res => res.text())
        .then(res => {
            const respuesta = res.trim();
            if (respuesta === 'ok') {
                // 1. Cerrar el modal correcto
                closePerfilPostModal();
                
                // 2. Eliminar el elemento de la grilla visualmente
                const gridItem = document.querySelector(`.grid-item[data-id="${postId}"]`);
                if (gridItem) {
                    gridItem.remove(); 
                } else {
                    // Si no lo encuentra por alguna razón, recargamos para asegurar
                    location.reload();
                }
            } else {
                alert('Error al eliminar: ' + respuesta);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión con el servidor');
        });
    });
  }

})();
</script>