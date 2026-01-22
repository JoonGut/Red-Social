<div id="modalListaUsuarios" class="modal-overlay">
  <div class="modal-content">
    
    <div class="modal-header-tabs">
      <button id="tabSeguidores" class="tab-btn" onclick="cambiarTab('seguidores')">
        Seguidores
      </button>
      <button id="tabSiguiendo" class="tab-btn" onclick="cambiarTab('siguiendo')">
        Siguiendo
      </button>
      <button class="btn-cerrar-modal-user" onclick="cerrarModalUsuarios()" aria-label="Cerrar">&times;</button>
    </div>

    <div id="contenedorLista">
      <p style="text-align:center; padding:20px; color:var(--muted);">Cargando...</p>
    </div>

  </div>
</div>