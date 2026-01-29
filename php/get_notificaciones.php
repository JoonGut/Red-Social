<?php
declare(strict_types=1);
ob_start(); // Buffer para proteger el JSON

session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false, 'items' => [], 'sin_leer' => 0];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) {
        throw new Exception('no-login');
    }

    // 1. MARCAR LEIDAS (Si se solicita)
    if (isset($_POST['marcar_leidas'])) {
        $stmt = $mysqli->prepare("UPDATE notificaciones SET leido = 1 WHERE id_usuario = ?");
        $stmt->bind_param('i', $yo);
        $stmt->execute();
        $stmt->close();
    }

    // 2. CONTAR SIN LEER
    $stmtCount = $mysqli->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE id_usuario = ? AND leido = 0");
    $stmtCount->bind_param('i', $yo);
    $stmtCount->execute();
    $resCount = $stmtCount->get_result()->fetch_assoc();
    $sinLeer = (int)($resCount['total'] ?? 0);
    $stmtCount->close();

    // 3. OBTENER NOTIFICACIONES
    // CAMBIO REALIZADO: 'n.fecha' -> 'n.creado_en'
    $sql = "
        SELECT 
            n.id_notificacion, 
            n.tipo, 
            n.leido, 
            n.referencia_id, 
            n.texto_extra, 
            n.creado_en, 
            u.usuario AS actor_usuario,
            u.foto_perfil AS actor_foto
        FROM notificaciones n
        JOIN usuario u ON n.id_actor = u.id_usuario
        WHERE n.id_usuario = ?
        ORDER BY n.creado_en DESC
        LIMIT 20
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $yo);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($row = $res->fetch_assoc()) {
        
        // CONVERTIR FOTO BLOB A BASE64
        if (!empty($row['actor_foto'])) {
            $row['actor_foto'] = 'data:image/jpeg;base64,' . base64_encode($row['actor_foto']);
        } else {
            $row['actor_foto'] = null; 
        }

        // GENERAR TEXTOS
        $tipo = $row['tipo'];
        $texto = "";
        $link = "";

        switch ($tipo) {
            case 'like':
                $texto = "le gustó tu publicación";
                $link = "javascript:cargarVistaPublicacion(" . (int)$row['referencia_id'] . ")";
                break;
            case 'comentario':
                $texto = "comentó tu publicación";
                $link = "javascript:cargarVistaPublicacion(" . (int)$row['referencia_id'] . ")";
                break;
            case 'seguir':
                $texto = "comenzó a seguirte";
                $link = "javascript:loadUserProfile('" . htmlspecialchars($row['actor_usuario']) . "')";
                break;
            case 'etiqueta':
                $texto = "te etiquetó en una publicación";
                $link = "javascript:cargarVistaPublicacion(" . (int)$row['referencia_id'] . ")";
                break;
            case 'mensaje':
                $texto = "te envió un mensaje";
                $link = "javascript:sessionStorage.setItem('chatUser', '" . htmlspecialchars($row['actor_usuario']) . "'); loadPage('chat');";
                break;
            default:
                $texto = "nueva notificación";
                $link = "#";
        }

        $row['texto_formato'] = $texto;
        $row['link_accion'] = $link;
        
        $items[] = $row;
    }
    $stmt->close();

    $response = [
        'ok' => true,
        'items' => $items,
        'sin_leer' => $sinLeer
    ];

} catch (Exception $e) {
    // Si hay error SQL, lo enviamos en el JSON para depurar
    $response['error'] = $e->getMessage();
}

ob_clean(); // Limpiar cualquier salida previa
echo json_encode($response);
exit;
?>