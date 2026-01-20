<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'no-login']);
  exit;
}

$idChat = (int)($_GET['id_chat'] ?? 0);
$afterId = (int)($_GET['after_id'] ?? 0);

if ($idChat <= 0) {
  echo json_encode(['ok' => false, 'error' => 'bad-chat']);
  exit;
}

// 1. Verificar pertenencia
$stmt = $mysqli->prepare("SELECT 1 FROM pertenece_chat WHERE id_chat = ? AND id_usuario = ? LIMIT 1");
$stmt->bind_param('ii', $idChat, $yo);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
  $stmt->close();
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'forbidden']);
  exit;
}
$stmt->close();

// 2. NUEVO: Obtener hasta qué mensaje ha leído la OTRA persona
// Buscamos en chat_lectura alguien que NO sea yo en este chat
$ultimoLeidoOtro = 0;
$stmt = $mysqli->prepare("
    SELECT ultimo_leido_id_mensaje 
    FROM chat_lectura 
    WHERE id_chat = ? AND id_usuario <> ? 
    LIMIT 1
");
$stmt->bind_param('ii', $idChat, $yo);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $ultimoLeidoOtro = (int)$row['ultimo_leido_id_mensaje'];
}
$stmt->close();

// 3. Obtener mensajes
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

// Devolvemos los mensajes Y el dato de lectura
echo json_encode([
    'ok' => true, 
    'items' => $items, 
    'ultimo_leido_otro' => $ultimoLeidoOtro
]);
?>