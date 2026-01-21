<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>NeonNest</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../css/modal.css">
  <link rel="icon" href="../multimedia/file.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <div class="bg-orbs" aria-hidden="true"></div>

  <div class="contenedor-inicio">
    <aside class="barra-lateral">
      <div class="logo-red">
        <div class="logo-mark">
          <img src="../multimedia/file.svg" alt="Logo">
        </div>
        <div class="logo-copy">
          <strong>NeonNest</strong>
          <small>Corporation</small>
        </div>
        <div class="noti-container">
          <button id="btnNoti" class="btn-noti">
            🔔
            <span id="badgeNoti" class="badge-noti" style="display:none">0</span>
          </button>

          <div id="listaNoti" class="dropdown-noti" style="display:none">
            <div class="dropdown-header">Notificaciones</div>
            <div id="contenidoNoti" class="dropdown-content">
              <p class="noti-empty">No hay novedades.</p>
            </div>
          </div>
        </div>
      </div>

      <nav class="menu">
        <a href="index.php" class="menu-item activo">Inicio</a>
        <a href="#" class="menu-item" data-page="explorar">Explorar</a>
        <a href="#" class="menu-item" data-page="chat">Mensajes</a>
        <a href="#" class="menu-item" data-page="perfil">Perfil</a>
      </nav>

      <div class="sidebar-cta">
        <button id="abrirModal" class="boton-registrarse" type="button">✨ Publicar</button>
        <p class="sidebar-tip">Comparte algo brillante hoy.</p>
      </div>

      <div class="sidebar-footer">
        <small>© <?php echo date('Y'); ?> Cloudia</small>
      </div>
    </aside>

    <main class="contenido-principal">
      <header class="cabecera">
        <div class="cabecera-left">
          <h1>Inicio</h1>
          <p class="cabecera-sub">Tu feed está vivo ahora mismo</p>
        </div>

        <div class="cabecera-right">
          <label class="buscador" aria-label="Buscar">
            <span class="buscador-ico">🔎</span>
            <input type="search" id="inputBusquedaGlobal" placeholder="Buscar..." />
          </label>
          <div class="cabecera-right">
            <label class="buscador">...</label>
          </div>
          <div id="toastContainer" class="toast-container"></div>
        </div>
      </header>

      <section class="crear-publicacion">
        <div class="composer">
          <div class="avatar">😊</div>
          <button class="composer-input" type="button" id="abrirModalQuick">
            ¿Qué quieres publicar?
          </button>
          <button class="boton-registrarse boton-publicar" type="button" id="abrirModalQuick2">
            Publicar
          </button>
        </div>
      </section>

      <section class="feed">
        <?php include __DIR__ . '/feedPublicaciones.php'; ?>
      </section>
    </main>

    <aside class="barra-derecha">
      <section class="panel">
        <h2>🔥 Tendencias</h2>
        <ul>
          <li><span class="tag">#Tecnología</span></li>
          <li><span class="tag">#Ciencia</span></li>
          <li><span class="tag">#DiseñoWeb</span></li>
          <li><span class="tag">#Programación</span></li>
        </ul>
      </section>

      <section class="panel">
        <h2>🤝 A quién seguir</h2>

        <?php
        $miId = (int)($_SESSION['id_usuario'] ?? 0);

        // Consulta: 3 usuarios aleatorios que NO soy yo y que NO sigo actualmente
        $sqlSugerencias = "
            SELECT id_usuario, usuario, foto_perfil 
            FROM usuario 
            WHERE id_usuario != ? 
            AND id_usuario NOT IN (SELECT id_usuario FROM seguidores WHERE id_seguidor = ?)
            ORDER BY RAND() 
            LIMIT 3
        ";

        // Asumimos que $mysqli viene de db.php
        if (isset($mysqli)) {
          $stmtSug = $mysqli->prepare($sqlSugerencias);
          if ($stmtSug) {
            $stmtSug->bind_param('ii', $miId, $miId);
            $stmtSug->execute();
            $resSug = $stmtSug->get_result();

            if ($resSug->num_rows > 0) {
              while ($sug = $resSug->fetch_assoc()) {
                $sugId = (int)$sug['id_usuario'];
                $sugUser = htmlspecialchars($sug['usuario']);
                $sugFoto = $sug['foto_perfil'] ? '../multimedia/' . rawurlencode($sug['foto_perfil']) : '';
                $inicial = strtoupper(substr($sugUser, 0, 1));
        ?>
                <div class="follow-row">
                  <a href="#" class="user-link" data-user="<?php echo $sugUser; ?>" style="display:flex; align-items:center; text-decoration:none; color:inherit; flex:1;">
                    <?php if ($sugFoto): ?>
                      <img src="<?php echo $sugFoto; ?>" alt="Avatar" class="mini-avatar" style="object-fit:cover;">
                    <?php else: ?>
                      <div class="mini-avatar"><?php echo $inicial; ?></div>
                    <?php endif; ?>

                    <div class="follow-txt">
                      <strong>@<?php echo $sugUser; ?></strong>
                      <small>Sugerencia</small>
                    </div>
                  </a>

                  <button class="btn-mini btn-accion-seguir"
                    type="button"
                    data-id="<?php echo $sugId; ?>"
                    data-sigo="0">
                    Seguir
                  </button>
                </div>
        <?php
              }
            } else {
              echo '<p style="padding:10px; color:#aaa; font-size:0.9rem;">¡Estás al día! No hay nuevas sugerencias.</p>';
            }
            $stmtSug->close();
          }
        }
        ?>
      </section>

      
    </aside>
  </div>

  <?php include __DIR__ . '/modal_publicar.php'; ?>
  <?php include __DIR__ . '/modal_cerrar_sesion.php'; ?>
  <?php include __DIR__ . '/modal_EditarPerfil.php'; ?>
  <?php include __DIR__ . '/modal_lista_usuarios.php'; ?>

  <script>
    window.__MY_ID__ = <?php echo (int)($_SESSION['id_usuario'] ?? 0); ?>;
    
    const cssMap = {
      explorar: '../css/explorar.css',
      chat: '../css/chat.css',
    };

    // --- 2. CONFIGURACIÓN INICIAL ---
    document.addEventListener('DOMContentLoaded', () => {
        
        // A. Inicializar botones de Modals (Publicar)
        const mainBtn = document.getElementById('abrirModal');
        const q1 = document.getElementById('abrirModalQuick');
        const q2 = document.getElementById('abrirModalQuick2');
        [q1, q2].forEach(el => el && mainBtn && el.addEventListener('click', () => mainBtn.click()));

        // B. Inicializar Buscador
        const inputBusqueda = document.getElementById('inputBusquedaGlobal');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const termino = this.value.trim();
                    if (termino.length > 0) {
                        realizarBusqueda(termino);
                        this.blur();
                    }
                }
            });
        }
    });

    // --- 3. DELEGACIÓN DE EVENTOS GLOBAL (EL CEREBRO DE LA PÁGINA) ---
    document.body.addEventListener('click', (e) => {
        
        // A) CLICK EN LIKE (Corazón)
        const btnLike = e.target.closest('.btn-like-inline');
        if (btnLike) {
            e.preventDefault();
            e.stopPropagation(); 
            handleLike(btnLike);
            return;
        }

        // B) CLICK EN COMENTAR (Icono globo)
        const btnComment = e.target.closest('.btn-comment-inline');
        if (btnComment) {
            e.preventDefault();
            e.stopPropagation();
            const id = btnComment.dataset.id;
            const box = document.getElementById(`comment-box-${id}`);
            if(box) {
                box.style.display = box.style.display === 'none' ? 'block' : 'none';
                const input = box.querySelector('input');
                if(input && box.style.display === 'block') input.focus();
            }
            return;
        }

        // C) CLIC EN POST PARA ABRIRLO (Feed y Perfil)
        // Detectamos posts normales y previews del perfil
        const postCard = e.target.closest('article.post, article.tweet-style, article.publicaciones, .post-preview-click');
        
        // Verificamos que NO sea un clic en elementos interactivos dentro del post
        if (postCard && !e.target.closest('.stop-prop') && !e.target.closest('a') && !e.target.closest('button') && !e.target.closest('input')) {
            const id = postCard.dataset.id;
            cargarVistaPublicacion(id);
            return;
        }

        // D) BOTÓN "SEGUIR"
        const btnSeguir = e.target.closest('.btn-accion-seguir, #btnSeguir');
        if (btnSeguir) {
            e.preventDefault();
            e.stopPropagation();
            handleFollow(btnSeguir);
            return;
        }

        // E) BOTÓN "MENSAJE/CHAT"
        const btnChat = e.target.closest('#btnChat');
        if (btnChat) {
            e.preventDefault();
            const user = btnChat.dataset.user;
            if (!user) return;
            sessionStorage.setItem('chatUser', user);
            history.pushState({ type: 'chatUser', u: user }, '', `?chatUser=${encodeURIComponent(user)}`);
            loadPage('chat');
            return;
        }

        // F) CLIC EN USUARIO (Enlace perfil)
        const userLink = e.target.closest('a.user-link');
        if (userLink) {
            e.preventDefault();
            e.stopPropagation();
            const u = userLink.dataset.user;
            if(u) loadUserProfile(u);
            return;
        }

        // G) MENU DE NAVEGACIÓN (ESTO ES LO QUE TE FALLABA)
        const menuItem = e.target.closest('.menu-item[data-page]');
        if (menuItem) {
            e.preventDefault();
            const page = menuItem.dataset.page;
            
            // Si es inicio, recargamos para limpiar
            if(page === 'index') {
                window.location.href = 'index.php';
            } else {
                loadPage(page);
            }
            
            // Actualizar clase activo visualmente
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('activo'));
            menuItem.classList.add('activo');
            return;
        }
    });

    // --- 4. LISTENER PARA FORMULARIOS ---
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('form-inline-comment')) {
            e.preventDefault();
            enviarComentarioInline(e.target);
            return;
        }
        if (e.target.id === 'formFotoPerfil') {
            e.preventDefault();
        }
    });

    // Subida de Foto de Perfil AJAX
    document.addEventListener('change', async function(e) {
      if (!e.target || e.target.id !== 'inputFotoPerfil') return;
      const input = e.target;
      const form = input.closest('form');
      if (!form || !input.files || input.files.length === 0) return;

      try {
        const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
        // Recargar perfil actual
        const u = new URLSearchParams(window.location.search).get('u');
        if(u) loadUserProfile(u);
        else loadPage('perfil');
      } catch (err) { console.error(err); } 
      finally { input.value = ''; }
    });

    // --- 5. FUNCIONES DE CARGA Y NAVEGACIÓN ---

    function loadPageCSS(page) {
      const existingLink = document.querySelector('link[data-page-css]');
      if (existingLink) existingLink.remove();

      const cssHref = cssMap[page];
      if (cssHref) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = cssHref;
        link.setAttribute('data-page-css', page);
        document.head.appendChild(link);
      }
    }

    function replaceMainFromHtml(html) {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newMain = doc.querySelector('.contenido-principal');
      if (!newMain) return false;
      
      const currentMain = document.querySelector('.contenido-principal');
      currentMain.innerHTML = newMain.innerHTML;
      
      const title = doc.querySelector('title');
      if (title) document.title = title.textContent;
      
      // Re-ejecutar scripts si hay
      currentMain.querySelectorAll('script').forEach(s => eval(s.textContent));
      
      return true;
    }

    // Cargar Páginas del Menú
    function loadPage(page) {
        loadPageCSS(page); 
        // Asumimos que los archivos están en la misma carpeta php/
        fetch(`${page}.php`)
            .then(r => {
                 if (!r.ok) throw new Error('Página no encontrada');
                 return r.text();
            })
            .then(html => {
                replaceMainFromHtml(html);
                if (page === 'chat') {
                    setTimeout(() => {
                        if (typeof window.__chatInit === 'function') window.__chatInit();
                    }, 50);
                }
            })
            .catch(error => console.error('Error cargando página:', error));
    }

    // Cargar Vista Detallada del Post
    function cargarVistaPublicacion(id) {
        history.pushState({view: 'post', id: id}, '', '?post=' + id);
        window.scrollTo(0,0);
        const main = document.querySelector('.contenido-principal');
        main.innerHTML = '<div style="padding:40px; text-align:center; color:#888;">Cargando publicación...</div>';

        fetch(`ver_publicacion.php?id=${id}`)
            .then(r => r.text())
            .then(html => {
                main.innerHTML = html;
                main.querySelectorAll('script').forEach(s => eval(s.textContent));
            });
    }

    // Cargar Perfil
    function loadUserProfile(username) {
        history.pushState({type: 'user', u: username}, '', `?u=${encodeURIComponent(username)}`);
        
        // Ajustar menú activo
        document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('activo'));
        const perfilBtn = document.querySelector(`[data-page="perfil"]`);
        // Solo marcar activo si es mi propio perfil
        // (Opcional, pero visualmente correcto)

        fetch(`perfil_usuario.php?u=${encodeURIComponent(username)}`)
            .then(r => r.text())
            .then(html => replaceMainFromHtml(html));
    }

    // Realizar Búsqueda
    function realizarBusqueda(termino) {
        history.pushState({ type: 'search', q: termino }, '', `?q=${encodeURIComponent(termino)}`);
        document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('activo'));

        fetch(`resultados_busqueda.php?q=${encodeURIComponent(termino)}`)
            .then(r => r.text())
            .then(html => replaceMainFromHtml(html));
    }

    // --- 6. ACCIONES (Like, Seguir, Comentar) ---

    async function handleFollow(btn) {
        const id = btn.dataset.id;
        const sigo = btn.dataset.sigo === '1'; 
        const url = sigo ? 'dejar_seguir_usuario.php' : 'seguir_usuario.php';
        
        const txtOriginal = btn.textContent;
        btn.textContent = '...';
        btn.disabled = true;

        try {
            const params = new URLSearchParams();
            params.append('id_usuario', id);
            const res = await fetch(url, { method: 'POST', body: params });
            const txt = (await res.text()).trim();

            if (txt.startsWith('ok')) {
                const nuevoEstado = !sigo;
                btn.dataset.sigo = nuevoEstado ? '1' : '0';
                
                if (btn.classList.contains('btn-mini')) {
                    btn.textContent = nuevoEstado ? 'Siguiendo' : 'Seguir';
                    btn.style.background = nuevoEstado ? 'transparent' : '#fff';
                    btn.style.color = nuevoEstado ? '#fff' : '#000';
                    btn.style.border = nuevoEstado ? '1px solid #555' : 'none';
                } else {
                    btn.textContent = nuevoEstado ? 'Dejar de seguir' : 'Seguir';
                }
                // Actualizar contador
                const contador = document.getElementById('nSeguidores');
                if (contador && !btn.classList.contains('btn-mini')) {
                    let n = parseInt(contador.textContent, 10) || 0;
                    contador.textContent = nuevoEstado ? n + 1 : Math.max(0, n - 1);
                }
            } else { btn.textContent = txtOriginal; }
        } catch (err) { btn.textContent = txtOriginal; } 
        finally { btn.disabled = false; }
    }

    async function handleLike(btn) {
        const id = btn.dataset.id;
        const isLiked = btn.dataset.liked === '1';
        const icon = btn.querySelector('i');
        const span = btn.querySelector('.count-like') || btn.querySelector('span');
        
        const newState = !isLiked;
        btn.dataset.liked = newState ? '1' : '0';
        
        if(newState) {
            icon.classList.replace('far', 'fas');
            btn.style.color = '#e0245e';
            if(span) span.textContent = (parseInt(span.textContent||0) + 1) || 1;
        } else {
            icon.classList.replace('fas', 'far');
            btn.style.color = '#71767b';
            let n = (parseInt(span.textContent||0) - 1);
            if(span) span.textContent = n > 0 ? n : '';
        }

        try {
            const fd = new URLSearchParams();
            fd.append('id_publicacion', id);
            await fetch('toggle_like.php', { method: 'POST', body: fd });
        } catch(e) { console.error("Error like", e); }
    }

    async function enviarComentarioInline(form) {
        const idPub = form.dataset.id;
        const input = form.querySelector('input[name="texto"]');
        const texto = input.value.trim();
        const btn = form.querySelector('button');

        if (!texto) return;
        btn.disabled = true;
        btn.textContent = '...';

        try {
            const fd = new URLSearchParams();
            fd.append('id_publicacion', idPub);
            fd.append('texto', texto);

            const res = await fetch('comentar.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.ok) {
                input.value = '';
                const box = document.getElementById(`comment-box-${idPub}`);
                if(box) box.style.display = 'none';

                document.querySelectorAll(`.btn-comment-inline[data-id="${idPub}"] .count-comment`).forEach(el => {
                    el.textContent = (parseInt(el.textContent||0) + 1);
                });

                if(typeof loadCommentsForView === 'function') loadCommentsForView(idPub);
                const bigCounter = document.getElementById('sp-coments');
                if(bigCounter) bigCounter.textContent = parseInt(bigCounter.textContent||0)+1;
            } 
        } catch (err) { console.error(err); } 
        finally {
            btn.disabled = false;
            btn.textContent = 'Responder';
        }
    }

    // --- 7. HISTORIAL NAVEGADOR ---
    window.addEventListener('popstate', (e) => {
        const s = e.state;
        if (!s) {
            const params = new URLSearchParams(window.location.search);
            if (params.has('u')) loadUserProfile(params.get('u'));
            else if (params.has('q')) realizarBusqueda(params.get('q'));
            else if (params.has('post')) cargarVistaPublicacion(params.get('post'));
            else window.location.href = 'index.php';
        } else if (s.view === 'post') {
            cargarVistaPublicacion(s.id);
        } else if (s.type === 'user') {
            loadUserProfile(s.u);
        } else if (s.type === 'search') {
            realizarBusqueda(s.q);
        } else if (s.type === 'chatUser') {
            loadPage('chat');
        }
    });

    // Helper Comentarios Vista Detallada
    window.loadCommentsForView = function(id) {
        const cont = document.getElementById('contenedor-comentarios');
        if(!cont) return;
        cont.innerHTML = '<p style="text-align:center; padding:20px; color:#555">Cargando...</p>';
        fetch(`get_comentarios.php?id_publicacion=${id}`)
            .then(r => r.json())
            .then(data => {
                if(data.ok && data.items.length > 0) {
                    let h = '';
                    data.items.forEach(c => {
                        h += `
                        <div style="padding:15px; border-bottom:1px solid #333; display:flex; gap:10px;">
                            <img src="${c.foto_perfil ? '../multimedia/'+c.foto_perfil : '../multimedia/file.svg'}" style="width:35px; height:35px; border-radius:50%; background:#333;">
                            <div>
                                <div style="margin-bottom:2px;">
                                    <span style="font-weight:bold; color:#fff;">@${c.usuario}</span>
                                    <small style="color:#71767b; margin-left:5px;">${new Date(c.creado_en).toLocaleDateString()}</small>
                                </div>
                                <div style="color:#eee;">${c.texto}</div>
                            </div>
                        </div>`;
                    });
                    cont.innerHTML = h;
                } else {
                    cont.innerHTML = '<p style="text-align:center; padding:20px; color:#555">Sé el primero en responder.</p>';
                }
            });
    };
  </script>
  
  <script src="../js/chat.js"></script>
  <script src="../js/notificaciones.js"></script>
</body>
</html>

  <script src="../js/chat.js"></script>
  <script src="../js/notificaciones.js"></script>
</body>

</html>