<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'No autenticado']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
  exit;
}

$nombre = trim((string)($_POST['nombre'] ?? ''));
$bio    = trim((string)($_POST['bio'] ?? ''));

if ($nombre === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'El nombre no puede estar vacío.']);
  exit;
}

$nombre = mb_substr($nombre, 0, 60);
$bio    = mb_substr($bio, 0, 240);

$idUsuario = (int)$_SESSION['id_usuario'];

$stmt = $mysqli->prepare('UPDATE usuario SET nombre = ?, biografia = ? WHERE id_usuario = ?');

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error preparando la consulta.']);
  exit;
}

$stmt->bind_param('ssi', $nombre, $bio, $idUsuario);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el perfil.']);
  $stmt->close();
  exit;
}

$stmt->close();

$_SESSION['nombre'] = $nombre;
$_SESSION['biografia'] = $bio;

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
exit;
