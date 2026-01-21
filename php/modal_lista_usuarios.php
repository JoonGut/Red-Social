<div id="modalListaUsuarios" class="modal-overlay" style="display: none;">
  <div class="modal-content" style="max-width: 600px; padding: 0; overflow: hidden; display: flex; flex-direction: column; max-height: 80vh;">
    
    <div class="modal-header-tabs" style="display: flex; border-bottom: 1px solid #333; background: #000;">
      <button id="tabSeguidores" class="tab-btn" onclick="cambiarTab('seguidores')" style="flex: 1; padding: 15px; background: none; border: none; color: white; cursor: pointer; font-weight: bold; border-bottom: 3px solid transparent;">
        Seguidores
      </button>
      <button id="tabSiguiendo" class="tab-btn" onclick="cambiarTab('siguiendo')" style="flex: 1; padding: 15px; background: none; border: none; color: white; cursor: pointer; font-weight: bold; border-bottom: 3px solid transparent;">
        Siguiendo
      </button>
      <button onclick="cerrarModalUsuarios()" style="padding: 0 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>

    <div id="contenedorLista" style="overflow-y: auto; flex: 1; padding: 10px; background: #111;">
      <p style="text-align:center; color: #777;">Cargando...</p>
    </div>

  </div>
</div>

<style>
  /* Estilo para la tab activa */
  .tab-btn.active {
    border-bottom-color: var(--neon-blue, #00f3ff) !important;
    color: var(--neon-blue, #00f3ff) !important;
  }
  /* Estilo para cada fila de usuario */
  .user-row {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #333;
    transition: background 0.2s;
  }
  .user-row:hover { background: #1a1a1a; }
  .user-info { flex: 1; margin-left: 10px; }
  .user-info h4 { margin: 0; color: white; font-size: 1rem; }
  .user-info span { color: #888; font-size: 0.9rem; }
  .user-bio { color: #ccc; font-size: 0.85rem; margin-top: 4px; display: block; }
</style>