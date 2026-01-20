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

$idPub = (int)($_POST['id_publicacion'] ?? 0);
$texto = trim((string)($_POST['texto'] ?? ''));
$idPadre = !empty($_POST['id_padre']) ? (int)$_POST['id_padre'] : null;

if ($idPub <= 0 || $texto === '') {
    echo json_encode(['ok' => false, 'error' => 'vacío']);
    exit;
}

// 1. Insertar el comentario
$stmt = $mysqli->prepare("INSERT INTO comentarios (id_publicacion, id_usuario, texto, id_padre) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iisi', $idPub, $yo, $texto, $idPadre);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

// 2. Obtener datos para devolver al JS (nombre, foto, fecha)
$stmt = $mysqli->prepare("SELECT usuario, nombre, foto_perfil FROM usuario WHERE id_usuario = ?");
$stmt->bind_param('i', $yo);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. GENERAR NOTIFICACIÓN (Si no es mi propia publicación/comentario)
// Primero averiguamos de quién es la publicación o el comentario padre
$idDestino = 0;
$tipoNoti = 'comentario';

if ($idPadre) {
    // Si es respuesta, notificamos al dueño del comentario original
    $q = $mysqli->prepare("SELECT id_usuario FROM comentarios WHERE id_comentario = ?");
    $q->bind_param('i', $idPadre);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    if ($res) $idDestino = (int)$res['id_usuario'];
    $q->close();
} else {
    // Si es comentario normal, notificamos al dueño del post
    $q = $mysqli->prepare("SELECT id_usuario FROM publicacion WHERE id_publicacion = ?");
    $q->bind_param('i', $idPub);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    if ($res) $idDestino = (int)$res['id_usuario'];
    $q->close();
}

// Solo notificar si no soy yo mismo
if ($idDestino > 0 && $idDestino !== $yo) {
    $resumen = mb_strlen($texto) > 30 ? mb_substr($texto, 0, 30) . '...' : $texto;
    $stmtN = $mysqli->prepare("INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, texto_extra) VALUES (?, ?, ?, ?, ?)");
    $stmtN->bind_param('iiisi', $idDestino, $yo, $tipoNoti, $idPub, $resumen);
    $stmtN->execute();
}

echo json_encode([
    'ok' => true,
    'id_comentario' => $newId,
    'usuario' => $userData,
    'texto' => $texto,
    'creado_en' => date('Y-m-d H:i:s')
]);