<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

$miId = (int)($_SESSION['id_usuario'] ?? 0);
$pId  = (int)($_GET['id'] ?? 0);

if ($pId <= 0) {
    echo '<div style="color:var(--text); padding:20px;">Publicación no válida.</div>';
    exit;
}


$sql = "
    SELECT 
        p.*, 
        u.usuario, 
        u.nombre, 
        u.foto_perfil,
        (SELECT COUNT(*) FROM interaccion WHERE id_publicacion = p.id_publicacion AND tipo_interaccion = 'LIKE') as num_likes,
        (SELECT COUNT(*) FROM interaccion WHERE id_publicacion = p.id_publicacion AND id_usuario = ? AND tipo_interaccion = 'LIKE') as liked_by_me
    FROM publicacion p
    JOIN usuario u ON p.id_usuario = u.id_usuario
    WHERE p.id_publicacion = ?
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $miId, $pId);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();

if (!$post) {
    echo '<div style="color:var(--text); padding:20px;">La publicación no existe o fue eliminada.</div>';
    exit;
}


$usuario  = htmlspecialchars($post['usuario']);
$nombre   = htmlspecialchars($post['nombre']);
$textoRaw = htmlspecialchars($post['texto']);
$fecha    = date('g:i A · d M. Y', strtotime($post['fecha_publicacion']));




$fotoPerfil = '';
if (!empty($post['foto_perfil'])) {
    $base64Perfil = base64_encode($post['foto_perfil']);
    $fotoPerfil = 'data:image/jpeg;base64,' . $base64Perfil;
}


$imgPost = '';
if (!empty($post['imagen'])) {
    $base64Post = base64_encode($post['imagen']);
    $imgPost = 'data:image/jpeg;base64,' . $base64Post;
}


$likes = $post['num_likes'];
$isLiked = $post['liked_by_me'] > 0;
$heartClass = $isLiked ? 'fas fa-heart' : 'far fa-heart';
$heartColor = $isLiked ? 'color:#e0245e' : 'color:var(--muted)';


$textoProcesado = preg_replace(
    '/@(\w+)/',
    '<a href="#" class="user-link stop-prop" data-user="$1" style="color:var(--accent); text-decoration:none;">@$1</a>',
    nl2br($textoRaw)
);
?>

<div class="detalle-post-container" style="width:100%; border-right:1px solid var(--border); min-height:100vh;">
    <div style="padding:10px 15px; display:flex; align-items:center; gap:20px; position:sticky; top:0; background:var(--card); opacity:0.98; backdrop-filter:blur(10px); z-index:10; border-bottom:1px solid var(--border);">
        <button onclick="window.location.href='index.php'" style="background:none; border:none; color:var(--text); font-size:1.2rem; cursor:pointer;">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h2 style="font-size:1.2rem; margin:0; color:var(--text);">Publicación</h2>
    </div>

    <div style="padding:15px;">

        <div style="display:flex; gap:12px; margin-bottom:15px;">
            <?php if ($fotoPerfil): ?>
                <img src="<?php echo $fotoPerfil; ?>" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">
            <?php else: ?>
                <div style="width:48px; height:48px; border-radius:50%; background:var(--card2); display:flex; align-items:center; justify-content:center; font-weight:bold; color:var(--text); font-size:1.2rem;">
                    <?php echo strtoupper(substr($usuario, 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div style="display:flex; flex-direction:column; justify-content:center;">
                <span style="font-weight:bold; font-size:1rem; color:var(--text);"><?php echo $nombre; ?></span>
                <span style="color:var(--muted);">@<?php echo $usuario; ?></span>
            </div>
        </div>

        <?php if (!empty($post['ubicacion'])): ?>
            <div style="font-size:0.9rem; color:var(--muted); margin-bottom:10px;">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($post['ubicacion']); ?>
            </div>
        <?php endif; ?>

        <div style="font-size:1.4rem; line-height:1.4; color:var(--text); margin-bottom:15px;">
            <?php echo $textoProcesado; ?>
        </div>

        <?php if ($imgPost): ?>
            <div style="margin-bottom:15px; border-radius:15px; overflow:hidden; border:1px solid var(--border);">
                <img src="<?php echo $imgPost; ?>" style="width:100%; display:block;">
            </div>
        <?php endif; ?>

        <div style="border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:15px;">
            <span style="color:var(--muted); font-size:0.95rem;"><?php echo $fecha; ?></span>
        </div>

        <?php if ($likes > 0): ?>
            <div style="border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:15px; display:flex; gap:20px;">
                <span><strong style="color:var(--text);"><?php echo $likes; ?></strong> <span style="color:var(--muted);">Me gusta</span></span>
            </div>
        <?php endif; ?>

        <div style="display:flex; justify-content:space-around; padding-bottom:15px; border-bottom:1px solid var(--border);">

            <button onclick="document.getElementById('inputComentarioDetalle').focus()" style="background:none; border:none; color:var(--muted); font-size:1.3rem; cursor:pointer;" title="Comentar">
                <i class="far fa-comment"></i>
            </button>

            <button id="btnLikeBig" class="btn-like-inline" data-id="<?php echo $pId; ?>" data-liked="<?php echo $isLiked ? '1' : '0'; ?>" style="background:none; border:none; font-size:1.3rem; cursor:pointer; transition:transform 0.1s; <?php echo $heartColor; ?>" title="Me gusta">
                <i class="<?php echo $heartClass; ?> icon-heart"></i>
            </button>

            <button style="background:none; border:none; color:var(--muted); font-size:1.3rem; cursor:pointer;" title="Compartir">
                <i class="fas fa-share"></i>
            </button>

            <?php if ((int)$post['id_usuario'] === $miId): ?>
                <button onclick="event.stopPropagation(); eliminarPublicacion(<?php echo $pId; ?>, this)"
                    style="background:none; border:none; color:#f4212e; font-size:1.3rem; cursor:pointer; display:flex; align-items:center;"
                    title="Eliminar publicación">
                    <i class="fas fa-trash-alt"></i>
                </button>
            <?php endif; ?>
        </div>

        <div style="margin-top:20px; display:flex; gap:10px;">
            <div style="width:40px; height:40px; border-radius:50%; background:var(--card2); overflow:hidden; display:flex; align-items:center; justify-content:center;">
                <?php
                
                
                $miFotoBase64 = $_SESSION['foto_perfil'] ?? '';
                
                if ($miFotoBase64) {
                    
                    echo '<img src="data:image/jpeg;base64,' . $miFotoBase64 . '" style="width:100%; height:100%; object-fit:cover;">';
                } else {
                    echo '<span style="font-size:1.2rem;">😊</span>';
                }
                ?>
            </div>
            <form id="formComentarioDetalle" data-id="<?php echo $pId; ?>" style="flex:1;">
                <input type="text" id="inputComentarioDetalle" placeholder="Postea tu respuesta" style="width:100%; background:none; border:none; border-bottom:1px solid var(--border); padding:10px; color:var(--text); font-size:1.1rem; outline:none;">
                <div style="text-align:right; margin-top:10px;">
                    <button type="submit" style="background:var(--accent); color:#fff; border:none; padding:8px 18px; border-radius:20px; font-weight:bold; cursor:pointer; font-size:0.9rem;">Responder</button>
                </div>
            </form>
        </div>

    </div>

    <div id="contenedor-comentarios" style="padding-bottom:100px;">
    </div>
</div>

<script>
    
    if (typeof loadCommentsForView === 'function') {
        loadCommentsForView(<?php echo $pId; ?>);
    }

    
    document.getElementById('formComentarioDetalle').addEventListener('submit', async function(e) {
        e.preventDefault();
        const input = document.getElementById('inputComentarioDetalle');
        const txt = input.value.trim();
        const btn = this.querySelector('button');

        if (!txt) return;
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        try {
            const fd = new URLSearchParams();
            fd.append('id_publicacion', this.dataset.id);
            fd.append('texto', txt);

            const res = await fetch('comentar.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();

            if (data.ok) {
                input.value = '';
                
                loadCommentsForView(this.dataset.id);
            }
        } catch (err) {
            console.error(err);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Responder';
        }
    });
</script>