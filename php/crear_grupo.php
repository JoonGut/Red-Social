<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

// 1. Verificar sesión
$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'no-login']);
    exit;
}

// 2. Recibir datos (JSON)
// Esperamos: { "nombre": "Nombre Grupo", "miembros": [2, 5, 8] }
$input = json_decode(file_get_contents('php://input'), true);

$nombreGrupo = trim((string)($input['nombre'] ?? ''));
$idsMiembros = $input['miembros'] ?? [];

// 3. Validaciones
if ($nombreGrupo === '') {
    echo json_encode(['ok' => false, 'error' => 'empty-name', 'msg' => 'El grupo debe tener un nombre.']);
    exit;
}

if (!is_array($idsMiembros) || count($idsMiembros) === 0) {
    echo json_encode(['ok' => false, 'error' => 'no-members', 'msg' => 'Selecciona al menos un usuario.']);
    exit;
}

// REQUISITO: Máximo 15 usuarios (Yo + 14 invitados)
if (count($idsMiembros) > 14) {
    echo json_encode(['ok' => false, 'error' => 'too-many', 'msg' => 'Máximo 15 personas por grupo.']);
    exit;
}

// Añadimos mi ID al array para procesarlo en bucle (aseguramos unicidad)
$todosLosMiembros = array_unique(array_merge([$yo], $idsMiembros));

// 4. TRANSACCIÓN (Crear chat, añadir gente y mensaje bienvenida)
$mysqli->begin_transaction();

try {
    // A) Crear el Chat con Nombre
    // El campo 'miembros' será el total de participantes
    $numMiembros = count($todosLosMiembros);
    $stmt = $mysqli->prepare("INSERT INTO chat (miembros, nombre) VALUES (?, ?)");
    $stmt->bind_param('is', $numMiembros, $nombreGrupo);
    $stmt->execute();
    $idChat = (int)$stmt->insert_id;
    $stmt->close();

    // B) Preparar sentencias para insertar en bucle
    $stmtPertenece = $mysqli->prepare("INSERT INTO pertenece_chat (id_chat, id_usuario) VALUES (?, ?)");
    $stmtLectura   = $mysqli->prepare("INSERT INTO chat_lectura (id_chat, id_usuario, ultimo_leido_id_mensaje) VALUES (?, ?, 0)");

    // C) Insertar a CADA miembro
    foreach ($todosLosMiembros as $idUser) {
        $idUser = (int)$idUser;
        if ($idUser <= 0) continue;

        // Tabla pertenece_chat
        $stmtPertenece->bind_param('ii', $idChat, $idUser);
        $stmtPertenece->execute();

        // Tabla chat_lectura (Inicializar en 0)
        $stmtLectura->bind_param('ii', $idChat, $idUser);
        $stmtLectura->execute();
    }
    $stmtPertenece->close();
    $stmtLectura->close();

    // D) Insertar Mensaje de Sistema ("Grupo creado")
    // Lo enviamos como si fuera yo, para que aparezca el primero
    $msgSistema = "Bienvenidos al grupo: " . $nombreGrupo;
    $stmtMsg = $mysqli->prepare("INSERT INTO enviar_mensaje (id_chat, id_usuario, texto) VALUES (?, ?, ?)");
    $stmtMsg->bind_param('iis', $idChat, $yo, $msgSistema);
    $stmtMsg->execute();
    $stmtMsg->close();

    // Confirmar todo
    $mysqli->commit();

    echo json_encode(['ok' => true, 'id_chat' => $idChat]);

} catch (Throwable $e) {
    $mysqli->rollback();
    echo json_encode(['ok' => false, 'error' => 'db_error', 'msg' => $e->getMessage()]);
}
?>