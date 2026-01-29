<?php
// chat_get.php
declare(strict_types=1);
ob_start();
// Silenciar errores visuales
error_reporting(0);
ini_set('display_errors', '0');

session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false, 'error' => 'unknown'];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) throw new Exception('no-login');

    $idChat  = (int)($_GET['id_chat'] ?? 0);
    $afterId = (int)($_GET['after_id'] ?? 0);

    if ($idChat <= 0) throw new Exception('bad-chat');

    // Verificar pertenencia
    $stmt = $mysqli->prepare("SELECT 1 FROM pertenece_chat WHERE id_chat = ? AND id_usuario = ? LIMIT 1");
    $stmt->bind_param('ii', $idChat, $yo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) throw new Exception('forbidden');
    $stmt->close();

    // Obtener ticks de lectura
    $ultimoLeidoOtro = 0;
    $stmt = $mysqli->prepare("SELECT MAX(ultimo_leido_id_mensaje) as leido FROM chat_lectura WHERE id_chat = ? AND id_usuario <> ?");
    $stmt->bind_param('ii', $idChat, $yo);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $ultimoLeidoOtro = (int)($row['leido'] ?? 0); 
    }
    $stmt->close();

    // Obtener mensajes
    $sql = "SELECT id_mensaje, texto, id_usuario, creado_en FROM enviar_mensaje WHERE id_chat = ?";
    if ($afterId > 0) {
        $sql .= " AND id_mensaje > ? ORDER BY id_mensaje ASC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $idChat, $afterId);
    } else {
        $sql .= " ORDER BY id_mensaje ASC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $idChat);
    }

    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $response = [
        'ok' => true, 
        'items' => $items, 
        'ultimo_leido_otro' => $ultimoLeidoOtro
    ];

} catch (Exception $e) {
    $response = ['ok' => false, 'error' => $e->getMessage()];
}

ob_clean();
echo json_encode($response);
?>