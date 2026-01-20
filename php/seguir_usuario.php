<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

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
    $stmt = $mysqli->prepare("INSERT INTO seguidores (id_usuario, id_seguidor) VALUES (?, ?)");
    $stmt->bind_param('ii', $idDestino, $idYo);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        $stmtNoti = $mysqli->prepare("INSERT INTO notificaciones (id_usuario, id_actor, tipo, texto_extra) VALUES (?, ?, 'seguir', 'Te ha empezado a seguir')");
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
    if ($e->getCode() === 1062) {
        echo 'ok'; 
    } else {
        error_log("Error SQL Seguir: " . $e->getMessage());
        echo 'error_db';
    }
}
?>