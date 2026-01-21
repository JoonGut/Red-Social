<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';

$pId = (int)($_GET['id'] ?? 0);
$miId = (int)($_SESSION['id_usuario'] ?? 0);

if ($pId <= 0) {
    echo "<h2 style='color:white; padding:20px;'>Post no encontrado</h2>";
    exit;
}

// 1. Obtener el Post Principal
$sql = "
    SELECT p.*, u.usuario, u.foto_perfil,
    (SELECT COUNT(*) FROM interaccion WHERE id_publicacion = p.id_publicacion AND tipo_interaccion='LIKE') as likes,
    (SELECT COUNT(*) FROM interaccion WHERE id_publicacion = p.id_publicacion AND id_usuario = ? AND tipo_interaccion='LIKE') as liked_by_me,
    (SELECT COUNT(*) FROM interaccion WHERE id_publicacion = p.id_publicacion AND tipo_interaccion='COMENTARIO') as coments
    FROM publicacion p 
    JOIN usuario u ON p.id_usuario = u.id_usuario 
    WHERE p.id_publicacion = ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $miId, $pId);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo "<h2 style='color:white; padding:20px;'>El post ha sido eliminado.</h2>";
    exit;
}

// Variables visuales
$isLiked = $post['liked_by_me'] > 0;
$heartClass = $isLiked ? 'fas fa-heart' : 'far fa-heart';
$heartColor = $isLiked ? 'color:#e0245e' : 'color:#71767b';
?>

<header class="cabecera sticky-top" style="display:flex; align-items:center; gap:20px; padding:10px 15px; border-bottom:1px solid #333;">
    <button onclick="window.history.back()" style="background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer; padding:5px;">
        <i class="fas fa-arrow-left"></i>
    </button>
    <h2 style="margin:0; font-size:1.2rem;">Post</h2>
</header>

<div class="single-post-view" style="padding:15px; border-bottom:1px solid #333;">
    <div style="display:flex; gap:10px; align-items:center; margin-bottom:15px;">
        <img src="<?php echo $post['foto_perfil'] ? '../multimedia/'.$post['foto_perfil'] : '../multimedia/file.svg'; ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
        <div>
            <div style="font-weight:bold;">@<?php echo htmlspecialchars($post['usuario']); ?></div>
        </div>
    </div>

    <div style="font-size:1.1rem; line-height:1.5; margin-bottom:15px; color:#fff;">
        <?php echo nl2br(htmlspecialchars($post['texto'] ?? '')); ?>
    </div>

    <?php if($post['imagen']): ?>
        <div style="margin-bottom:15px; border-radius:15px; overflow:hidden; border:1px solid #333;">
            <img src="../multimedia/<?php echo rawurlencode($post['imagen']); ?>" style="width:100%; display:block;">
        </div>
    <?php endif; ?>

    <div style="color:#71767b; font-size:0.9rem; margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid #333;">
        <?php echo date('h:i A · d M, Y', strtotime($post['fecha_publicacion'])); ?>
    </div>

    <div style="display:flex; gap:20px; padding-bottom:15px; border-bottom:1px solid #333; margin-bottom:15px;">
        <div><strong id="sp-likes"><?php echo $post['likes']; ?></strong> <span style="color:#71767b;">Likes</span></div>
        <div><strong id="sp-coments"><?php echo $post['coments']; ?></strong> <span style="color:#71767b;">Comentarios</span></div>
    </div>

    <div style="display:flex; justify-content:space-around; padding-bottom:15px; border-bottom:1px solid #333;">
        <button style="background:none; border:none; color:#71767b; font-size:1.2rem; cursor:pointer;">
            <i class="far fa-comment"></i>
        </button>
        <button id="btnLikeBig" class="btn-like-inline" data-id="<?php echo $pId; ?>" data-liked="<?php echo $isLiked?'1':'0'; ?>" style="background:none; border:none; font-size:1.2rem; cursor:pointer; <?php echo $heartColor; ?>">
            <i class="<?php echo $heartClass; ?> icon-heart"></i>
        </button>
        <button style="background:none; border:none; color:#71767b; font-size:1.2rem; cursor:pointer;">
            <i class="fas fa-share"></i>
        </button>
    </div>

    <div style="padding:15px 0;">
        <form class="form-inline-comment" data-id="<?php echo $pId; ?>" data-is-main="true" style="display:flex; gap:10px;">
            <img src="../multimedia/file.svg" style="width:40px; height:40px; border-radius:50%; background:#333;">
            <div style="flex:1;">
                <input type="text" name="texto" placeholder="Postea tu respuesta" style="width:100%; background:none; border:none; color:#fff; font-size:1.1rem; outline:none; padding:10px 0;">
                <div style="text-align:right; margin-top:5px;">
                    <button type="submit" style="background:#1d9bf0; color:#fff; border:none; padding:8px 18px; border-radius:20px; cursor:pointer; font-weight:bold;">Responder</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="contenedor-comentarios">
    </div>

<script>
    // Script automático al cargar esta vista: Cargar comentarios
    if(typeof loadCommentsForView === 'function') {
        loadCommentsForView(<?php echo $pId; ?>);
    }
</script>