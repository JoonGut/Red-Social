<?php
// php/ajax_obtener_post.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

$idPost = (int)($_GET['id'] ?? 0);
$miId = (int)($_SESSION['id_usuario'] ?? 0);

if ($idPost <= 0) {
    echo "Post no encontrado.";
    exit;
}

// Reutilizamos la consulta para obtener un solo post
$sql = "
    SELECT 
        p.id_publicacion, p.texto, p.imagen, p.fecha_publicacion,
        u.usuario, u.foto_perfil,
        (SELECT COUNT(*) FROM interaccion i WHERE i.id_publicacion = p.id_publicacion AND i.tipo_interaccion = 'LIKE') as num_likes,
        (SELECT COUNT(*) FROM interaccion i WHERE i.id_publicacion = p.id_publicacion AND i.id_usuario = ? AND i.tipo_interaccion = 'LIKE') as liked_by_me
    FROM publicacion p
    JOIN usuario u ON p.id_usuario = u.id_usuario
    WHERE p.id_publicacion = ?
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $miId, $idPost);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    // Renderizamos el post usando las clases de modal.css
    $pUser = htmlspecialchars($row['usuario']);
    $pTxt  = nl2br(htmlspecialchars($row['texto'] ?? ''));
    $pFoto = $row['foto_perfil'] ? '../multimedia/' . rawurlencode($row['foto_perfil']) : '';
    $pImg  = $row['imagen'] ? '../multimedia/' . rawurlencode($row['imagen']) : '';
    
    // Contenedor principal del post para el modal (usa padding y flex definido en CSS)
    echo '<div class="post-modal">';

        // --- Header (Avatar + Usuario + Fecha) ---
        echo '<div class="post-head">';
            if($pFoto) {
                echo "<img src='$pFoto' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'>";
            } else {
                // Avatar por defecto usando variable de tema
                echo "<div style='width:50px; height:50px; border-radius:50%; background:var(--card2); display:flex; align-items:center; justify-content:center; color:var(--muted); font-weight:bold;'>".strtoupper(substr($pUser,0,1))."</div>";
            }
            
            echo '<div class="post-head-txt">';
                echo '<span class="post-user">@'.$pUser.'</span>';
                echo '<span class="post-meta">'.date('d M Y H:i', strtotime($row['fecha_publicacion'])).'</span>';
            echo '</div>';
        echo '</div>';

        // --- Texto ---
        if (!empty($pTxt)) {
            echo '<div class="post-text">'.$pTxt.'</div>';
        }

        // --- Imagen (Multimedia) ---
        if($pImg) {
            echo '<div class="post-media">';
                echo "<img src='$pImg'>";
            echo '</div>';
        }

        // --- Separador ---
        echo "<hr style='border:0; border-top:1px solid var(--border); margin: 10px 0;'>";
        
        // --- Placeholder Comentarios ---
        echo "<p style='color:var(--muted); text-align:center; font-size:0.9rem;'>
                <i class='far fa-comment-dots'></i> Comentarios próximamente...
              </p>";

    echo '</div>'; // Fin .post-modal

} else {
    echo "<div style='padding:20px; color:var(--muted); text-align:center;'>El post no existe o fue eliminado.</div>";
}
?>