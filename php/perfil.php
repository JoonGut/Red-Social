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
  <link rel="stylesheet" href="../css/index.css" />
  <link rel="stylesheet" href="../css/modal.css" />
  <link rel="icon" href="favicon.ico">

</head>

<body>
  <div class="contenedor-inicio">

    <main class="contenido-principal">
      <section class="cabecera-perfil">
        <div class="banner">
          <a href="index.php" class="volver">← Volver</a>
        </div>
        <?php
        // foto por defecto si no hay
        $foto = $_SESSION['foto_perfil'] ?? '';
        $fotoUrl = ($foto !== '') ? '../multimedia/' . $foto : '';
        $bioActual = $_SESSION['biografia'] ?? '';
        ?>

        <div class="info-perfil">
          <!-- Avatar clicable -->
          <form id="formFotoPerfil" class="avatar-form" method="POST" action="subirFotoPerfil.php" enctype="multipart/form-data">
            <label class="avatar avatar-click" for="inputFotoPerfil" title="Cambiar foto">
              <?php if ($fotoUrl): ?>
                <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto de perfil">
              <?php else: ?>
                <span>👤</span>
              <?php endif; ?>
            </label>

            <input
              type="file"
              id="inputFotoPerfil"
              name="foto_perfil"
              accept="image/*"
              class="input-file-oculto">
          </form>

          <div class="perfil-mini">
            <p class="bio-perfil" id="perfilBio"><?php echo htmlspecialchars($bioActual); ?></p>
          </div>

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
                    echo $stmt->get_result()->fetch_assoc()['total']; ?></strong>
          </span>


          <span>Seguiendo
            <strong><?php
                    $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_seguidor = ?');
                    $stmt->bind_param('i', $_SESSION['id_usuario']);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_assoc()['total']; ?></strong></span>

          <span>
            Publicaciones
            <strong><?php
                    $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM publicacion WHERE id_usuario = ?');
                    $stmt->bind_param('i', $_SESSION['id_usuario']);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_assoc()['total']; ?>
            </strong>

          </span>
        </div>
        <?php
        // Publicaciones del usuario (ajusta nombres de columnas a tu BD)
        $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);

        $stmt = $mysqli->prepare("
  SELECT id_publicacion, imagen, texto, pie_foto, fecha_publicacion
  FROM publicacion
  WHERE id_usuario = ?
  ORDER BY fecha_publicacion DESC, id_publicacion DESC
");
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $pubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        ?>

        <section class="mis-publicaciones">
          <h3 class="titulo-seccion">Publicaciones</h3>

          <div class="grid-publicaciones">
            <?php foreach ($pubs as $p):
              $idp = (int)$p['id_publicacion'];
              $img = trim((string)($p['imagen'] ?? ''));
              $txt = (string)($p['texto'] ?? '');
              $pie = (string)($p['pie_foto'] ?? '');

              $imgUrl = $img !== '' ? '../multimedia/' . $img : '';
              $textoModal = trim($pie . "\n" . $txt);
            ?>
              <button
                type="button"
                class="grid-item"
                data-id="<?php echo $idp; ?>"
                data-img="<?php echo htmlspecialchars($imgUrl); ?>"
                data-pie="<?php echo htmlspecialchars($pie); ?>"
                data-desc="<?php echo htmlspecialchars($txt); ?>"
                data-fecha="<?php echo htmlspecialchars($p['fecha_publicacion'] ?? ''); ?>">

                <?php if ($imgUrl): ?>
                  <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Publicación <?php echo $idp; ?>">
                <?php else: ?>
                  <div class="grid-item-texto">
                    <?php if (trim($pie) !== ''): ?>
                      <div class="grid-txt-pie"><?php echo htmlspecialchars($pie); ?></div>
                      <div class="grid-txt-desc"><?php echo htmlspecialchars($txt); ?></div>
                    <?php else: ?>
                      <div class="grid-txt-desc"><?php echo htmlspecialchars($txt); ?></div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>
        </section>

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
  <?php include __DIR__ . '/modal_publicaciones_perfil.php'; ?>

  <script>
    document.addEventListener('change', async (e) => {
      if (e.target && e.target.id === 'inputFotoPerfil') {
        const input = e.target;
        const form = input.closest('form');
        if (!form || !input.files || input.files.length === 0) return;

        try {
          const res = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
          });
          const data = await res.json();

          if (!data.ok) {
            alert(data.error || 'Error al subir foto');
            return;
          }

          // ✅ actualizar imagen en pantalla sin recargar
          const img = form.querySelector('label.avatar img');
          if (img) {
            // cache-bust para que el navegador no muestre la antigua
            img.src = data.foto_url + '?t=' + Date.now();
          } else {
            // si antes no había img, lo creamos
            const label = form.querySelector('label.avatar');
            if (label) {
              label.innerHTML = `<img src="${data.foto_url}?t=${Date.now()}" alt="Foto de perfil">`;
            }
          }

        } catch (err) {
          console.error(err);
          alert('Error de red');
        }
      }
    });
  </script>



</body>

</html>