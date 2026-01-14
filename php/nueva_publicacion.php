<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: login.html');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Nueva publicación · Cloudia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/index.css">
</head>
<body>

<div class="contenedor-inicio">
  <main class="contenido-principal">
    <header class="cabecera" style="display:flex; justify-content:space-between; align-items:center;">
      <h1>Nueva publicación</h1>
      <a href="index.php" class="menu-item">Cancelar</a>
    </header>

    <section class="crear-publicacion">
      <form action="../php/guardar_publicacion.php" method="POST" enctype="multipart/form-data">

        <textarea name="texto" placeholder="¿Qué está pasando?" required></textarea>

        <input type="text" name="ubicacion" placeholder="Ubicación (opcional)">
        <input type="text" name="pie_foto" placeholder="Pie de foto (opcional)">

        <label style="display:block; margin-top:10px;">
          Imagen (opcional):
          <input type="file" name="imagen" accept="image/*">
        </label>

        <button class="boton-registrarse" type="submit" style="margin-top:10px;">
          Publicar
        </button>
      </form>
    </section>

  </main>
</div>

</body>
</html>
