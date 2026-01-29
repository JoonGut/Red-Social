<?php
// iniciar_chat.php
ob_start();

declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) throw new Exception('no-login');

    $u = trim((string)($_GET['u'] ?? ''));
    if ($u === '') throw new Exception('bad-user');

    // Buscar al otro usuario
    $stmt = $mysqli->prepare("SELECT id_usuario, usuario, nombre, foto_perfil FROM usuario WHERE usuario = ? LIMIT 1");
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $other = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$other) throw new Exception('not-found');
    $otherId = (int)$other['id_usuario'];
    if ($otherId === $yo) throw new Exception('self');

    // --- CONVERSIÓN FOTO A BASE64 ---
    if (!empty($other['foto_perfil'])) {
        $other['foto_perfil'] = 'data:image/jpeg;base64,' . base64_encode($other['foto_perfil']);
    }

    // Buscar chat existente
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
        ob_clean();
        echo json_encode(['ok' => true, 'id_chat' => (int)$row['id_chat'], 'other' => $other]);
        exit;
    }

    // Crear nuevo chat
    $mysqli->begin_transaction();
    
    $stmt = $mysqli->prepare("INSERT INTO chat (miembros) VALUES (2)");
    $stmt->execute();
    $idChat = (int)$stmt->insert_id;
    $stmt->close();

    $stmt = $mysqli->prepare("INSERT INTO pertenece_chat (id_usuario, id_chat) VALUES (?, ?), (?, ?)");
    $stmt->bind_param('iiii', $yo, $idChat, $otherId, $idChat);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("INSERT INTO chat_lectura (id_chat, id_usuario, ultimo_leido_id_mensaje) VALUES (?, ?, 0), (?, ?, 0)");
    $stmt->bind_param('iiii', $idChat, $yo, $idChat, $otherId);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

    $response = ['ok' => true, 'id_chat' => $idChat, 'other' => $other];

} catch (Throwable $e) {
    if ($mysqli->connect_errno === 0) $mysqli->rollback();
    $response = ['ok' => false, 'error' => $e->getMessage()];
}

ob_clean();
echo json_encode($response);
?>