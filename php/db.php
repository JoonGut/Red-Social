<?php
// Evita cualquier espacio en blanco antes de la etiqueta <?php
$host = 'YOUR_IP'; // Cambia a 127.0.0.1 si estás en local
$db   = 'YOUR BD_NAME';
$user = 'YOUR_USER';
$pass = 'YOUR_PASSWROD';
$port = 'YOUR BD_PORT';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    $mysqli->set_charset('utf8mb4');
} catch (Exception $e) {
    // Si hay error, enviamos un JSON válido en lugar de un texto plano (die)
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}
