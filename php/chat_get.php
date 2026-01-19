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

$stmt = $mysqli->prepare("SELECT 1 FROM pertenece_chat WHERE id_chat = ? AND id_usuario = ? LIMIT 1");
$stmt->bind_param('ii', $idChat, $yo);
$stmt->execute();
$ok = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$ok) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'forbidden']);
  exit;
}

if ($afterId > 0) {
  $sql = "
    SELECT id_mensaje, texto, id_usuario, creado_en
    FROM enviar_mensaje
    WHERE id_chat = ? AND id_mensaje > ?
    ORDER BY id_mensaje ASC
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ii', $idChat, $afterId);
} else {
  $sql = "
    SELECT id_mensaje, texto, id_usuario, creado_en
    FROM enviar_mensaje
    WHERE id_chat = ?
    ORDER BY id_mensaje ASC
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $idChat);
}

$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['ok' => true, 'items' => $items]);
