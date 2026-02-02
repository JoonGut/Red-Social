<?php
// Evita cualquier espacio en blanco antes de la etiqueta <?php
$host = '18.208.57.228'; // Cambia a 127.0.0.1 si estás en local
$db   = 'bd_social';
$user = 'usuario';
$pass = 'usuario';
$port = 3306;

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