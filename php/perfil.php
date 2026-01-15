<?php

declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Perfil · Cloudia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/perfil.css" />
  <link rel="stylesheet" href="../css/modal.css" />
  <link rel="icon" href="favicon.ico">

</head>

<body>
  <div class="contenedor-inicio">

    <main class="contenido-principal">
      <section class="cabecera-perfil">
        <div class="banner">
          <a href="../php/index.php" class="volver">← Volver</a>
        </div>
        <div class="info-perfil">
          <div class="avatar">👤</div>
          <button id="botonEditarPerfil" class="boton-registrarse boton-editar">Editar perfil</button>
        </div>

        <div>
          <a href="javascript:void(0)" class="boton-cerrar-sesion" onclick="mostrarModal()">
            Cerrar sesión
          </a>
        </div>
      </section>

      <section class="datos-perfil">
        <h2>@<?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?></h2>
        <p class="nombre-real"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></p>

        <div class="estadisticas">
          <span></strong> Siguidores

          <strong><?php
                        $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_usuario = ?');
                        $stmt->bind_param('i', $_SESSION['id_usuario']);
                        $stmt->execute();
                        echo $stmt->get_result()->fetch_assoc()['total'];?></strong>
              </span>
          

          <span>Seguiendo
          <strong><?php
                        $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_seguidor = ?');
                        $stmt->bind_param('i', $_SESSION['id_usuario']);
                        $stmt->execute();
                        echo $stmt->get_result()->fetch_assoc()['total'];?></strong></span>

          <span>
            Publicaciones
            <strong><?php
                    $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM publicacion WHERE id_usuario = ?');
                    $stmt->bind_param('i', $_SESSION['id_usuario']);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_assoc()['total'];?>
            </strong> 
            
          </span>
        </div>
      </section>


    </main>

    <aside class="barra-derecha">
      <section class="panel">
        <h2>Sugerencias</h2>
        <p>@persona1</p>
        <p>@persona2</p>
      </section>
    </aside>

  </div>

  <?php include __DIR__ . '/modal_EditarPerfil.php'; ?>
</body>

</html>