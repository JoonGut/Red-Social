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

$stmt = $mysqli->prepare("
  DELETE FROM seguidores
  WHERE id_usuario = ? AND id_seguidor = ?
");
$stmt->bind_param('ii', $idUsuario, $idSeguidor);
$stmt->execute();

echo 'ok';
