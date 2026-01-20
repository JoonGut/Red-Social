<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

// 1. Verificar sesión
if (empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo 'no-login';
    exit;
}

$idSeguidor = (int)$_SESSION['id_usuario'];
$idUsuario  = (int)($_POST['id_usuario'] ?? 0);

// 2. Validaciones básicas
if ($idUsuario <= 0 || $idUsuario === $idSeguidor) {
    echo 'error';
    exit;
}

try {
    // 3. Intentamos insertar directamente.
    // Si ya existe (duplicado), saltará automáticamente al bloque 'catch'.
    $stmt = $mysqli->prepare("INSERT INTO seguidores (id_usuario, id_seguidor) VALUES (?, ?)");
    $stmt->bind_param('ii', $idUsuario, $idSeguidor);
    $stmt->execute();

    // 4. Si llegamos aquí, la inserción fue exitosa (es un NUEVO seguidor).
    // Procedemos a crear la notificación.
    $stmtNoti = $mysqli->prepare("INSERT INTO notificaciones (id_usuario, id_actor, tipo, texto_extra, leido, fecha) VALUES (?, ?, 'seguir', 'Te ha empezado a seguir', 0, NOW())");
    
    if ($stmtNoti) {
        $stmtNoti->bind_param('ii', $idUsuario, $idSeguidor);
        $stmtNoti->execute();
        $stmtNoti->close();
    }

    $stmt->close();
    echo 'ok';

} catch (mysqli_sql_exception $e) {
    // 5. Capturamos el error
    // El código 1062 es "Duplicate entry" (Entrada duplicada)
    if ($e->getCode() === 1062) {
        // Significa que ya lo seguías. No pasa nada.
        // Devolvemos 'ok' para que el botón se ponga verde/azul en el frontend.
        echo 'ok';
    } else {
        // Cualquier otro error real de base de datos
        error_log("Error al seguir usuario: " . $e->getMessage());
        echo 'error';
    }
}
?>