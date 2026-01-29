<?php
declare(strict_types=1);
ob_start();
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) throw new Exception('no-login');

    $idChat = (int)($_POST['id_chat'] ?? 0);
    $ultimo = (int)($_POST['ultimo_id'] ?? 0);

    if ($idChat > 0 && $ultimo >= 0) {
        $sql = "
        INSERT INTO chat_lectura (id_chat, id_usuario, ultimo_leido_id_mensaje)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE ultimo_leido_id_mensaje = GREATEST(ultimo_leido_id_mensaje, VALUES(ultimo_leido_id_mensaje))
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iii', $idChat, $yo, $ultimo);
        $stmt->execute();
        $stmt->close();
        
        $response = ['ok' => true];
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
?>