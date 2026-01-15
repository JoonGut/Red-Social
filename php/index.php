<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Inicio · Cloudia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../css/modal.css">
  <link rel="icon" href="favicon.ico">
</head>

<body>
  <div class="contenedor-inicio">
    <aside class="barra-lateral">
      <div class="logo-red">
        <span>🐦</span>
      </div>
      <nav class="menu">
        <a href="index.php" class="menu-item activo">Inicio</a>
        <a href="#" class="menu-item" data-page="explorar">Explorar</a>
        <a href="#" class="menu-item" data-page="chat">Mensajes</a>
        <a href="#" class="menu-item" data-page="perfil">Perfil</a>
      </nav>
    </aside>

    <main class="contenido-principal">
      <header class="cabecera">
        <h1>Inicio</h1>
      </header>

      <section class="crear-publicacion">
        <button id="abrirModal" class="boton-registrarse" type="button">Publicar</button>
      </section>

      <section class="feed">
        <article class="publicacion">
          <section class="feed">
            <?php include __DIR__ . '/feedPublicaciones.php'; ?>
          </section>
        </article>

        
      </section>
    </main>

    <aside class="barra-derecha">
      <section class="panel">
        <h2>Tendencias</h2>
        <ul>
          <li>#Tecnología</li>
          <li>#Ciencia</li>
          <li>#DiseñoWeb</li>
          <li>#Programación</li>
        </ul>
      </section>

      <section class="panel">
        <h2>A quién seguir</h2>
        <p>@usuario3</p>
        <p>@usuario4</p>
        <p>@usuario5</p>
      </section>
    </aside>
  </div>

  <?php include __DIR__ . '/modal_publicar.php'; ?>
  <?php include __DIR__ . '/modal_cerrar_sesion.php'; ?>

  <script>
    const cssMap = {
      explorar: '../css/explorar.css',
      chat: '../css/chat.css',
      perfil: '../css/perfil.css'
    };

    let currentPage = null;

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
      fetch(`../html/${page}.html`)
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newMain = doc.querySelector('.contenido-principal');
          
          if (newMain) {
            const currentMain = document.querySelector('.contenido-principal');
            currentMain.innerHTML = newMain.innerHTML;
            
            const title = doc.querySelector('title');
            if (title) {
              document.title = title.textContent;
            }
            
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('activo'));
            document.querySelector(`[data-page="${page}"]`).classList.add('activo');
            
            loadPageCSS(page);
          }
        })
        .catch(error => console.error('Error loading page:', error));
    }

    function loadPageCSS(page) {

      const existingLink = document.querySelector('link[data-page-css]');
      if (existingLink) {
        existingLink.remove();
      }
    
      const cssHref = cssMap[page];
      if (cssHref) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = cssHref;
        link.setAttribute('data-page-css', page);
        document.head.appendChild(link);
      }
    }

    setInterval(() => {
      if (currentPage) {
        fetch(`../html/${currentPage}.html`)
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
          .catch(error => console.error('Error checking for updates:', error));
      }
    }, 30000);
  </script>
</body>
</html>
