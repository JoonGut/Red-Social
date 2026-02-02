<?php
declare(strict_types=1);
// php/dejar_seguir_usuario.php

session_start();
require __DIR__ . '/db.php';

// Ocultar errores HTML para no romper la respuesta AJAX
ini_set('display_errors', '0'); 

if (empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo 'no-login';
    exit;
}

$idYo      = (int)$_SESSION['id_usuario']; // Yo soy el seguidor
$idDestino = (int)($_POST['id_usuario'] ?? 0); // A quién dejo de seguir

if ($idDestino <= 0) {
    echo 'error_params';
    exit;
}

try {
    // 1. PRIMERO: Ejecutamos el borrado del seguimiento
    // Nota: En tu tabla, id_usuario es el seguido, id_seguidor es quien sigue
    $stmt = $mysqli->prepare("DELETE FROM seguidores WHERE id_usuario = ? AND id_seguidor = ?");
    $stmt->bind_param('ii', $idDestino, $idYo);
    $stmt->execute();
    
    // Guardamos si se borró algo para saber si limpiar notificación
    $seBorro = $stmt->affected_rows > 0;
    $stmt->close();

    // 2. SEGUNDO: Si realmente lo seguía, borramos la notificación de "Seguir" anterior
    // Esto es mejor que enviar una notificación de "Te dejé de seguir"
    if ($seBorro) {
        $stmtNotiDel = $mysqli->prepare("
            DELETE FROM notificaciones 
            WHERE id_usuario = ? AND id_actor = ? AND tipo = 'seguir'
        ");
        $stmtNotiDel->bind_param('ii', $idDestino, $idYo);
        $stmtNotiDel->execute();
        $stmtNotiDel->close();
    }

    echo 'ok';

} catch (Exception $e) {
    echo 'error_sql';
}
?>