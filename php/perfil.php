<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';


$miId = (int)($_SESSION['id_usuario'] ?? 0);
if ($miId === 0) { header('Location: index.php'); exit; }



$stmtPerfil = $mysqli->prepare("SELECT foto_perfil, biografia FROM usuario WHERE id_usuario = ?");
$stmtPerfil->bind_param('i', $miId);
$stmtPerfil->execute();
$resPerfil = $stmtPerfil->get_result();
$datosUsuario = $resPerfil->fetch_assoc();


$fotoUrl = '';
if (!empty($datosUsuario['foto_perfil'])) {
    $base64Perfil = base64_encode($datosUsuario['foto_perfil']);
    $fotoUrl = 'data:image/jpeg;base64,' . $base64Perfil;
    
    
    $_SESSION['foto_perfil'] = $base64Perfil; 
}

$bioActual = $datosUsuario['biografia'] ?? '';
$_SESSION['biografia'] = $bioActual; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Perfil · NeonNest</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/index.css" />
  <link rel="stylesheet" href="../css/modal.css" />
  <link rel="stylesheet" href="../css/perfil.css" />
  <link rel="icon" href="../multimedia/file.svg">
</head>

<body>
  <div class="contenedor-inicio layout-solo-main">
    <main class="contenido-principal">
      <section class="cabecera-perfil">

        <div class="banner" style="background-image: url('../multimedia/file.svg'); background-size: cover; background-position: center; height: 150px; position: relative; margin-bottom: -40px;">
          <a href="#" class="volver" onclick="window.location.href='index.php'; return false;" style="position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.5); color: white; padding: 5px 12px; border-radius: 20px; text-decoration: none; font-weight: bold; backdrop-filter: blur(4px);">← Volver</a>
        </div>

        <div class="info-perfil" style="
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            padding: 0 20px 20px; 
            position: relative; 
            z-index: 2;">

          <form id="formFotoPerfil" class="avatar-form" method="POST" action="subirFotoPerfil.php" enctype="multipart/form-data" style="margin-bottom: 0;">
            <label class="avatar avatar-click" for="inputFotoPerfil" title="Cambiar foto" style="
                width: 100px; height: 100px; 
                border-radius: 50%; 
                border: 4px solid var(--bg); 
                overflow: hidden; 
                background: var(--card2); 
                display: flex; align-items: center; justify-content: center; 
                cursor: pointer;
                flex-shrink: 0;">
              
              <?php if ($fotoUrl): ?>
                <img src="<?php echo $fotoUrl; ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <div style="font-size: 2.5rem;">👤</div>
              <?php endif; ?>

            </label>
            <input type="file" id="inputFotoPerfil" name="foto_perfil" accept="image/*" class="input-file-oculto" style="display: none;">
          </form>

          <div style="display: flex; gap: 10px; padding-bottom: 10px;">
            <button id="botonEditarPerfil" class="boton-registrarse" style="
                background: var(--card2); 
                color: var(--text); 
                border: 1px solid var(--border); 
                padding: 8px 20px; 
                border-radius: 20px; 
                font-weight: bold; 
                cursor: pointer;
                font-size: 0.9rem;
                white-space: nowrap;">
                Editar perfil
            </button>
            
            <button
              type="button"
              class="boton-cerrar-sesion"
              onclick="event.stopPropagation(); window.location.href='cerrarSesion.php';"
              style="
                background: var(--card2); 
                color: #ff4757; 
                border: 1px solid var(--border); 
                padding: 8px 20px; 
                border-radius: 20px; 
                font-weight: bold; 
                cursor: pointer; 
                font-size: 0.9rem;
                white-space: nowrap;">
              Cerrar sesión
            </button>
          </div>

        </div>

        <div class="perfil-mini" style="padding: 0 20px;">
           <p class="bio-perfil" id="perfilBio" style="margin-top: 5px;"><?php echo htmlspecialchars($bioActual); ?></p>
        </div>

      </section>
      <section class="datos-perfil">
        <h2>@<?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?></h2>
        <p class="nombre-real"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></p>

        <div class="estadisticas">

          <div onclick="if(typeof abrirModalUsuarios === 'function') abrirModalUsuarios('seguidores')"
            style="cursor: pointer; text-align: center; background: var(--card2); border: 1px solid var(--border); padding: 12px; border-radius: 14px; transition: background 0.2s;">
            <span style="display:block; font-size:0.85rem; color: var(--muted); margin-bottom: 4px;">Seguidores</span>
            <strong style="display:block; font-size: 1.2rem; color: var(--text);">
              <?php
              $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_usuario = ?');
              $stmt->bind_param('i', $_SESSION['id_usuario']);
              $stmt->execute();
              echo $stmt->get_result()->fetch_assoc()['total'];
              ?>
            </strong>
          </div>

          <div onclick="if(typeof abrirModalUsuarios === 'function') abrirModalUsuarios('siguiendo')"
            style="cursor: pointer; text-align: center; background: var(--card2); border: 1px solid var(--border); padding: 12px; border-radius: 14px; transition: background 0.2s;">
            <span style="display:block; font-size:0.85rem; color: var(--muted); margin-bottom: 4px;">Siguiendo</span>
            <strong style="display:block; font-size: 1.2rem; color: var(--text);">
              <?php
              $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_seguidor = ?');
              $stmt->bind_param('i', $_SESSION['id_usuario']);
              $stmt->execute();
              echo $stmt->get_result()->fetch_assoc()['total'];
              ?>
            </strong>
          </div>

          <div style="text-align: center; background: var(--card2); border: 1px solid var(--border); padding: 12px; border-radius: 14px;">
            <span style="display:block; font-size:0.85rem; color: var(--muted); margin-bottom: 4px;">Publicaciones</span>
            <strong style="display:block; font-size: 1.2rem; color: var(--text);">
              <?php
              $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM publicacion WHERE id_usuario = ?');
              $stmt->bind_param('i', $_SESSION['id_usuario']);
              $stmt->execute();
              echo $stmt->get_result()->fetch_assoc()['total'];
              ?>
            </strong>
          </div>

        </div>

        <?php
        
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

          <div class="grid-publicaciones" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px;">
            <?php foreach ($pubs as $p):
              $idp = (int)$p['id_publicacion'];
              $txt = (string)($p['texto'] ?? '');
              
              
              $imgUrlPost = '';
              if (!empty($p['imagen'])) {
                  $base64Post = base64_encode($p['imagen']);
                  $imgUrlPost = 'data:image/jpeg;base64,' . $base64Post;
              }
            ?>
              <div
                class="grid-item post-preview-click"
                data-id="<?php echo $idp; ?>"
                style="cursor: pointer; position: relative; aspect-ratio: 1/1; background: var(--card2); overflow: hidden; border-radius: 4px; border:1px solid var(--border);">

                <?php if ($imgUrlPost): ?>
                  <img src="<?php echo $imgUrlPost; ?>" alt="Post" style="width: 100%; height: 100%; object-fit: cover; display:block;">
                <?php else: ?>
                  <div style="padding: 10px; font-size: 0.8rem; color: var(--text); height: 100%; display: flex; align-items: center; justify-content: center; text-align: center; word-break: break-word;">
                    <?php echo htmlspecialchars(mb_strimwidth($txt, 0, 80, '...')); ?>
                  </div>
                <?php endif; ?>

              </div>
            <?php endforeach; ?>
          </div>
        </section>

      </section>
    </main>

    <aside class="barra-derecha">
      <section class="panel">
        <h2>Sugerencias</h2>
        <p style="color:var(--muted); padding:10px; font-style:italic;">Pronto más sugerencias...</p>
      </section>
    </aside>

  </div>
  <?php include __DIR__ . '/modal_EditarPerfil.php'; ?>

  <script>
    
    document.addEventListener('click', (e) => {
      const postPreview = e.target.closest('.post-preview-click');
      if (postPreview) {
        e.preventDefault();
        e.stopPropagation();
        const id = postPreview.dataset.id;
        if (typeof window.cargarVistaPublicacion === 'function') {
          window.cargarVistaPublicacion(id);
        } else {
          window.location.href = `index.php?post=${id}`;
        }
      }
    });

    
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
          
          location.reload(); 
        } catch (err) {
          console.error(err);
          alert('Error al subir la imagen');
        }
      }
    });
  </script>

</body>
</html>