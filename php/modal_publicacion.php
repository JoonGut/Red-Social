<style>
  /* Contenedor principal de comentarios */
  .comments-section {
    border-top: 1px solid #2a2a3c;
    margin-top: 15px;
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    height: 100%; /* Ocupar resto del modal */
    min-height: 200px;
  }

  /* Lista Scrollable */
  .comments-list {
    flex: 1;
    overflow-y: auto;
    padding-right: 5px;
    margin-bottom: 10px;
    max-height: 300px; /* Altura máxima antes de scroll */
  }

  /* Items individuales */
  .comment-item {
    padding: 10px 0;
    border-bottom: 1px solid #1e1e2f;
    display: flex;
    gap: 10px;
    animation: fadeIn 0.3s ease;
  }
  .comment-item.reply {
    margin-left: 40px; /* Sangría para respuestas */
    border-left: 2px solid #333;
    padding-left: 10px;
    background: rgba(255,255,255,0.02);
  }

  .comment-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    object-fit: cover;
    background: #333;
  }

  .comment-content { flex: 1; }
  
  .comment-header {
    display: flex; justify-content: space-between;
    font-size: 0.85rem; margin-bottom: 4px;
  }
  .comment-user { font-weight: bold; color: #fff; }
  .comment-date { color: #888; font-size: 0.75rem; }
  
  .comment-text { color: #ddd; font-size: 0.9rem; line-height: 1.4; word-break: break-word; }

  .comment-actions {
    margin-top: 5px;
    display: flex; gap: 15px;
  }
  .btn-reply-comment {
    background: none; border: none; color: #888;
    font-size: 0.8rem; cursor: pointer; padding: 0;
  }
  .btn-reply-comment:hover { color: #00bcd4; text-decoration: underline; }

  /* Input Fijo Abajo */
  .comment-input-area {
    background: #151520;
    padding: 10px;
    border-top: 1px solid #333;
    position: sticky; bottom: 0;
  }

  /* Banner "Respondiendo a..." */
  .replying-banner {
    background: #1e1e2f;
    color: #888;
    font-size: 0.8rem;
    padding: 5px 10px;
    margin-bottom: 5px;
    border-radius: 4px;
    display: flex; justify-content: space-between; align-items: center;
  }
  .replying-banner strong { color: #00bcd4; }
  .btn-cancel-reply { background:none; border:none; color:#ff4757; cursor:pointer; }

  .input-group { display: flex; gap: 10px; }
  .input-comment {
    flex: 1;
    background: #000; border: 1px solid #333;
    color: #fff; padding: 8px 12px;
    border-radius: 20px; outline: none;
  }
  .input-comment:focus { border-color: #00bcd4; }
  .btn-send-comment {
    background: #00bcd4; color: #000;
    border: none; padding: 0 15px;
    border-radius: 20px; font-weight: bold; cursor: pointer;
  }
  .btn-send-comment:disabled { background: #555; cursor: not-allowed; }

  @keyframes fadeIn { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:translateY(0); } }
</style>

<div id="modalPublicacion" class="modal-overlay" aria-hidden="true">
  <div class="modal modal-post">
    <div class="modal-header modal-header-post">
      <div class="post-head">
        <div class="post-head-txt">
          <a id="m-user" class="post-user" href="#" role="link">@usuario</a>
          <div id="m-fecha" class="post-meta"></div>
        </div>
      </div>
      <button id="cerrarModal" type="button" class="btn-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body" style="display:flex; flex-direction:column; max-height:80vh;">
      <article class="post-modal" style="flex-shrink: 0;">
        <p id="m-texto" class="post-text"></p>
        <div id="m-ubicacion" class="post-location" style="display:none;"></div>
        <div class="post-media" id="m-img-wrap" style="display:none;">
          <img id="m-img" alt="Imagen publicación">
        </div>
        <p id="m-pie" class="post-caption"></p>

        </article>

      <section class="comments-section">
        <h4 style="margin:0 0 10px 0; color:#888; font-size:0.9rem;">Comentarios</h4>
        
        <div id="listaComentarios" class="comments-list">
          <div style="text-align:center; color:#555; padding:20px;">Cargando...</div>
        </div>

        <div class="comment-input-area">
          <div id="replyBanner" class="replying-banner" style="display:none;">
            <span>Respondiendo a <strong id="replyUserTarget">@usuario</strong></span>
            <button id="cancelReply" class="btn-cancel-reply">✕</button>
          </div>
          
          <form id="formComentario" class="input-group" autocomplete="off">
            <input type="text" id="inputComentario" class="input-comment" placeholder="Postea tu respuesta" maxlength="280">
            <button type="submit" class="btn-send-comment">Enviar</button>
          </form>
        </div>
      </section>

    </div>
  </div>
</div>

<script>
  // --- VARIABLES GLOBALES DEL MODAL ---
  let currentPostId = 0;
  let replyToId = null; 

  function escapeHtml(str) {
    return (str ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  // --- ABRIR MODAL ---
  function openPostModal(article) {
    const modal = document.getElementById('modalPublicacion');
    if (!modal) return;

    // 1. Cargar datos visuales
    const usuario = article.dataset.usuario || '';
    const fecha = article.dataset.fecha || '';
    const ubicacion = article.dataset.ubicacion || '';
    const texto = article.dataset.texto || '';
    const img = article.dataset.img || '';
    const pie = article.dataset.pie || '';
    const postId = article.dataset.id || '';
    
    currentPostId = postId;
    replyToId = null; 
    updateReplyUI();

    const userEl = document.getElementById('m-user');
    if (userEl) {
      userEl.textContent = usuario ? `@${usuario}` : '@usuario';
      userEl.href = usuario ? `../php/perfil_usuario.php?u=${encodeURIComponent(usuario)}` : '#';
      userEl.onclick = (ev) => {
        if (!usuario) return;
        if (typeof window.loadUserProfile === 'function') {
          ev.preventDefault();
          closePostModal();
          window.loadUserProfile(usuario);
        }
      };
    }

    const fechaEl = document.getElementById('m-fecha');
    if (fechaEl) fechaEl.textContent = fecha;

    const textoEl = document.getElementById('m-texto');
    if (textoEl) {
      textoEl.innerHTML = escapeHtml(texto).replace(/\n/g, '<br>');
      textoEl.style.display = texto ? '' : 'none';
    }

    const ubiEl = document.getElementById('m-ubicacion');
    if (ubiEl) {
        ubiEl.innerHTML = ubicacion ? `📍 ${escapeHtml(ubicacion)}` : '';
        ubiEl.style.display = ubicacion ? '' : 'none';
    }

    const imgWrap = document.getElementById('m-img-wrap');
    const imgEl = document.getElementById('m-img');
    if (imgWrap && imgEl) {
      if (img) {
        imgWrap.style.display = '';
        imgEl.src = img;
      } else {
        imgWrap.style.display = 'none';
      }
    }

    const pieEl = document.getElementById('m-pie');
    if (pieEl) {
        pieEl.textContent = pie;
        pieEl.style.display = pie ? '' : 'none';
    }

    // 2. CARGAR COMENTARIOS (AJAX)
    loadComments(postId);

    // Mostrar modal
    modal.classList.add('abierto');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }

  function closePostModal() {
    const modal = document.getElementById('modalPublicacion');
    if (!modal) return;
    modal.classList.remove('abierto');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }

  // --- LÓGICA DE COMENTARIOS ---

  async function loadComments(idPub) {
    const lista = document.getElementById('listaComentarios');
    lista.innerHTML = '<div style="text-align:center; padding:10px; color:#666;">Cargando...</div>';

    try {
        const res = await fetch(`../php/get_comentarios.php?id_publicacion=${idPub}`);
        
        // Verificamos texto plano primero para evitar errores HTML
        const text = await res.text();
        
        try {
            const json = JSON.parse(text); 
            if (!json.ok) {
                console.error("Error lógico:", json);
                lista.innerHTML = '<div style="color:red; text-align:center;">Error al cargar</div>';
                return;
            }
            renderComments(json.items);
        } catch (errJSON) {
            console.error("PHP Error CRÍTICO:", text); 
            lista.innerHTML = `<div style="color:red; text-align:center; font-size:0.8rem;">
                Error del servidor. Revisa consola.
            </div>`;
        }
    } catch (e) {
        console.error("Error de red:", e);
        lista.innerHTML = '<div style="color:red; text-align:center;">Error de conexión</div>';
    }
  }

  function renderComments(items) {
    const lista = document.getElementById('listaComentarios');
    lista.innerHTML = '';

    if (!items || items.length === 0) {
        lista.innerHTML = '<div style="text-align:center; color:#555; padding:20px;">Sé el primero en comentar.</div>';
        return;
    }
    
    const padres = items.filter(i => !i.id_padre);
    const hijos = items.filter(i => i.id_padre);

    padres.forEach(padre => {
        lista.appendChild(createCommentElement(padre, false));
        const misHijos = hijos.filter(h => h.id_padre == padre.id_comentario);
        misHijos.forEach(hijo => {
            lista.appendChild(createCommentElement(hijo, true));
        });
    });
    
    setTimeout(() => lista.scrollTop = lista.scrollHeight, 100);
  }

  function createCommentElement(data, isReply) {
    const div = document.createElement('div');
    div.className = `comment-item ${isReply ? 'reply' : ''}`;
    
    const avatarUrl = data.foto_perfil ? `../multimedia/${data.foto_perfil}` : '../multimedia/file.svg';

    div.innerHTML = `
        <img src="${avatarUrl}" class="comment-avatar" alt="Avatar">
        <div class="comment-content">
            <div class="comment-header">
                <span class="comment-user">@${escapeHtml(data.usuario)}</span>
                <span class="comment-date">${data.creado_en.substring(5, 16)}</span>
            </div>
            <div class="comment-text">${escapeHtml(data.texto)}</div>
            <div class="comment-actions">
                <button class="btn-reply-comment" 
                    data-id="${data.id_comentario}" 
                    data-user="${escapeHtml(data.usuario)}">
                    💬 Responder
                </button>
            </div>
        </div>
    `;

    div.querySelector('.btn-reply-comment').addEventListener('click', (e) => {
        replyToId = e.target.dataset.id;
        const user = e.target.dataset.user;
        updateReplyUI(user);
        document.getElementById('inputComentario').focus();
    });

    return div;
  }

  function updateReplyUI(username = null) {
    const banner = document.getElementById('replyBanner');
    const target = document.getElementById('replyUserTarget');
    const input = document.getElementById('inputComentario');

    if (replyToId && username) {
        banner.style.display = 'flex';
        target.textContent = `@${username}`;
        input.placeholder = `Respondiendo a @${username}`;
    } else {
        banner.style.display = 'none';
        replyToId = null;
        input.placeholder = "Postea tu respuesta";
    }
  }

  document.getElementById('cancelReply').addEventListener('click', () => updateReplyUI(null));

  // --- ENVIAR COMENTARIO ---
  document.getElementById('formComentario').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('inputComentario');
    const texto = input.value.trim();
    if (!texto) return;

    const btn = e.target.querySelector('button');
    btn.disabled = true;
    btn.textContent = '...';

    const formData = new URLSearchParams();
    formData.append('id_publicacion', currentPostId);
    formData.append('texto', texto);
    if (replyToId) formData.append('id_padre', replyToId);

    try {
        const res = await fetch('../php/comentar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        });

        const text = await res.text();
        
        try {
            const json = JSON.parse(text);
            if (json.ok) {
                input.value = '';
                updateReplyUI(null); 
                loadComments(currentPostId); 
            } else {
                alert('Error: ' + (json.error || 'Desconocido'));
            }
        } catch (jsonErr) {
            console.error("No es JSON válido:", text);
            alert("Error crítico en el servidor.");
        }
    } catch (err) {
        console.error("Error de red:", err);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar';
    }
  });


  // --- EVENTOS GLOBALES ---
  document.addEventListener('click', (e) => {
    if (e.target.closest('a.user-link')) return;

    // Detectar clic en publicación del feed
    const article = e.target.closest('article.publicaciones');
    if (article) {
      openPostModal(article);
      return;
    }

    if (e.target.id === 'modalPublicacion' || e.target.id === 'cerrarModal') {
      closePostModal();
    }
  }, true);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePostModal();
  });
</script>