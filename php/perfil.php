<?php
declare(strict_types=1);
// Si se carga vía AJAX la sesión ya existe, si entra directo la iniciamos
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Perfil · NeonNest</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/index.css" />
  <link rel="stylesheet" href="../css/modal.css" />
  <link rel="stylesheet" href="../css/perfil.css" /> <link rel="icon" href="../multimedia/file.svg">
</head>

<body>
  <div class="contenedor-inicio layout-solo-main"> <main class="contenido-principal">
      <section class="cabecera-perfil">
        <div class="banner">
          <a href="#" class="volver" onclick="if(window.history.length > 1){ window.history.back(); return false; } else { window.location.href='index.php'; }">← Volver</a>
        </div>
        
        <?php
        // Datos del usuario logueado
        $foto = $_SESSION['foto_perfil'] ?? '';
        $fotoUrl = ($foto !== '') ? '../multimedia/' . rawurlencode($foto) : '';
        $bioActual = $_SESSION['biografia'] ?? '';
        ?>

        <div class="info-perfil">
          <form id="formFotoPerfil" class="avatar-form" method="POST" action="subirFotoPerfil.php" enctype="multipart/form-data">
            <label class="avatar avatar-click" for="inputFotoPerfil" title="Cambiar foto">
              <?php if ($fotoUrl): ?>
                <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto de perfil">
              <?php else: ?>
                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--card2); color:var(--muted); font-size:2rem;">👤</div>
              <?php endif; ?>
            </label>

            <input type="file" id="inputFotoPerfil" name="foto_perfil" accept="image/*" class="input-file-oculto">
          </form>

          <div class="perfil-mini">
            <p class="bio-perfil" id="perfilBio"><?php echo htmlspecialchars($bioActual); ?></p>
          </div>

          <button id="botonEditarPerfil" class="boton-registrarse boton-editar">Editar perfil</button>
        </div>

        <div style="text-align:right; padding:10px;">
          <a href="cerrar_sesion.php" class="boton-cerrar-sesion" style="color:#ff4757; text-decoration:none; font-size:0.9rem;">
            Cerrar sesión
          </a>
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
        // Obtener publicaciones del usuario
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
              $img = trim((string)($p['imagen'] ?? ''));
              $txt = (string)($p['texto'] ?? '');
              $imgUrl = $img !== '' ? '../multimedia/' . rawurlencode($img) : '';
            ?>
              <div 
                class="grid-item post-preview-click" 
                data-id="<?php echo $idp; ?>"
                style="cursor: pointer; position: relative; aspect-ratio: 1/1; background: var(--card2); overflow: hidden; border-radius: 4px; border:1px solid var(--border);">
                
                <?php if ($imgUrl): ?>
                  <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Post" style="width: 100%; height: 100%; object-fit: cover; display:block;">
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
    // 1. Detectar clic en el Grid y navegar
    document.addEventListener('click', (e) => {
        const postPreview = e.target.closest('.post-preview-click');
        
        if (postPreview) {
            e.preventDefault();
            e.stopPropagation();
            const id = postPreview.dataset.id;
            
            // Si estamos dentro del sistema SPA (Index), usamos su función
            if (typeof window.cargarVistaPublicacion === 'function') {
                window.cargarVistaPublicacion(id);
            } else {
                // Fallback: Si se entró directo a perfil.php, vamos al index con el post
                window.location.href = `index.php?post=${id}`;
            }
        }
    });

    // 2. Subida de Foto de Perfil AJAX
    document.addEventListener('change', async (e) => {
      if (e.target && e.target.id === 'inputFotoPerfil') {
        const input = e.target;
        const form = input.closest('form');
        if (!form || !input.files || input.files.length === 0) return;

        try {
          const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
          // Recargar para ver cambios
          const u = new URLSearchParams(window.location.search).get('u');
          if (typeof window.loadUserProfile === 'function' && u) {
             window.loadUserProfile(u);
          } else {
             location.reload();
          }
        } catch (err) {
          console.error(err);
          alert('Error al subir la imagen');
        }
      }
    });
  </script>

</body>
</html>