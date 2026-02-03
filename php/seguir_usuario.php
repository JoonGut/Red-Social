<?php
declare(strict_types=1);
session_start();

// 1. ACTIVAR CHIVATOS DE ERROR (SOLO PARA PRUEBAS)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/db.php';

// 2. VERIFICAR CONEXIÓN
if (!isset($mysqli) || $mysqli->connect_errno) {
    die("ERROR_DB_CONEXION: " . ($mysqli->connect_error ?? 'No definida'));
}

// 3. VERIFICAR SESIÓN
if (empty($_SESSION['id_usuario'])) {
    die("ERROR_SESSION: No hay usuario logueado. Session ID: " . session_id());
}

$idYo      = (int)$_SESSION['id_usuario'];
$idDestino = (int)($_POST['id_usuario'] ?? 0);

// 4. VERIFICAR DATOS ENTRANTES
if ($idDestino <= 0) {
    // Esto nos dirá qué está llegando realmente
    die("ERROR_PARAMS: id_usuario recibido es 0 o nulo. POST RAW: " . print_r($_POST, true));
}

if ($idDestino === $idYo) {
    die("ERROR_AUTO: No te puedes seguir a ti mismo.");
}

try {
    // 5. INTENTAR LA CONSULTA Y MOSTRAR ERROR SQL SI FALLA
$sql = "INSERT INTO seguidores (id_usuario, id_seguidor) VALUES (?, ?)";    
$stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        die("ERROR_PREPARE: " . $mysqli->error);
    }

    $stmt->bind_param('ii', $idDestino, $idYo);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Insertar notificación (Opcional, si falla esto no debería romper el seguir)
        $sqlNoti = "INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, leido, creado_en) VALUES (?, ?, 'seguir', 0, 0, NOW())";
        $stmtNoti = $mysqli->prepare($sqlNoti);
        if ($stmtNoti) {
            $stmtNoti->bind_param('ii', $idDestino, $idYo);
            $stmtNoti->execute();
            $stmtNoti->close();
        } else {
            // Si falla la notificación, lo registramos pero devolvemos OK al seguir
            error_log("Fallo al preparar notificación: " . $mysqli->error);
        }

        echo 'ok'; 
    } else {
        // AQUÍ ESTÁ LA CLAVE: Mostrar por qué falló el execute
        die("ERROR_EXECUTE: " . $stmt->error);
    }

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo 'ok'; // Ya lo seguías, todo bien
    } else {
        die("EXCEPTION_SQL: " . $e->getMessage());
    }
} catch (Exception $e) {
    die("EXCEPTION_GENERAL: " . $e->getMessage());
}
?>