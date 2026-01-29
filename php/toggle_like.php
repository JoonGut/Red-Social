<?php
declare(strict_types=1);
ob_start(); // Prevenir errores visuales

session_start();
require __DIR__ . '/db.php';

$yo = (int)($_SESSION['id_usuario'] ?? 0);
$idPub = (int)($_POST['id_publicacion'] ?? 0);

if ($yo <= 0 || $idPub <= 0) {
    ob_clean();
    echo "error"; // Respuesta simple para el JS
    exit;
}

try {
    // 1. COMPROBAR SI YA EXISTE EL LIKE
    $stmtCheck = $mysqli->prepare("SELECT id_interaccion FROM interaccion WHERE id_usuario = ? AND id_publicacion = ? AND tipo_interaccion = 'LIKE' LIMIT 1");
    $stmtCheck->bind_param('ii', $yo, $idPub);
    $stmtCheck->execute();
    $res = $stmtCheck->get_result();
    $row = $res->fetch_assoc();
    $stmtCheck->close();

    if ($row) {
        // --- A. SI YA EXISTE: QUITAR LIKE (DISLIKE) ---
        $stmtDel = $mysqli->prepare("DELETE FROM interaccion WHERE id_interaccion = ?");
        $stmtDel->bind_param('i', $row['id_interaccion']);
        $stmtDel->execute();
        $stmtDel->close();

        // Opcional: Eliminar la notificación asociada para que no se quede "huerfana"
        // (Esto es bueno para limpiar la DB)
        $stmtNotiDel = $mysqli->prepare("DELETE FROM notificaciones WHERE id_actor = ? AND referencia_id = ? AND tipo = 'like'");
        $stmtNotiDel->bind_param('ii', $yo, $idPub);
        $stmtNotiDel->execute();
        $stmtNotiDel->close();

        ob_clean();
        echo "ok_removed";

    } else {
        // --- B. NO EXISTE: DAR LIKE ---
        $stmtIns = $mysqli->prepare("INSERT INTO interaccion (id_usuario, id_publicacion, fecha_interaccion, tipo_interaccion) VALUES (?, ?, NOW(), 'LIKE')");
        $stmtIns->bind_param('ii', $yo, $idPub);
        $stmtIns->execute();
        $stmtIns->close();

        // --- C. CREAR NOTIFICACIÓN ---
        // Primero: Averiguar de quién es la publicación
        $stmtOwner = $mysqli->prepare("SELECT id_usuario FROM publicacion WHERE id_publicacion = ? LIMIT 1");
        $stmtOwner->bind_param('i', $idPub);
        $stmtOwner->execute();
        $resOwner = $stmtOwner->get_result();
        
        if ($rowOwner = $resOwner->fetch_assoc()) {
            $idDueno = (int)$rowOwner['id_usuario'];

            // Solo notificar si no soy yo mismo
            if ($idDueno !== $yo && $idDueno > 0) {
                // Insertamos la notificación
                // tipo = 'like', referencia_id = id_publicacion
                $stmtNoti = $mysqli->prepare("
                    INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, leido, creado_en) 
                    VALUES (?, ?, 'like', ?, 0, NOW())
                ");
                $stmtNoti->bind_param('iii', $idDueno, $yo, $idPub);
                $stmtNoti->execute();
                $stmtNoti->close();
            }
        }
        $stmtOwner->close();

        ob_clean();
        echo "ok_added";
    }

} catch (Exception $e) {
    ob_clean();
    echo "error_sql";
}
?>