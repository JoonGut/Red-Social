<?php
declare(strict_types=1);
// php/seguir_usuario.php

session_start();
require __DIR__ . '/db.php';

// Ocultar errores HTML para no ensuciar la respuesta AJAX
ini_set('display_errors', '0');
error_reporting(E_ALL);

if (empty($_SESSION['id_usuario'])) {
    echo 'no-login';
    exit;
}

$idYo      = (int)$_SESSION['id_usuario']; 
$idDestino = (int)($_POST['id_usuario'] ?? 0); 

if ($idDestino <= 0 || $idDestino === $idYo) {
    echo 'error_params';
    exit;
}

try {
    // 1. Insertar el seguimiento
    // Asegúrate de que las columnas coincidan con tu tabla (id_usuario = seguido, id_seguidor = quien sigue)
    $stmt = $mysqli->prepare("INSERT INTO seguidores (id_usuario, id_seguidor, fecha_seguimiento) VALUES (?, ?, NOW())");
    
    // Si tu tabla no tiene columna 'fecha_seguimiento', borra ", fecha_seguimiento" y ", NOW()"
    // Pero para la notificación SIEMPRE usaremos NOW()
    
    $stmt->bind_param('ii', $idDestino, $idYo);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // 2. Insertar la NOTIFICACIÓN (Corregido)
        // Añadimos: referencia_id (0), leido (0) y creado_en (NOW())
        $stmtNoti = $mysqli->prepare("
            INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, leido, creado_en) 
            VALUES (?, ?, 'seguir', 0, 0, NOW())
        ");
        
        if ($stmtNoti) {
            $stmtNoti->bind_param('ii', $idDestino, $idYo);
            $stmtNoti->execute();
            $stmtNoti->close();
        }

        echo 'ok'; 
    } else {
        echo 'error_sql';
    }

} catch (mysqli_sql_exception $e) {
    // Código 1062 = Duplicate entry (Ya lo seguías)
    if ($e->getCode() === 1062) {
        echo 'ok'; 
    } else {
        error_log("Error SQL Seguir: " . $e->getMessage());
        echo 'error_db';
    }
}
?>