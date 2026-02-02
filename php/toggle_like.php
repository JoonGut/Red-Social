<?php
declare(strict_types=1);
// php/toggle_like.php

ob_start(); 
session_start();
require __DIR__ . '/db.php';

$yo = (int)($_SESSION['id_usuario'] ?? 0);
$idPub = (int)($_POST['id_publicacion'] ?? 0);

if ($yo <= 0 || $idPub <= 0) { ob_clean(); echo "error"; exit; }

try {
    // 1. Verificar si ya existe el like
    $stmtCheck = $mysqli->prepare("SELECT id_interaccion FROM interaccion WHERE id_usuario = ? AND id_publicacion = ? AND tipo_interaccion = 'LIKE' LIMIT 1");
    $stmtCheck->bind_param('ii', $yo, $idPub);
    $stmtCheck->execute();
    $row = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($row) {
        // --- A. QUITAR LIKE ---
        $mysqli->query("DELETE FROM interaccion WHERE id_interaccion = " . (int)$row['id_interaccion']);
        
        // Borrar notificación
        $stmtDel = $mysqli->prepare("DELETE FROM notificaciones WHERE id_actor = ? AND referencia_id = ? AND tipo = 'like'");
        $stmtDel->bind_param('ii', $yo, $idPub);
        $stmtDel->execute();
        $stmtDel->close();

        ob_clean(); echo "ok_removed";

    } else {
        // --- B. DAR LIKE (Corregido: fecha_creacion) ---
        $stmtIns = $mysqli->prepare("INSERT INTO interaccion (id_usuario, id_publicacion, fecha_creacion, tipo_interaccion) VALUES (?, ?, NOW(), 'LIKE')");
        $stmtIns->bind_param('ii', $yo, $idPub);
        $stmtIns->execute();
        $stmtIns->close();

        // ---------------------------------------------------------
        // NOTIFICACIÓN
        // ---------------------------------------------------------
        $stmtOwn = $mysqli->prepare("SELECT id_usuario FROM publicacion WHERE id_publicacion = ? LIMIT 1");
        $stmtOwn->bind_param('i', $idPub);
        $stmtOwn->execute();
        $resOwn = $stmtOwn->get_result();

        if ($rowOwner = $resOwn->fetch_assoc()) {
            $idDueno = (int)$rowOwner['id_usuario'];

            if ($idDueno !== $yo && $idDueno > 0) {
                // Notificación robusta (leido=0, creado_en=NOW())
                $sqlNoti = "INSERT INTO notificaciones (id_usuario, id_actor, tipo, leido, referencia_id, creado_en) 
                            VALUES (?, ?, 'like', 0, ?, NOW())";
                
                $stmtNoti = $mysqli->prepare($sqlNoti);
                $stmtNoti->bind_param('iii', $idDueno, $yo, $idPub);
                $stmtNoti->execute();
                $stmtNoti->close();
            }
        }
        $stmtOwn->close();

        ob_clean(); echo "ok_added";
    }

} catch (Exception $e) {
    ob_clean(); echo "error_sql";
}
?>