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
    // Renderizamos el post en formato GRANDE para el modal
    $pUser = htmlspecialchars($row['usuario']);
    $pTxt  = nl2br(htmlspecialchars($row['texto'] ?? ''));
    $pFoto = $row['foto_perfil'] ? '../multimedia/' . rawurlencode($row['foto_perfil']) : '';
    $pImg  = $row['imagen'] ? '../multimedia/' . rawurlencode($row['imagen']) : '';
    
    // Header
    echo '<div style="display:flex; align-items:center; margin-bottom:15px;">';
        if($pFoto) echo "<img src='$pFoto' style='width:50px; height:50px; border-radius:50%; object-fit:cover; margin-right:10px;'>";
        else echo "<div style='width:50px; height:50px; border-radius:50%; background:#555; margin-right:10px;'></div>";
        echo "<div><h3 style='margin:0;'>@$pUser</h3><small style='color:#aaa;'>".date('d M Y H:i', strtotime($row['fecha_publicacion']))."</small></div>";
    echo '</div>';

    // Texto
    echo "<div style='font-size:1.1rem; line-height:1.6; margin-bottom:15px;'>$pTxt</div>";

    // Imagen
    if($pImg) {
        echo "<img src='$pImg' style='max-width:100%; border-radius:10px; margin-bottom:15px;'>";
    }

    echo "<hr style='border-color:#333; margin: 15px 0;'>";
    
    // Aquí podrías añadir zona de comentarios si la tienes
    echo "<p style='color:#aaa; text-align:center;'>Comentarios próximamente...</p>";
} else {
    echo "El post no existe o fue eliminado.";
}
?>