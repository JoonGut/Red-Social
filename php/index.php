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
        <a href="index.php" class="menu-item activo" data-page="index">Inicio</a>
        <a href="#" class="menu-item" data-page="explorar">Explorar</a>
        <a href="#" class="menu-item" data-page="chat">Mensajes</a>
        <a href="#" class="menu-item" data-page="perfil">Perfil</a>

        <?php if (isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 2): ?>
            <a href="#" class="menu-item" data-page="admin" style="color: #ff4757;">
                <i class="fas fa-shield-alt"></i>
                <span>Admin</span>
            </a>
        <?php endif; ?>
        <a href="#" id="btnThemeToggle" class="menu-item">
          <i class="fas fa-moon" id="themeIcon"></i>
          <span>Tema</span>
        </a>
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
            <input type="search" id="inputBusquedaGlobal" placeholder="Buscar..." autocomplete="off" />
          </label>
          <div id="toastContainer" class="toast-container"></div>
        </div>
      </header>

      <section class="crear-publicacion">
        <div class="composer">
          <div class="avatar" style="overflow:hidden; background:var(--card2); display:flex; align-items:center; justify-content:center;">
            <?php
            $miFoto = $_SESSION['foto_perfil'] ?? '';
            if ($miFoto):
            ?>
              <img src="../multimedia/<?php echo rawurlencode($miFoto); ?>" alt="Yo" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <span style="font-size:1.5rem;">😊</span>
            <?php endif; ?>
          </div>

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
                    data-sigo="0"
                    style="background:var(--text); color:var(--bg); border:none; font-weight:bold;">
                    Seguir
                  </button>
                </div>
        <?php
              }
            } else {
              echo '<p style="padding:10px; color:var(--muted); font-size:0.9rem;">¡Estás al día! No hay nuevas sugerencias.</p>';
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
    // Helper para avatar en JS
    window.USER_AVATAR = "<?php echo isset($_SESSION['foto_perfil']) ? '../multimedia/' . rawurlencode($_SESSION['foto_perfil']) : ''; ?>";

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
        inputBusqueda.addEventListener('keypress', function(e) {
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
      // Si pulsamos en el botón de cerrar sesión, NO hacemos preventDefault
      if (e.target.closest('a[href="cerrar_sesion.php"]') || e.target.closest('.boton-cerrar-sesion')) {
        return; // Salimos de la función y dejamos que el navegador siga el enlace
      }
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
        if (box) {
          box.style.display = box.style.display === 'none' ? 'block' : 'none';
          const input = box.querySelector('input');
          if (input && box.style.display === 'block') input.focus();
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
        history.pushState({
          type: 'chatUser',
          u: user
        }, '', `?chatUser=${encodeURIComponent(user)}`);
        loadPage('chat');
        return;
      }

      // F) CLIC EN USUARIO (Enlace perfil)
      const userLink = e.target.closest('a.user-link');
      if (userLink) {
        e.preventDefault();
        e.stopPropagation();
        const u = userLink.dataset.user;
        if (u) loadUserProfile(u);
        return;
      }

      // G) MENU DE NAVEGACIÓN (ESTO ES LO QUE TE FALLABA)
      const menuItem = e.target.closest('.menu-item[data-page]');
      if (menuItem) {
        e.preventDefault();
        const page = menuItem.dataset.page;

        // Si es inicio, recargamos para limpiar
        if (page === 'index') {
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
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form)
        });
        // Recargar perfil actual
        const u = new URLSearchParams(window.location.search).get('u');
        if (u) loadUserProfile(u);
        else loadPage('perfil');
      } catch (err) {
        console.error(err);
      } finally {
        input.value = '';
      }
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

      // 1. Lógica para el MODO CHAT (Estilo Twitter)
      if (page === 'chat') {
        document.body.classList.add('modo-chat');
      } else {
        document.body.classList.remove('modo-chat');
      }

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
      history.pushState({
        view: 'post',
        id: id
      }, '', '?post=' + id);
      window.scrollTo(0, 0);
      const main = document.querySelector('.contenido-principal');
      main.innerHTML = '<div style="padding:40px; text-align:center; color:var(--muted);">Cargando publicación...</div>';

      fetch(`ver_publicacion.php?id=${id}`)
        .then(r => r.text())
        .then(html => {
          main.innerHTML = html;
          main.querySelectorAll('script').forEach(s => eval(s.textContent));
        });
    }

    // Cargar Perfil
    function loadUserProfile(username) {
      history.pushState({
        type: 'user',
        u: username
      }, '', `?u=${encodeURIComponent(username)}`);

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
      history.pushState({
        type: 'search',
        q: termino
      }, '', `?q=${encodeURIComponent(termino)}`);
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
        const res = await fetch(url, {
          method: 'POST',
          body: params
        });
        const txt = (await res.text()).trim();

        if (txt.startsWith('ok')) {
          const nuevoEstado = !sigo;
          btn.dataset.sigo = nuevoEstado ? '1' : '0';

          if (btn.classList.contains('btn-mini')) {
            // CAMBIO: Estilos dinámicos usando Variables CSS
            if (nuevoEstado) {
              // Siguiendo (Transparente + Borde)
              btn.textContent = 'Siguiendo';
              btn.style.background = 'transparent';
              btn.style.color = 'var(--text)';
              btn.style.border = '1px solid var(--border)';
            } else {
              // Seguir (Relleno + Contraste)
              btn.textContent = 'Seguir';
              btn.style.background = 'var(--text)';
              btn.style.color = 'var(--bg)';
              btn.style.border = 'none';
            }
          } else {
            btn.textContent = nuevoEstado ? 'Dejar de seguir' : 'Seguir';
          }
          // Actualizar contador
          const contador = document.getElementById('nSeguidores');
          if (contador && !btn.classList.contains('btn-mini')) {
            let n = parseInt(contador.textContent, 10) || 0;
            contador.textContent = nuevoEstado ? n + 1 : Math.max(0, n - 1);
          }
        } else {
          btn.textContent = txtOriginal;
        }
      } catch (err) {
        btn.textContent = txtOriginal;
      } finally {
        btn.disabled = false;
      }
    }

    async function handleLike(btn) {
      const id = btn.dataset.id;
      const isLiked = btn.dataset.liked === '1';
      const icon = btn.querySelector('i');
      const span = btn.querySelector('.count-like') || btn.querySelector('span');

      const newState = !isLiked;
      btn.dataset.liked = newState ? '1' : '0';

      if (newState) {
        icon.classList.replace('far', 'fas');
        btn.style.color = '#e0245e';
        if (span) span.textContent = (parseInt(span.textContent || 0) + 1) || 1;
      } else {
        icon.classList.replace('fas', 'far');
        btn.style.color = 'var(--muted)'; // CAMBIO: Usar variable gris
        let n = (parseInt(span.textContent || 0) - 1);
        if (span) span.textContent = n > 0 ? n : '';
      }

      try {
        const fd = new URLSearchParams();
        fd.append('id_publicacion', id);
        await fetch('toggle_like.php', {
          method: 'POST',
          body: fd
        });
      } catch (e) {
        console.error("Error like", e);
      }
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

        const res = await fetch('comentar.php', {
          method: 'POST',
          body: fd
        });
        const data = await res.json();

        if (data.ok) {
          input.value = '';
          const box = document.getElementById(`comment-box-${idPub}`);
          if (box) box.style.display = 'none';

          document.querySelectorAll(`.btn-comment-inline[data-id="${idPub}"] .count-comment`).forEach(el => {
            el.textContent = (parseInt(el.textContent || 0) + 1);
          });

          if (typeof loadCommentsForView === 'function') loadCommentsForView(idPub);
          const bigCounter = document.getElementById('sp-coments');
          if (bigCounter) bigCounter.textContent = parseInt(bigCounter.textContent || 0) + 1;
        }
      } catch (err) {
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Responder';
      }
    }
    // --- 6.5 FUNCIONES DEL MODAL DE SEGUIDORES ---

    // Variable para saber de qué usuario estamos viendo la lista
    let currentListUserId = 0;

    // 1. Abrir el modal
    window.abrirModalUsuarios = function(tipo, idUsuario) {
      // Si nos pasan un ID, lo guardamos. Si no, usamos el global si existe.
      if (idUsuario) currentListUserId = idUsuario;

      // Si por alguna razón no hay ID (ej: perfil propio sin pasar ID), usamos el de sesión
      if (!currentListUserId && window.__MY_ID__) currentListUserId = window.__MY_ID__;

      const modal = document.getElementById('modalListaUsuarios');
      if (modal) {
        modal.style.display = 'flex';
        cambiarTab(tipo);
      } else {
        console.error("El modal 'modalListaUsuarios' no existe en el DOM.");
      }
    };

    // 2. Cerrar el modal
    window.cerrarModalUsuarios = function() {
      const modal = document.getElementById('modalListaUsuarios');
      if (modal) modal.style.display = 'none';
    };

    // 3. Cambiar pestaña y Cargar datos (AJAX)
    window.cambiarTab = async function(tipo) {
      // ... (Tu código de gestión de pestañas sigue igual) ...
      const tabSeg = document.getElementById('tabSeguidores');
      const tabSig = document.getElementById('tabSiguiendo');
      if (tabSeg) tabSeg.classList.remove('active');
      if (tabSig) tabSig.classList.remove('active');
      if (tipo === 'seguidores' && tabSeg) tabSeg.classList.add('active');
      if (tipo === 'siguiendo' && tabSig) tabSig.classList.add('active');

      const contenedor = document.getElementById('contenedorLista');
      if (!contenedor) return;
      contenedor.innerHTML = '<p style="text-align:center; padding:20px; color:var(--muted);">Cargando...</p>';

      try {
        // Llamamos a la API
        const url = `api_lista_usuarios.php?tipo=${tipo}&id_usuario=${currentListUserId}`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.length === 0) {
          contenedor.innerHTML = '<p style="text-align:center; padding:20px; color:var(--muted);">La lista está vacía.</p>';
          return;
        }

        let html = '';
        data.forEach(u => {
          // 1. ARREGLO DE FOTOS: Usamos ../multimedia/ porque index.php está en php/
          const foto = u.foto_perfil ? `../multimedia/${u.foto_perfil}` : '../multimedia/file.svg';

          // 2. LOGICA DEL BOTÓN SEGUIR
          let botonHtml = '';
          if (!u.soy_yo) { // No mostrar botón si soy yo mismo
            if (u.lo_sigo === 1) {
              // YA LO SIGUES -> Botón "Siguiendo" (transparente/borde)
              botonHtml = `
                            <button class="btn-mini btn-lista-seguir" 
                                    data-id="${u.id_usuario}" 
                                    data-sigo="1"
                                    onclick="event.stopPropagation(); toggleFollowList(this)"
                                    style="background:transparent; border:1px solid var(--border); color:var(--text); padding:5px 12px; border-radius:20px; cursor:pointer;">
                                Siguiendo
                            </button>`;
            } else {
              // NO LO SIGUES -> Botón "Seguir" (blanco/relleno)
              botonHtml = `
                            <button class="btn-mini btn-lista-seguir" 
                                    data-id="${u.id_usuario}" 
                                    data-sigo="0"
                                    onclick="event.stopPropagation(); toggleFollowList(this)"
                                    style="background:var(--text); border:none; color:var(--bg); padding:5px 12px; border-radius:20px; cursor:pointer; font-weight:bold;">
                                Seguir
                            </button>`;
            }
          }

          html += `
                <div class="user-row" onclick="window.loadUserProfile('${u.usuario}'); cerrarModalUsuarios();" style="cursor:pointer; display:flex; align-items:center; padding:10px; border-bottom:1px solid var(--border);">
                    <div style="width:40px; height:40px; border-radius:50%; overflow:hidden; background:var(--card2); margin-right:10px; flex-shrink:0;">
                        <img src="${foto}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="user-info" style="flex:1;">
                        <h4 style="color:var(--text); margin:0; font-size:0.95rem;">${u.nombre}</h4>
                        <span style="color:var(--muted); font-size:0.85rem;">@${u.usuario}</span>
                    </div>
                    <div>
                        ${botonHtml}
                    </div>
                </div>`;
        });
        contenedor.innerHTML = html;

      } catch (error) {
        console.error(error);
        contenedor.innerHTML = '<p style="text-align:center; color:red; padding:20px;">Error.</p>';
      }
    };

    // 4. NUEVA FUNCIÓN PARA SEGUIR DESDE LA LISTA
    window.toggleFollowList = async function(btn) {
      const id = btn.dataset.id;
      const sigo = btn.dataset.sigo === '1';

      // Efecto visual inmediato
      btn.disabled = true;
      btn.textContent = '...';

      const url = sigo ? 'dejar_seguir_usuario.php' : 'seguir_usuario.php';

      try {
        const params = new URLSearchParams();
        params.append('id_usuario', id);

        const res = await fetch(url, {
          method: 'POST',
          body: params
        });
        const txt = (await res.text()).trim();

        if (txt.startsWith('ok')) {
          const nuevoEstado = !sigo;
          btn.dataset.sigo = nuevoEstado ? '1' : '0';

          // Cambiar estilo del botón dinámicamente
          if (nuevoEstado) {
            btn.textContent = 'Siguiendo';
            btn.style.background = 'transparent';
            btn.style.color = 'var(--text)';
            btn.style.border = '1px solid var(--border)';
            btn.style.fontWeight = 'normal';
          } else {
            btn.textContent = 'Seguir';
            btn.style.background = 'var(--text)';
            btn.style.color = 'var(--bg)';
            btn.style.border = 'none';
            btn.style.fontWeight = 'bold';
          }
        }
      } catch (e) {
        console.error(e);
      } finally {
        btn.disabled = false;
      }
    };

    // Cerrar al hacer clic fuera del modal
    window.addEventListener('click', (e) => {
      const modal = document.getElementById('modalListaUsuarios');
      if (e.target === modal) cerrarModalUsuarios();
    });
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
    // --- GESTIÓN DE COMENTARIOS Y RESPUESTAS ---

    // --- GESTIÓN DE COMENTARIOS Y RESPUESTAS (Rutas Corregidas) ---

    // 1. Cargar y Pintar Comentarios
    // --- GESTIÓN DE COMENTARIOS ESTILO TWITTER/X ---

    // 1. Cargar y Pintar Comentarios
    window.loadCommentsForView = function(id) {
      const cont = document.getElementById('contenedor-comentarios');
      if (!cont) return;

      // Loader simple
      cont.innerHTML = '<div style="padding:20px; text-align:center;"><div class="spinner"></div></div>';

      fetch(`get_comentarios.php?id_publicacion=${id}`)
        .then(r => r.json())
        .then(data => {
          if (data.ok && data.items.length > 0) {
            cont.innerHTML = '';

            // A. Pintar todos
            data.items.forEach(c => {
              const html = renderComentarioHTML(c);
              const tempDiv = document.createElement('div');
              tempDiv.innerHTML = html;
              cont.appendChild(tempDiv.firstElementChild);
            });

            // B. Ordenar (Anidar respuestas visualmente)
            data.items.forEach(c => {
              if (c.id_padre && c.id_padre > 0) {
                const hijo = document.getElementById(`comentario-${c.id_comentario}`);
                const padre = document.getElementById(`respuestas-${c.id_padre}`);
                if (hijo && padre) {
                  // Añadimos una línea vertical visual si quieres, o solo indentación
                  hijo.classList.add('es-respuesta');
                  padre.appendChild(hijo);
                }
              }
            });

          } else {
            cont.innerHTML = '<p style="text-align:center; padding:30px; color:var(--muted);">Sé la primera persona en responder.</p>';
          }
        })
        .catch(err => {
          console.error(err);
          cont.innerHTML = '<p style="color:var(--muted); text-align:center; padding:20px;">No se pudieron cargar los comentarios.</p>';
        });
    };

    // 2. Generador de HTML (ESTILO TWITTER EXACTO)
    function renderComentarioHTML(c) {
      const foto = c.foto_perfil ? `../multimedia/${c.foto_perfil}` : '../multimedia/file.svg';
      // Formato de tiempo estilo Twitter (ej: 4h o fecha corta)
      const fechaObj = new Date(c.creado_en);
      const ahora = new Date();
      const diff = Math.floor((ahora - fechaObj) / 1000); // segundos
      let tiempo = '';

      if (diff < 60) tiempo = diff + 's';
      else if (diff < 3600) tiempo = Math.floor(diff / 60) + 'm';
      else if (diff < 86400) tiempo = Math.floor(diff / 3600) + 'h';
      else tiempo = fechaObj.toLocaleDateString();

      return `
        <div id="comentario-${c.id_comentario}" class="comentario-wrap" style="position:relative; transition:background 0.2s;">
            <div class="comentario-body" style="display:flex; padding:12px 16px; border-bottom:1px solid var(--border);">
                
                <div style="flex-shrink:0; margin-right:12px; display:flex; flex-direction:column; align-items:center;">
                    <img src="${foto}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; background:var(--card2);">
                    </div>
                
                <div style="flex:1; min-width:0;">
                    
                    <div style="font-size:15px; line-height:20px; display:flex; align-items:baseline; gap:5px;">
                        <span style="font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            ${c.nombre || c.usuario}
                        </span>
                        <span style="color:var(--muted); font-weight:400; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            @${c.usuario}
                        </span>
                        <span style="color:var(--muted); font-size:14px;">· ${tiempo}</span>
                    </div>

                    <div style="color:var(--text); font-size:15px; line-height:20px; margin-top:2px; white-space:pre-wrap; word-break:break-word;">${c.texto}</div>
                    
                    <div style="margin-top:12px; display:flex; gap:20px;">
                        <button onclick="mostrarFormResponder(${c.id_comentario})" 
                                class="btn-reply-action"
                                style="background:none; border:none; color:var(--muted); cursor:pointer; display:flex; align-items:center; gap:5px; padding:0; font-size:13px; transition:color 0.2s;">
                            <svg viewBox="0 0 24 24" aria-hidden="true" style="width:18px; height:18px; fill:currentColor;"><g><path d="M1.751 10c0-4.42 3.584-8 8.005-8h4.366c4.49 0 8.129 3.64 8.129 8.13 0 2.96-1.607 5.68-4.196 7.11l-8.054 4.46v-3.69h-.067c-4.49.1-8.183-3.51-8.183-8.01zm8.005-6c-3.317 0-6.005 2.69-6.005 6 0 3.37 2.77 6.08 6.138 6.01l.351-.01h1.761v2.3l5.087-2.81c1.951-1.08 3.163-3.13 3.163-5.36 0-3.39-2.744-6.13-6.129-6.13H9.756z"></path></g></svg>
                            <span>Responder</span>
                        </button>
                    </div>

                    <div id="form-reply-${c.id_comentario}" style="display:none; margin-top:15px; padding-top:10px;">
                        <form onsubmit="enviarRespuesta(event, ${c.id_comentario})" data-id-pub="${c.id_publicacion || obtenerIdPubActual()}" style="display:flex; gap:10px; align-items:flex-start;">
                            <img src="${window.USER_AVATAR || '../multimedia/file.svg'}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                            <div style="flex:1;">
                                <input type="text" name="texto" placeholder="Postea tu respuesta" required
                                       style="width:100%; background:transparent; border:none; border-bottom:1px solid var(--border); color:var(--text); padding:10px 0; outline:none; font-size:16px;">
                                <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                                    <button type="submit" style="background:var(--accent); color:white; border:none; padding:8px 16px; border-radius:20px; font-weight:bold; cursor:pointer;">
                                        Responder
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="respuestas-${c.id_comentario}" style="padding-left:0;"></div>
        </div>`;
    }

    // Nota: El CSS global necesita esto para que las respuestas anidadas se vean bien
    // Puedes añadir esto al final de tu función o en tu archivo CSS
    const styleHilo = document.createElement('style');
    styleHilo.innerHTML = `
        .btn-reply-action:hover { color: var(--accent) !important; }
        /* Las respuestas se indentan un poco y se conectan */
        .comentario-wrap .comentario-wrap { margin-left: 20px; border-left: 2px solid var(--border); border-bottom: none; }
        .comentario-wrap .comentario-wrap .comentario-body { border-bottom: none; padding-bottom: 8px; }
    `;
    document.head.appendChild(styleHilo);

    // 3. Mostrar Formulario (Igual)
    window.mostrarFormResponder = function(idComentario) {
      const form = document.getElementById(`form-reply-${idComentario}`);
      if (form) {
        form.style.display = (form.style.display === 'none') ? 'block' : 'none';
        if (form.style.display === 'block') form.querySelector('input')?.focus();
      }
    };

    // 4. Enviar Respuesta
    window.enviarRespuesta = async function(e, idPadre) {
      e.preventDefault();
      const form = e.target;
      const input = form.querySelector('input[name="texto"]');
      const texto = input.value.trim();
      const idPublicacion = new URLSearchParams(window.location.search).get('post') || form.dataset.idPub;

      if (!texto) return;

      const btn = form.querySelector('button');
      btn.disabled = true;

      const fd = new URLSearchParams();
      fd.append('id_publicacion', idPublicacion);
      fd.append('id_padre', idPadre);
      fd.append('texto', texto);

      try {
        // CORRECCIÓN AQUÍ: Quitamos 'php/'
        const res = await fetch('comentar.php', {
          method: 'POST',
          body: fd
        });
        const data = await res.json(); // Si aquí falla, es que comentar.php tiene error PHP

        if (data.ok) {
          input.value = '';
          form.style.display = 'none';
          loadCommentsForView(idPublicacion);
        } else {
          alert('Error: ' + (data.error || 'No se pudo enviar'));
        }
      } catch (err) {
        console.error("Error al enviar respuesta:", err);
        // Si ves este alert, mira la pestaña Network en F12 para ver la respuesta roja
        alert("Error de conexión con comentar.php");
      } finally {
        btn.disabled = false;
      }
    };
    //5 Función para eliminar publicación
    window.eliminarPublicacion = async function(idPublicacion, btn) {
      // 1. Confirmación de seguridad
      if (!confirm("¿Estás seguro de que quieres eliminar esta publicación? No se puede deshacer.")) {
        return;
      }

      // 2. Desactivar botón para evitar doble click
      if (btn) {
        btn.disabled = true;
        btn.innerText = "...";
      }

      try {
        // 3. Preparar los datos formato formulario (NO JSON)
        const datos = new URLSearchParams();
        datos.append('id', idPublicacion); // La clave 'id' debe coincidir con $_POST['id']

        // 4. Enviar petición (Ajusta la ruta si es necesario)
        // Si index.php está en php/, la ruta es 'eliminar_publicacion.php'
        const respuesta = await fetch('eliminar_publicacion.php', {
          method: 'POST',
          body: datos
        });

        const resultado = await respuesta.text();

        // 5. Manejar respuesta
        if (resultado.trim() === 'ok') {
          alert("Publicación eliminada.");

          // Eliminar visualmente del HTML sin recargar
          // Buscamos el elemento padre (post) y lo quitamos
          const postElement = document.querySelector(`article[data-id="${idPublicacion}"], .post-preview-click[data-id="${idPublicacion}"]`);
          if (postElement) {
            postElement.remove();
          } else {
            // Si no lo encuentra (estás en vista detalle), vuelve al inicio
            window.location.href = 'index.php';
          }
        } else {
          alert("Error al eliminar: " + resultado);
          if (btn) {
            btn.disabled = false;
            btn.innerText = "Eliminar";
          }
        }

      } catch (error) {
        console.error(error);
        alert("Error de conexión");
        if (btn) {
          btn.disabled = false;
          btn.innerText = "Eliminar";
        }
      }
    };

    function obtenerIdPubActual() {
      return new URLSearchParams(window.location.search).get('post') || 0;
    }
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Elementos
      const btnTheme = document.getElementById('btnThemeToggle');
      const themeIcon = document.getElementById('themeIcon');
      const body = document.body;

      // 2. Verificar preferencia guardada al cargar
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'light') {
        body.classList.add('light-mode');
        updateIcon(true);
      }

      // 3. Evento Click (Solo si el botón existe)
      if (btnTheme) {
        btnTheme.addEventListener('click', (e) => {
          e.preventDefault(); // Evita que la página salte al inicio

          // Alternar clase
          body.classList.toggle('light-mode');

          // Guardar estado
          const isLight = body.classList.contains('light-mode');
          localStorage.setItem('theme', isLight ? 'light' : 'dark');

          // Cambiar icono
          updateIcon(isLight);
        });
      }

      // Función auxiliar para el icono
      function updateIcon(isLight) {
        if (!themeIcon) return;
        if (isLight) {
          themeIcon.classList.remove('fa-moon');
          themeIcon.classList.add('fa-sun');
        } else {
          themeIcon.classList.remove('fa-sun');
          themeIcon.classList.add('fa-moon');
        }
      }
    });
  </script>
  <script src="../js/chat.js"></script>
  <script src="../js/notificaciones.js"></script>
</body>

</html>