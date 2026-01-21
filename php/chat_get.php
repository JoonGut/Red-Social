<?php
declare(strict_types=1);
// 1. SILENCIAR ERRORES VISUALES (Para que no rompan el JSON)
error_reporting(0);
ini_set('display_errors', '0');

session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'no-login']);
    exit;
}

$idChat  = (int)($_GET['id_chat'] ?? 0);
$afterId = (int)($_GET['after_id'] ?? 0);

if ($idChat <= 0) {
    echo json_encode(['ok' => false, 'error' => 'bad-chat']);
    exit;
}

// 2. VERIFICAR PERTENENCIA AL CHAT
$stmt = $mysqli->prepare("SELECT 1 FROM pertenece_chat WHERE id_chat = ? AND id_usuario = ? LIMIT 1");
$stmt->bind_param('ii', $idChat, $yo);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}
$stmt->close();

// 3. OBTENER INFORMACIÓN DE LECTURA (Ticks azules)
// Usamos "AS leido" para darle un nombre fácil de encontrar
$ultimoLeidoOtro = 0;
$stmt = $mysqli->prepare("
    SELECT MAX(ultimo_leido_id_mensaje) as leido
    FROM chat_lectura 
    WHERE id_chat = ? AND id_usuario <> ? 
");
$stmt->bind_param('ii', $idChat, $yo);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    // AQUÍ ESTABA EL ERROR: Usamos 'leido' que coincide con el SQL
    $ultimoLeidoOtro = (int)($row['leido'] ?? 0); 
}
$stmt->close();

// 4. OBTENER MENSAJES
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

echo json_encode([
    'ok' => true, 
    'items' => $items, 
    'ultimo_leido_otro' => $ultimoLeidoOtro
]);
?>