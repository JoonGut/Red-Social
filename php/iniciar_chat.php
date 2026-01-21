<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    echo json_encode(['ok' => false, 'error' => 'no-login']);
    exit;
}

$u = trim((string)($_GET['u'] ?? ''));

// 1. Buscar al otro usuario
$stmt = $mysqli->prepare("SELECT id_usuario, usuario, nombre, foto_perfil FROM usuario WHERE usuario = ? LIMIT 1");
$stmt->bind_param('s', $u);
$stmt->execute();
$other = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$other || $other['id_usuario'] === $yo) {
    echo json_encode(['ok' => false, 'error' => 'invalid-user']);
    exit;
}
$otherId = (int)$other['id_usuario'];

// 2. BUSCAR SI YA EXISTE (Versión Robusta)
// Buscamos un chat que NO sea grupo (nombre IS NULL) y donde estemos LOS DOS.
$sqlFind = "
    SELECT c.id_chat
    FROM chat c
    JOIN pertenece_chat p1 ON p1.id_chat = c.id_chat
    JOIN pertenece_chat p2 ON p2.id_chat = c.id_chat
    WHERE p1.id_usuario = ? 
      AND p2.id_usuario = ? 
      AND (c.nombre IS NULL OR c.nombre = '')
    LIMIT 1
";
$stmt = $mysqli->prepare($sqlFind);
$stmt->bind_param('ii', $yo, $otherId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    echo json_encode(['ok' => true, 'id_chat' => (int)$row['id_chat'], 'other' => $other]);
    exit;
}

// 3. CREAR NUEVO SI NO EXISTE
$mysqli->begin_transaction();
try {
    // Insertamos chat (miembros=2)
    $stmt = $mysqli->prepare("INSERT INTO chat (miembros, nombre) VALUES (2, NULL)");
    $stmt->execute();
    $idChat = (int)$stmt->insert_id;
    $stmt->close();

    // Insertamos relación
    $stmt = $mysqli->prepare("INSERT INTO pertenece_chat (id_usuario, id_chat) VALUES (?, ?), (?, ?)");
    $stmt->bind_param('iiii', $yo, $idChat, $otherId, $idChat);
    $stmt->execute();
    $stmt->close();

    // Insertamos lectura inicial
    $stmt = $mysqli->prepare("INSERT INTO chat_lectura (id_chat, id_usuario, ultimo_leido_id_mensaje) VALUES (?, ?, 0), (?, ?, 0)");
    $stmt->bind_param('iiii', $idChat, $yo, $idChat, $otherId);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
    echo json_encode(['ok' => true, 'id_chat' => $idChat, 'other' => $other]);

} catch (Throwable $e) {
    $mysqli->rollback();
    echo json_encode(['ok' => false, 'error' => 'db', 'msg' => $e->getMessage()]);
}
?>