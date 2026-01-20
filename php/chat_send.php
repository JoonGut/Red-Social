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

$idChat = (int)($_POST['id_chat'] ?? 0);
$texto = trim((string)($_POST['texto'] ?? ''));

if ($idChat <= 0 || $texto === '') {
  echo json_encode(['ok' => false, 'error' => 'bad-request']);
  exit;
}

if (mb_strlen($texto) > 250) {
  echo json_encode(['ok' => false, 'error' => 'too-long']);
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

$stmt = $mysqli->prepare("INSERT INTO enviar_mensaje (texto, id_usuario, id_chat) VALUES (?, ?, ?)");
$stmt->bind_param('sii', $texto, $yo, $idChat);
$stmt->execute();
$newId = (int)$stmt->insert_id;
$stmt->close();
$stmtDest = $mysqli->prepare("SELECT id_usuario FROM pertenece_chat WHERE id_chat = ? AND id_usuario != ? LIMIT 1");
$stmtDest->bind_param('ii', $idChat, $yo);
$stmtDest->execute();
$dest = $stmtDest->get_result()->fetch_assoc();

if ($dest) {
    $idDestino = $dest['id_usuario'];
    $resumen = mb_strlen($texto) > 20 ? mb_substr($texto, 0, 20) . '...' : $texto;
    
    $stmtNoti = $mysqli->prepare("INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, texto_extra) VALUES (?, ?, 'mensaje', ?, ?)");
    $stmtNoti->bind_param('iiis', $idDestino, $yo, $idChat, $resumen);
    $stmtNoti->execute();
}
$stmt = $mysqli->prepare("SELECT creado_en FROM enviar_mensaje WHERE id_mensaje = ? LIMIT 1");
$stmt->bind_param('i', $newId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode(['ok' => true, 'id_mensaje' => $newId, 'creado_en' => ($row['creado_en'] ?? null)]);
