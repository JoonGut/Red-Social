<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if (empty($_SESSION['id_usuario'])) {
  http_response_code(401);
  echo 'no-login';
  exit;
}

$idSeguidor = (int)$_SESSION['id_usuario'];
$idUsuario  = (int)($_POST['id_usuario'] ?? 0);

if ($idUsuario <= 0 || $idUsuario === $idSeguidor) {
  echo 'error';
  exit;
}

$stmt = $mysqli->prepare("
  SELECT 1 FROM seguidores 
  WHERE id_usuario = ? AND id_seguidor = ?
");
$stmt->bind_param('ii', $idUsuario, $idSeguidor);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
  $stmt = $mysqli->prepare("
    INSERT INTO seguidores (id_usuario, id_seguidor)
    VALUES (?, ?)
  ");
  $stmt->bind_param('ii', $idUsuario, $idSeguidor);
  $stmt->execute();
}

echo 'ok';
