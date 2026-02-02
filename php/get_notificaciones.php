<?php
// php/get_notificaciones.php
declare(strict_types=1);

// Desactivar visualización de errores HTML para no romper el JSON
ini_set('display_errors', '0'); 
error_reporting(E_ALL);

ob_start(); // Iniciar buffer de salida

session_start();
header('Content-Type: application/json; charset=UTF-8');

// Ajusta la ruta si 'db.php' está en otra carpeta, según tu imagen está en la misma carpeta 'php/'
require __DIR__ . '/db.php'; 

$response = ['ok' => false, 'items' => [], 'sin_leer' => 0, 'error' => ''];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    
    // Si no hay sesión, devolvemos ok=false pero sin error grave
    if ($yo <= 0) {
        $response['error'] = 'no-login';
        echo json_encode($response);
        exit;
    }

    // 1. MARCAR LEIDAS
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
    // NOTA IMPORTANTE: Asegúrate que tu tabla tenga la columna 'creado_en'. Si usas 'fecha', cámbialo aquí.
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
    if (!$stmt) {
        throw new Exception("Error en SQL (Revisa nombres de columnas): " . $mysqli->error);
    }
    
    $stmt->bind_param('i', $yo);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($row = $res->fetch_assoc()) {
        // Convertir BLOB a Base64 si existe
        if (!empty($row['actor_foto'])) {
            $row['actor_foto'] = 'data:image/jpeg;base64,' . base64_encode($row['actor_foto']);
        } else {
            $row['actor_foto'] = null; 
        }

        // Lógica de textos
        $tipo = $row['tipo'];
        $texto = "nueva notificación";
        $link = "#";

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
                $texto = "te etiquetó";
                $link = "javascript:cargarVistaPublicacion(" . (int)$row['referencia_id'] . ")";
                break;
            case 'mensaje':
                $texto = "te envió un mensaje";
                $link = "javascript:sessionStorage.setItem('chatUser', '" . htmlspecialchars($row['actor_usuario']) . "'); loadPage('chat');";
                break;
        }

        $row['texto_formato'] = $texto;
        $row['link_accion'] = $link;
        $items[] = $row;
    }
    $stmt->close();

    $response['ok'] = true;
    $items_utf8 = mb_convert_encoding($items, 'UTF-8', 'UTF-8'); // Asegurar UTF8
    $response['items'] = $items_utf8;
    $response['sin_leer'] = $sinLeer;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Limpiamos cualquier echo accidental antes de enviar JSON
ob_clean(); 
echo json_encode($response);
exit;
?>