<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$u = trim((string)($_GET['u'] ?? ''));

if ($u === '' || !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $u)) {
  http_response_code(400);
  echo "<!doctype html><html><head><title>Perfil</title></head><body><main class='contenido-principal'><p>Usuario inválido.</p></main></body></html>";
  exit;
}

$stmt = $mysqli->prepare("
  SELECT id_usuario, usuario, nombre, biografia, foto_perfil
  FROM usuario
  WHERE usuario = ?
  LIMIT 1
");
$stmt->bind_param('s', $u);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
  http_response_code(404);
  echo "<!doctype html><html><head><title>Perfil</title></head><body><main class='contenido-principal'><p>Usuario no encontrado.</p></main></body></html>";
  exit;
}

$idPerfil = (int)$user['id_usuario'];
$usuario  = (string)($user['usuario'] ?? '');
$nombre   = (string)($user['nombre'] ?? '');
$bio      = (string)($user['biografia'] ?? '');

$foto     = trim((string)($user['foto_perfil'] ?? ''));
$fotoUrl  = '';
if ($foto !== '') {
  $fotoUrl = '../multimedia/' . rawurlencode($foto);
}

$stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_usuario = ?');
$stmt->bind_param('i', $idPerfil);
$stmt->execute();
$seguidores = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $mysqli->prepare('SELECT COUNT(*) total FROM seguidores WHERE id_seguidor = ?');
$stmt->bind_param('i', $idPerfil);
$stmt->execute();
$seguiendo = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $mysqli->prepare('SELECT COUNT(*) total FROM publicacion WHERE id_usuario = ?');
$stmt->bind_param('i', $idPerfil);
$stmt->execute();
$publicaciones = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $mysqli->prepare("
  SELECT id_publicacion, imagen, texto, pie_foto, fecha_publicacion
  FROM publicacion
  WHERE id_usuario = ?
  ORDER BY fecha_publicacion DESC, id_publicacion DESC
");
$stmt->bind_param('i', $idPerfil);
$stmt->execute();
$pubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$yo = (int)($_SESSION['id_usuario'] ?? 0);
$sigo = false;

if ($yo > 0 && $yo !== $idPerfil) {
  $stmt = $mysqli->prepare("SELECT 1 FROM seguidores WHERE id_usuario = ? AND id_seguidor = ? LIMIT 1");
  $stmt->bind_param('ii', $idPerfil, $yo);
  $stmt->execute();
  $sigo = $stmt->get_result()->num_rows > 0;
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Perfil · @<?php echo htmlspecialchars($usuario); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>
  <main class="contenido-principal">

    <section class="cabecera-perfil">
      <div class="banner">
        <a href="javascript:history.back()" class="volver">← Volver</a>
      </div>

      <div class="info-perfil">
        <div class="avatar">
          <?php if ($fotoUrl): ?>
            <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto de perfil">
          <?php else: ?>
            <span>👤</span>
          <?php endif; ?>
        </div>

        <div class="perfil-mini">
          <p class="bio-perfil" id="perfilBio"><?php echo htmlspecialchars($bio); ?></p>
        </div>

        <?php if ($yo > 0 && $yo !== $idPerfil): ?>
          <div class="acciones-perfil">
            <button
              id="btnSeguir"
              class="boton-registrarse"
              data-id="<?php echo $idPerfil; ?>"
              data-sigo="<?php echo $sigo ? '1' : '0'; ?>"
              type="button"
            >
              <?php echo $sigo ? 'Dejar de seguir' : 'Seguir'; ?>
            </button>

            <button
              id="btnChat"
              class="boton-registrarse boton-secundario"
              data-user="<?php echo htmlspecialchars($usuario); ?>"
              type="button"
            >
              💬 Chat
            </button>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="datos-perfil">
      <h2>@<?php echo htmlspecialchars($usuario); ?></h2>
      <p class="nombre-real"><?php echo htmlspecialchars($nombre); ?></p>

      <div class="estadisticas">
        <span>Siguidores <strong id="nSeguidores"><?php echo $seguidores; ?></strong></span>
        <span>Seguiendo <strong><?php echo $seguiendo; ?></strong></span>
        <span>Publicaciones <strong><?php echo $publicaciones; ?></strong></span>
      </div>

      <section class="mis-publicaciones">
        <h3 class="titulo-seccion">Publicaciones</h3>

        <div class="grid-publicaciones">
          <?php foreach ($pubs as $p):
            $idp = (int)$p['id_publicacion'];
            $img = trim((string)($p['imagen'] ?? ''));
            $txt = (string)($p['texto'] ?? '');
            $pie = (string)($p['pie_foto'] ?? '');
            $imgUrl = $img !== '' ? '../multimedia/' . rawurlencode($img) : '';
          ?>
            <button
              type="button"
              class="grid-item"
              data-id="<?php echo $idp; ?>"
              data-img="<?php echo htmlspecialchars($imgUrl); ?>"
              data-pie="<?php echo htmlspecialchars($pie); ?>"
              data-desc="<?php echo htmlspecialchars($txt); ?>"
              data-fecha="<?php echo htmlspecialchars($p['fecha_publicacion'] ?? ''); ?>"
            >
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
</body>
</html>
