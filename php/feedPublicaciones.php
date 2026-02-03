<?php

declare(strict_types=1);
// Verificar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php'; // Asegúrate de que la ruta a db.php es correcta desde donde se incluye este archivo

$miId = (int)($_SESSION['id_usuario'] ?? 0);

// Consulta SQL Principal (INTACTA)
$sql = "
    SELECT 
        p.*, 
        u.usuario, 
        u.foto_perfil,
        (SELECT COUNT(*) 
         FROM interaccion i 
         WHERE i.id_publicacion = p.id_publicacion 
         AND i.tipo_interaccion = 'LIKE') as num_likes,
        (SELECT COUNT(*) 
         FROM interaccion i 
         WHERE i.id_publicacion = p.id_publicacion 
         AND i.id_usuario = ? 
         AND i.tipo_interaccion = 'LIKE') as liked_by_me,
        (SELECT COUNT(*) 
         FROM interaccion i 
         WHERE i.id_publicacion = p.id_publicacion 
         AND i.tipo_interaccion = 'COMENTARIO') as num_comentarios
    FROM publicacion p
    JOIN usuario u ON p.id_usuario = u.id_usuario
    ORDER BY p.fecha_publicacion DESC
    LIMIT 50
";

// Usamos $mysqli asumiendo que db.php lo define. Si no, ajusta según tu conexión.
// Si este archivo se incluye en index.php, $mysqli ya debería estar disponible.
global $mysqli;

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $miId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        // Datos Básicos
        $pId = $row['id_publicacion'];
        $pUser = htmlspecialchars($row['usuario']);
        $pFecha = date('d M', strtotime($row['fecha_publicacion']));

        // --- IMÁGENES (CORRECCIÓN BLOB) ---

        // 1. FOTO DE PERFIL DEL AUTOR DEL POST
        $pFoto = '';
        if (!empty($row['foto_perfil'])) {
            // Convertir BLOB a Base64
            $base64Perfil = base64_encode($row['foto_perfil']);
            $pFoto = 'data:image/jpeg;base64,' . $base64Perfil;
        }

        // 2. IMAGEN DE LA PUBLICACIÓN
        $pImg = '';
        if (!empty($row['imagen'])) {
            // Convertir BLOB a Base64
            $base64Img = base64_encode($row['imagen']);
            $pImg = 'data:image/jpeg;base64,' . $base64Img;
        }

        // Datos de Interacción
        $likes = $row['num_likes'];
        $coments = $row['num_comentarios'];
        $isLiked = $row['liked_by_me'] > 0;
        $heartClass = $isLiked ? 'fas fa-heart' : 'far fa-heart';
        $heartColor = $isLiked ? 'color:#e0245e' : 'color:var(--muted)';

        // --- PROCESAMIENTO DE TEXTO Y ETIQUETAS ---
        $textoRaw = htmlspecialchars($row['texto'] ?? '');
        $textoFormat = nl2br($textoRaw);

        $textoFinal = preg_replace(
            '/@(\w+)/',
            '<a href="#" class="user-link stop-prop" data-user="$1" style="color:var(--accent); text-decoration:none;">@$1</a>',
            $textoFormat
        );

?>
        <article class="post tweet-style" data-id="<?php echo $pId; ?>" style="cursor:pointer; transition:background 0.2s;">

            <div class="post-header" style="display:flex; gap:10px; margin-bottom:5px;">

                <div class="stop-prop" style="flex-shrink:0;">
                    <?php if ($pFoto): ?>
                        <img src="<?php echo $pFoto; ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <div style="width:40px; height:40px; border-radius:50%; background:var(--card2); display:flex; align-items:center; justify-content:center; font-weight:bold; color:var(--text);">
                            <?php echo strtoupper(substr($pUser, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between;">
                        <a href="#" class="user-link stop-prop" data-user="<?php echo $pUser; ?>" style="font-weight:bold; color:var(--text); text-decoration:none;">
                            @<?php echo $pUser; ?>
                        </a>
                        <small style="color:var(--muted);"><?php echo $pFecha; ?></small>
                    </div>

                    <div class="post-content-area" style="margin-top:5px;">

                        <?php if (!empty($row['ubicacion'])): ?>
                            <div style="font-size:0.85rem; color:var(--muted); margin-bottom:5px;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['ubicacion']); ?>
                            </div>
                        <?php endif; ?>

                        <div style="color:var(--text); margin-bottom:10px; line-height:1.5;">
                            <?php echo $textoFinal; ?>
                        </div>

                        <?php if ($pImg): ?>
                            <div class="post-img-container" style="border-radius:15px; overflow:hidden; border:1px solid var(--border); margin-top:10px;">
                                <img src="<?php echo $pImg; ?>" style="width:100%; display:block; max-height:500px; object-fit:cover;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="post-actions stop-prop" style="display:flex; justify-content:space-between; margin-top:12px; max-width:80%;">

                        <button class="btn-action btn-comment-inline" data-id="<?php echo $pId; ?>" style="background:none; border:none; color:var(--muted); cursor:pointer; display:flex; align-items:center; gap:5px;">
                            <i class="far fa-comment"></i>
                            <span class="count-comment"><?php echo $coments > 0 ? $coments : ''; ?></span>
                        </button>

                        <button class="btn-action btn-like-inline" data-id="<?php echo $pId; ?>" data-liked="<?php echo $isLiked ? '1' : '0'; ?>" style="background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:5px; <?php echo $heartColor; ?>">
                            <i class="<?php echo $heartClass; ?> icon-heart"></i>
                            <span class="count-like"><?php echo $likes > 0 ? $likes : ''; ?></span>
                        </button>

                        <button class="btn-action stop-prop"
                            onclick="event.stopPropagation(); window.compartirPost(<?php echo $pId; ?>)"
                            style="background:none; border:none; color:var(--muted); cursor:pointer;"
                            title="Compartir en Chat">
                            <i class="fas fa-share"></i>
                        </button>
                    </div>

                    <div class="inline-comment-box stop-prop" id="comment-box-<?php echo $pId; ?>" style="display:none; margin-top:10px; border-top:1px solid var(--border); padding-top:10px;">
                        <form class="form-inline-comment" data-id="<?php echo $pId; ?>" style="display:flex; gap:10px;">
                            <input type="text" name="texto" placeholder="Postea tu respuesta"
                                style="flex:1; background:var(--bg); border:1px solid var(--border); color:var(--text); padding:8px 12px; border-radius:20px; outline:none;"
                                autocomplete="off">
                            <button type="submit" style="background:var(--accent); color:#fff; border:none; padding:6px 15px; border-radius:20px; cursor:pointer; font-weight:bold;">Responder</button>
                        </form>
                    </div>

                </div>
            </div>
        </article>
<?php
    }
} else {
    echo '<div style="padding:40px; text-align:center; color:var(--muted);">';
    echo '<p style="font-size:1.2rem; font-weight:bold;">No hay publicaciones</p>';
    echo '<p>¡Sé el primero en publicar algo!</p>';
    echo '</div>';
}
?>