<div id="modalOverlay" class="modal-overlay" style="display:none;" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
    
    <div class="modal-header">
      <h3>Nueva Publicación</h3>
      <button id="cerrarModal" class="modal-close" type="button" aria-label="Cerrar">✕</button>
    </div>

    <form action="guardar_publicacion.php" method="POST" enctype="multipart/form-data" class="modal-body">
      
      <div style="position:relative;">
          <textarea id="textoPub" name="texto" placeholder="¿Qué está pasando?&#10;Usa @ para mencionar amigos..." required maxlength="250"></textarea>
          
          <div id="sugerenciasMencion" 
               style="position:absolute; top:100%; left:20px; right:20px; background:var(--card); border:1px solid var(--border); border-radius:0 0 10px 10px; display:none; z-index:100; box-shadow:var(--shadow);">
          </div>
      </div>

      <div class="modal-row">
        <div style="flex:1; position:relative;">
            <i class="fas fa-map-marker-alt" style="position:absolute; left:12px; top:11px; color:#f91880; z-index:2;"></i>
            <input type="text" name="ubicacion" placeholder="Ubicación" maxlength="50" style="padding-left:32px;">
        </div>
        
        <div style="flex:1; position:relative;">
            <i class="fas fa-tag" style="position:absolute; left:12px; top:11px; color:#ffd400; z-index:2;"></i>
            <input type="text" name="pie_foto" placeholder="Pie de foto" maxlength="50" style="padding-left:32px;">
        </div>
      </div>

      <div class="modal-footer" style="justify-content:space-between;">
        
        <div>
            <input type="file" name="imagen" id="file-upload" accept="image/jpeg,image/png,image/webp" hidden>
            <label for="file-upload" class="custom-file-upload">
              <i class="fas fa-image" style="font-size:18px;"></i> 
              <span>Foto</span>
            </label>
            <span id="nombreArchivo" style="font-size:12px; color:var(--accent2); margin-left:10px;"></span>
        </div>

        <div style="display:flex; align-items:center; gap:15px;">
            <small id="contador" style="font-family:monospace; color:var(--muted);">0/250</small>
            
            <button class="boton-registrarse" type="submit">
                Publicar
            </button>
        </div>
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
    var sugerencias = document.getElementById('sugerenciasMencion');
    var fileInput = document.getElementById('file-upload');
    
    
    if (abrir) {
        abrir.onclick = function() {
            overlay.style.display = 'flex';
            if(texto) setTimeout(() => texto.focus(), 100);
        };
    }

    if (cerrar) cerrar.onclick = () => overlay.style.display = 'none';
    
    overlay.onclick = (e) => {
        if (e.target === overlay) overlay.style.display = 'none';
    };

    
    if (texto) {
        texto.oninput = function() {
          document.getElementById('contador').innerText = texto.value.length + '/250';
          detectarMencion(this);
        };
    }

    
    if (fileInput) {
        fileInput.onchange = function() {
            if(this.files && this.files[0]) {
                document.getElementById('nombreArchivo').textContent = this.files[0].name;
            }
        };
    }

    
    function detectarMencion(input) {
        const val = input.value;
        const cursorPos = input.selectionStart;
        const textBeforeCursor = val.substring(0, cursorPos);
        const lastWordMatch = textBeforeCursor.match(/@(\w*)$/);

        if (lastWordMatch) {
            const query = lastWordMatch[1];
            if(query.length > 0) {
                buscarUsuarios(query);
            } else {
                sugerencias.style.display = 'none';
            }
        } else {
            sugerencias.style.display = 'none';
        }
    }

    function buscarUsuarios(q) {
        
        fetch(`ajax_buscar_usuarios_simple.php?q=${q}`)
            .then(r => r.json())
            .then(users => {
                if(users.length > 0) {
                    let html = '';
                    users.forEach(u => {
                        
                        html += `<div class="sug-item" onclick="insertarMencion('${u.usuario}')" 
                                      style="padding:10px; cursor:pointer; border-bottom:1px solid var(--border); color:var(--text); display:flex; align-items:center;">
                                    <img src="${u.foto ? '../multimedia/'+u.foto : '../multimedia/file.svg'}" style="width:20px; height:20px; border-radius:50%; margin-right:8px; object-fit:cover;"> 
                                    <strong>@${u.usuario}</strong>
                                 </div>`;
                    });
                    sugerencias.innerHTML = html;
                    sugerencias.style.display = 'block';
                } else {
                    sugerencias.style.display = 'none';
                }
            })
            .catch(() => sugerencias.style.display = 'none');
    }

    
    window.insertarMencion = function(username) {
        const val = texto.value;
        const cursorPos = texto.selectionStart;
        const textBefore = val.substring(0, cursorPos);
        const textAfter = val.substring(cursorPos);
        
        const newTextBefore = textBefore.replace(/@(\w*)$/, '@' + username + ' ');
        
        texto.value = newTextBefore + textAfter;
        sugerencias.style.display = 'none';
        texto.focus();
    };
  });
</script>