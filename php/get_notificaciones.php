<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

// Marcamos como leídas si se pide
if (isset($_POST['marcar_leidas'])) {
    $mysqli->query("UPDATE notificaciones SET leido = 1 WHERE id_usuario = $yo");
    echo json_encode(['ok' => true]);
    exit;
}

// 1. CONSULTA SQL (Corregida: Agregado n.referencia_id)
$sql = "
    SELECT 
        n.id_notificacion, 
        n.tipo, 
        n.texto_extra, 
        n.leido, 
        n.creado_en,
        n.referencia_id, 
        u.usuario AS actor_usuario, 
        u.foto_perfil AS actor_foto
    FROM notificaciones n
    JOIN usuario u ON u.id_usuario = n.id_actor
    WHERE n.id_usuario = ?
    ORDER BY n.id_notificacion DESC
    LIMIT 20
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $yo);
$stmt->execute();
$res = $stmt->get_result();

// 2. PROCESAR DATOS (Añadir iconos y textos bonitos)
$items = [];
while ($row = $res->fetch_assoc()) {
    
    // Valores por defecto
    $row['icono'] = '🔔'; 
    $row['texto_formato'] = 'Nueva interacción';
    $row['link_accion'] = '#'; 

    switch ($row['tipo']) {
        case 'like':
            $row['icono'] = '❤️';
            $row['texto_formato'] = "le gustó tu publicación";
            // Usamos la función global del index.php para abrir el post
            $row['link_accion'] = "javascript:if(typeof cargarVistaPublicacion==='function') cargarVistaPublicacion(" . $row['referencia_id'] . ")";
            break;

        case 'comentario':
            $row['icono'] = '💬';
            $row['texto_formato'] = "comentó tu publicación";
            $row['link_accion'] = "javascript:if(typeof cargarVistaPublicacion==='function') cargarVistaPublicacion(" . $row['referencia_id'] . ")";
            break;

        case 'seguir':
            $row['icono'] = '👤';
            $row['texto_formato'] = "comenzó a seguirte";
            $row['link_accion'] = "javascript:if(typeof loadUserProfile==='function') loadUserProfile('" . $row['actor_usuario'] . "')";
            break;

        case 'etiqueta':  // <--- AQUÍ ESTÁ LA MAGIA NUEVA
            $row['icono'] = '🏷️'; // Icono de etiqueta
            $row['texto_formato'] = "te etiquetó en una publicación";
            $row['link_accion'] = "javascript:if(typeof cargarVistaPublicacion==='function') cargarVistaPublicacion(" . $row['referencia_id'] . ")";
            break;
    }

    $items[] = $row;
}

// 3. CONTAR NO LEÍDAS
$resCount = $mysqli->query("SELECT COUNT(*) c FROM notificaciones WHERE id_usuario = $yo AND leido = 0");
$rowC = $resCount->fetch_assoc();
$sinLeer = $rowC['c'];

echo json_encode(['ok' => true, 'items' => $items, 'sin_leer' => $sinLeer]);
?>