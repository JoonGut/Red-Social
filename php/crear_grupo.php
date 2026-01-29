<?php
// crear_grupo.php
ob_start();

declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) throw new Exception('no-login');

    $input = json_decode(file_get_contents('php://input'), true);
    $nombreGrupo = trim((string)($input['nombre'] ?? ''));
    $idsMiembros = $input['miembros'] ?? [];

    if ($nombreGrupo === '') throw new Exception('Falta nombre del grupo');
    if (!is_array($idsMiembros) || count($idsMiembros) === 0) throw new Exception('Selecciona miembros');
    if (count($idsMiembros) > 14) throw new Exception('Demasiados miembros');

    $todosLosMiembros = array_unique(array_merge([$yo], $idsMiembros));

    $mysqli->begin_transaction();

    // Crear chat
    $numMiembros = count($todosLosMiembros);
    $stmt = $mysqli->prepare("INSERT INTO chat (miembros, nombre) VALUES (?, ?)");
    $stmt->bind_param('is', $numMiembros, $nombreGrupo);
    $stmt->execute();
    $idChat = (int)$stmt->insert_id;
    $stmt->close();

    // Añadir miembros
    $stmtPertenece = $mysqli->prepare("INSERT INTO pertenece_chat (id_chat, id_usuario) VALUES (?, ?)");
    $stmtLectura   = $mysqli->prepare("INSERT INTO chat_lectura (id_chat, id_usuario, ultimo_leido_id_mensaje) VALUES (?, ?, 0)");

    foreach ($todosLosMiembros as $idUser) {
        $idUser = (int)$idUser;
        if ($idUser <= 0) continue;
        $stmtPertenece->bind_param('ii', $idChat, $idUser);
        $stmtPertenece->execute();
        $stmtLectura->bind_param('ii', $idChat, $idUser);
        $stmtLectura->execute();
    }
    $stmtPertenece->close();
    $stmtLectura->close();

    // Mensaje sistema
    $msgSistema = "Bienvenidos al grupo: " . $nombreGrupo;
    $stmtMsg = $mysqli->prepare("INSERT INTO enviar_mensaje (id_chat, id_usuario, texto) VALUES (?, ?, ?)");
    $stmtMsg->bind_param('iis', $idChat, $yo, $msgSistema);
    $stmtMsg->execute();
    $stmtMsg->close();

    $mysqli->commit();
    $response = ['ok' => true, 'id_chat' => $idChat];

} catch (Throwable $e) {
    if ($mysqli->connect_errno === 0) $mysqli->rollback();
    $response = ['ok' => false, 'error' => $e->getMessage()];
}

ob_clean();
echo json_encode($response);
?>