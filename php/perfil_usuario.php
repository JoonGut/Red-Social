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

// Obtener datos usuario
$stmt = $mysqli->prepare("SELECT id_usuario, usuario, nombre, biografia, foto_perfil FROM usuario WHERE usuario = ? LIMIT 1");
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

// Contadores
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

// Publicaciones
$stmt = $mysqli->prepare("SELECT id_publicacion, imagen, texto, pie_foto, fecha_publicacion FROM publicacion WHERE id_usuario = ? ORDER BY fecha_publicacion DESC, id_publicacion DESC");
$stmt->bind_param('i', $idPerfil);
$stmt->execute();
$pubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Estado "Te sigo"
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
    <link rel="stylesheet" href="../css/index.css" />
    <link rel="stylesheet" href="../css/perfil.css" />
</head>

<body>
    <main class="contenido-principal">

       <section class="cabecera-perfil">
            <div class="banner" style="background-image: url('../multimedia/file.svg'); background-size: cover; background-position: center; height:150px; position:relative;">
                <a href="javascript:history.back()" class="volver" style="position:absolute; top:15px; left:15px; background:rgba(0,0,0,0.5); color:white; padding:5px 12px; border-radius:20px; text-decoration:none; font-weight:bold; backdrop-filter:blur(4px);">← Volver</a>
            </div>

            <div class="info-perfil" style="
                display: flex; 
                justify-content: space-between; /* Separa Avatar a la izq y Botones a la der */
                align-items: flex-end;          /* Alinea todo abajo */
                padding: 0 20px 20px;           /* Espacio interno */
                margin-top: -40px;              /* Sube la caja blanca sobre el banner */
                position: relative;             /* Necesario para el z-index */
                z-index: 2;">

                <div class="avatar" style="
                    width:100px; height:100px; 
                    border-radius:50%; 
                    border:4px solid var(--bg); 
                    overflow:hidden; 
                    background:var(--card2); 
                    display:flex; align-items:center; justify-content:center;
                    flex-shrink: 0; /* Evita que se aplaste */">
                    <?php if ($fotoUrl): ?>
                        <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <span style="font-size:2.5rem;">👤</span>
                    <?php endif; ?>
                </div>

                <?php if ($yo > 0 && $yo !== $idPerfil): ?>
                    <div class="acciones-perfil" style="
                        display: flex; 
                        gap: 10px;          /* Espacio entre botones */
                        padding-bottom: 10px; /* Un poco de aire abajo */">
                        
                        <button
                            id="btnChat"
                            class="boton-registrarse boton-secundario"
                            data-user="<?php echo htmlspecialchars($usuario); ?>"
                            type="button"
                            style="background:var(--card2); color:var(--text); border:1px solid var(--border); padding:8px 20px; border-radius:20px; font-weight:bold; cursor:pointer; display: flex; align-items: center; gap: 5px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            Chat
                        </button>

                        <button
                            id="btnSeguir"
                            class="boton-registrarse btn-accion-seguir"
                            data-id="<?php echo $idPerfil; ?>"
                            data-sigo="<?php echo $sigo ? '1' : '0'; ?>"
                            type="button"
                            style="
                                padding:8px 20px; 
                                border-radius:20px; 
                                font-weight:bold; 
                                cursor:pointer;
                                border: <?php echo $sigo ? '1px solid var(--border)' : 'none'; ?>;
                                background: <?php echo $sigo ? 'transparent' : 'var(--text)'; ?>;
                                color: <?php echo $sigo ? 'var(--text)' : 'var(--bg)'; ?>;">
                            <?php echo $sigo ? 'Siguiendo' : 'Seguir'; ?>
                        </button>
                    </div>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
            </div>

            <div class="perfil-mini" style="padding: 0 20px;">
                <p class="bio-perfil" id="perfilBio" style="margin-top: 10px;"><?php echo htmlspecialchars($bio); ?></p>
            </div>

        </section>

        <section class="datos-perfil">
            <h2>@<?php echo htmlspecialchars($usuario); ?></h2>
            <p class="nombre-real"><?php echo htmlspecialchars($nombre); ?></p>

            <div class="estadisticas" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 14px 0 10px;">
                
                <div onclick="if(typeof abrirModalUsuarios === 'function') abrirModalUsuarios('seguidores', <?php echo $idPerfil; ?>)"
                     style="cursor: pointer; text-align: center; background: var(--card2); border: 1px solid var(--border); padding: 12px; border-radius: 14px; transition: background 0.2s;">
                    <span style="display:block; font-size:0.85rem; color: var(--muted); margin-bottom: 4px;">Seguidores</span>
                    <strong id="nSeguidores" style="display:block; font-size: 1.2rem; color: var(--text);"><?php echo $seguidores; ?></strong>
                </div>

                <div onclick="if(typeof abrirModalUsuarios === 'function') abrirModalUsuarios('siguiendo', <?php echo $idPerfil; ?>)"
                     style="cursor: pointer; text-align: center; background: var(--card2); border: 1px solid var(--border); padding: 12px; border-radius: 14px; transition: background 0.2s;">
                    <span style="display:block; font-size:0.85rem; color: var(--muted); margin-bottom: 4px;">Siguiendo</span>
                    <strong style="display:block; font-size: 1.2rem; color: var(--text);"><?php echo $seguiendo; ?></strong>
                </div>

                <div style="text-align: center; background: var(--card2); border: 1px solid var(--border); padding: 12px; border-radius: 14px;">
                    <span style="display:block; font-size:0.85rem; color: var(--muted); margin-bottom: 4px;">Publicaciones</span>
                    <strong style="display:block; font-size: 1.2rem; color: var(--text);"><?php echo $publicaciones; ?></strong>
                </div>
            </div>

            <section class="mis-publicaciones">
                <h3 class="titulo-seccion">Publicaciones</h3>

                <div class="grid-publicaciones" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px;">
                    <?php foreach ($pubs as $p):
                        $idp = (int)$p['id_publicacion'];
                        $img = trim((string)($p['imagen'] ?? ''));
                        $txt = (string)($p['texto'] ?? '');
                        $pie = (string)($p['pie_foto'] ?? '');
                        $imgUrl = $img !== '' ? '../multimedia/' . rawurlencode($img) : '';
                    ?>
                        <div
                            class="grid-item post-preview-click"
                            data-id="<?php echo $idp; ?>"
                            style="cursor: pointer; position: relative; aspect-ratio: 1/1; background: var(--card2); overflow: hidden; border-radius: 4px; border:1px solid var(--border);">
                            
                            <?php if ($imgUrl): ?>
                                <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Publicación" style="width: 100%; height: 100%; object-fit: cover; display:block;">
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
</body>

</html>