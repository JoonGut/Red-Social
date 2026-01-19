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

$u = trim((string)($_GET['u'] ?? ''));
if ($u === '' || !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $u)) {
    echo json_encode(['ok' => false, 'error' => 'bad-user']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id_usuario, usuario, nombre, foto_perfil FROM usuario WHERE usuario = ? LIMIT 1");
$stmt->bind_param('s', $u);
$stmt->execute();
$other = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$other) {
    echo json_encode(['ok' => false, 'error' => 'not-found']);
    exit;
}

$otherId = (int)$other['id_usuario'];

if ($otherId === $yo) {
    echo json_encode(['ok' => false, 'error' => 'self']);
    exit;
}

$sqlFind = "
SELECT c.id_chat
FROM chat c
JOIN pertenece_chat p1 ON p1.id_chat = c.id_chat AND p1.id_usuario = ?
JOIN pertenece_chat p2 ON p2.id_chat = c.id_chat AND p2.id_usuario = ?
WHERE c.miembros = 2
LIMIT 1
";
$stmt = $mysqli->prepare($sqlFind);
$stmt->bind_param('ii', $yo, $otherId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    echo json_encode([
        'ok' => true,
        'id_chat' => (int)$row['id_chat'],
        'other' => $other
    ]);
    exit;
}

$mysqli->begin_transaction();

try {
    $stmt = $mysqli->prepare("INSERT INTO chat (miembros) VALUES (2)");
    $stmt->execute();
    $idChat = (int)$stmt->insert_id;
    $stmt->close();

    $stmt = $mysqli->prepare("INSERT INTO pertenece_chat (id_usuario, id_chat) VALUES (?, ?), (?, ?)");
    $stmt->bind_param('iiii', $yo, $idChat, $otherId, $idChat);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("
        INSERT INTO chat_lectura (id_chat, id_usuario, ultimo_leido_id_mensaje)
        VALUES (?, ?, 0), (?, ?, 0)
        ON DUPLICATE KEY UPDATE ultimo_leido_id_mensaje = ultimo_leido_id_mensaje
    ");
    $stmt->bind_param('iiii', $idChat, $yo, $idChat, $otherId);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

    echo json_encode([
        'ok' => true,
        'id_chat' => $idChat,
        'other' => $other
    ]);

} catch (Throwable $e) {
    $mysqli->rollback();
    echo json_encode(['ok' => false, 'error' => 'db', 'msg' => $e->getMessage()]);
}