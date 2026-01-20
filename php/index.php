<?php
declare(strict_types=1);
session_start();
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
            <input type="search" placeholder="Buscar..." />
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

        <div class="follow-row">
          <div class="mini-avatar">A</div>
          <div class="follow-txt">
            <strong>@usuario3</strong>
            <small>Diseño</small>
          </div>
          <button class="btn-mini" type="button">Seguir</button>
        </div>

        <div class="follow-row">
          <div class="mini-avatar">B</div>
          <div class="follow-txt">
            <strong>@usuario4</strong>
            <small>Tech</small>
          </div>
          <button class="btn-mini" type="button">Seguir</button>
        </div>

        <div class="follow-row">
          <div class="mini-avatar">C</div>
          <div class="follow-txt">
            <strong>@usuario5</strong>
            <small>Creativo</small>
          </div>
          <button class="btn-mini" type="button">Seguir</button>
        </div>
      </section>

      <section class="panel panel-footer">
        <small>Hecho con ⚡ estilo NeonNest</small>
      </section>
    </aside>
  </div>

  <?php include __DIR__ . '/modal_publicar.php'; ?>
  <?php include __DIR__ . '/modal_cerrar_sesion.php'; ?>
  <?php include __DIR__ . '/modal_publicacion.php'; ?>
  <?php include __DIR__ . '/modal_EditarPerfil.php'; ?>
  <?php include __DIR__ . '/modal_publicaciones_perfil.php'; ?>


  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const mainBtn = document.getElementById('abrirModal');
      const q1 = document.getElementById('abrirModalQuick');
      const q2 = document.getElementById('abrirModalQuick2');
      [q1, q2].forEach(el => el && mainBtn && el.addEventListener('click', () => mainBtn.click()));
    });

    const cssMap = {
      explorar: '../css/explorar.css',
      chat: '../css/chat.css',
    };

    let currentPage = null;
    document.addEventListener('click', (e) => {
      const userLink = e.target.closest('a.user-link');
      if (userLink) {
        e.preventDefault();
        e.stopPropagation();

        const u = userLink.dataset.user || '';
        if (u && typeof window.loadUserProfile === 'function') {
          window.loadUserProfile(u);
        }
        return;
      }
    }, true);

    document.addEventListener('DOMContentLoaded', function() {
      const menuItems = document.querySelectorAll('.menu-item[data-page]');
      menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          const page = this.getAttribute('data-page');
          loadPage(page);
        });
      });
    });

    function loadPage(page) {
      currentPage = page;
      fetch(`../php/${page}.php`)
        .then(response => {
          if (!response.ok) throw new Error('PHP not found');
          return response.text();
        })
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newMain = doc.querySelector('.contenido-principal');

          if (newMain) {
            const currentMain = document.querySelector('.contenido-principal');
            currentMain.innerHTML = newMain.innerHTML;

            if (page === 'chat') {
              setTimeout(() => {
                if (typeof window.__chatInit === 'function') {
                  console.log("Iniciando chat...");
                  window.__chatInit();
                }
              }, 50);
            }

            const title = doc.querySelector('title');
            if (title) document.title = title.textContent;

            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('activo'));
            const activeMenu = document.querySelector(`[data-page="${page}"]`);
            if (activeMenu) activeMenu.classList.add('activo');

            loadPageCSS(page);
          }
        })
        .catch(error => console.error('Error loading page:', error));
    }

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

      return true;
    }
    window.loadUserProfile = function(username) {
      if (!username) return;

      currentPage = null;
      fetch(`../php/perfil_usuario.php?u=${encodeURIComponent(username)}`)
        .then(r => {
          if (!r.ok) throw new Error('Perfil no encontrado');
          return r.text();
        })
        .then(html => {
          const ok = replaceMainFromHtml(html);
          if (!ok) return;

          document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('activo'));
          const perfilBtn = document.querySelector(`[data-page="perfil"]`);
          if (perfilBtn) perfilBtn.classList.add('activo');

          history.pushState({
            type: 'user',
            u: username
          }, '', `?u=${encodeURIComponent(username)}`);
        })
        .catch(err => console.error(err));
    };

    setInterval(() => {
      if (currentPage && currentPage !== 'chat') {
        fetch(`../php/${currentPage}.php`)
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('.contenido-principal');
            const currentMain = document.querySelector('.contenido-principal');
            if (newMain && newMain.innerHTML !== currentMain.innerHTML) {
              loadPage(currentPage);
            }
          })
          .catch(error => console.error('Error de red:', error));
      }
    }, 30000);
  </script>
  <script>
    document.addEventListener('submit', function(e) {
      const form = e.target;
      if (form && form.id === 'formFotoPerfil') {
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);

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



      } catch (err) {
        console.error(err);
      } finally {
        input.value = '';
      }
    });
    window.addEventListener('popstate', (e) => {
      const st = e.state;
      if (st && st.type === 'user' && st.u) {
        window.loadUserProfile(st.u);
        return;
      }


      if (!st) {
        window.location.href = 'index.php';
      }
    });
  </script>

  <script>
    document.addEventListener('click', async (e) => {
      const btnSeguir = e.target.closest('#btnSeguir');
      if (btnSeguir) {
        e.preventDefault();

        const id = btnSeguir.dataset.id;
        const sigo = btnSeguir.dataset.sigo === '1';
        const contador = document.getElementById('nSeguidores');

        const url = sigo ?
          '../php/dejar_seguir_usuario.php' :
          '../php/seguir_usuario.php';

        try {
          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_usuario=${encodeURIComponent(id)}`
          });

          const txt = (await res.text()).trim();

          if (txt === 'no-login') {
            alert('Tienes que iniciar sesión');
            return;
          }
          if (txt !== 'ok') {
            alert('Error al procesar');
            return;
          }

          btnSeguir.dataset.sigo = sigo ? '0' : '1';
          btnSeguir.textContent = sigo ? 'Seguir' : 'Dejar de seguir';


          if (contador) {
            let n = parseInt(contador.textContent, 10) || 0;
            contador.textContent = sigo ? Math.max(0, n - 1) : n + 1;
          }

        } catch (err) {
          console.error(err);
          alert('Error de conexión');
        }
        return;
      }


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
        console.log("[perfil] click Chat -> user =", user);
        console.log("[perfil] guardando sessionStorage chatUser =", user);
        loadPage('chat');
        return;
      }

    }, true);
  </script>
  <script>
    window.__MY_ID__ = <?php echo (int)($_SESSION['id_usuario'] ?? 0); ?>;
  </script>
  <script>
    window.__BASE__ = (function() {
      const p = location.pathname;
      return p.replace(/\/php\/index\.php.*$/, '').replace(/\/$/, '');
    })();
  </script>

  <script src="../js/chat.js"></script>
  <script src="../js/notificaciones.js"></script>
</body>

</html>