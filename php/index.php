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
        <a href="explorar.html" class="menu-item">Explorar</a>
        <a href="Chat.html" class="menu-item">Mensajes</a>
        <a href="perfil.html" class="menu-item">Perfil</a>
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
          <h3>@usuario1</h3>
          <p>Este es un ejemplo de publicación en la página de inicio.</p>
        </article>

        <article class="publicacion">
          <h3>@usuario2</h3>
          <p>Otra publicación. Breve, directa y con opinión fuerte.</p>
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

  <!-- ✅ Modal en otro archivo (pero aparece encima) -->
  <?php include __DIR__ . '/modal_publicar.php'; ?>

</body>
</html>
