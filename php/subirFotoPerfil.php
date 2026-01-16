<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['id_usuario'])) {
  http_response_code(401);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  exit;
}

$idUsuario = (int)$_SESSION['id_usuario'];
$archivo = $_FILES['foto_perfil'];

$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $permitidas, true)) {
  http_response_code(400);
  exit;
}

$max = 3 * 1024 * 1024;
if ($archivo['size'] > $max) {
  http_response_code(400);
  exit;
}

$rutaMultimedia = dirname(__DIR__) . '/multimedia';
if (!is_dir($rutaMultimedia)) {
  mkdir($rutaMultimedia, 0777, true);
}

$fotoAnterior = '';
$stmt = $mysqli->prepare('SELECT foto_perfil FROM usuario WHERE id_usuario = ? LIMIT 1');
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row && !empty($row['foto_perfil'])) {
  $fotoAnterior = (string)$row['foto_perfil'];
}

$nombreNuevo = 'pf_' . $idUsuario . '_' . time() . '.' . $ext;
$destino = $rutaMultimedia . '/' . $nombreNuevo;

if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
  http_response_code(500);
  exit;
}

$stmt = $mysqli->prepare('UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?');
$stmt->bind_param('si', $nombreNuevo, $idUsuario);

if (!$stmt->execute()) {
  $stmt->close();

  @unlink($destino);

  http_response_code(500);
  exit;
}
$stmt->close();

if ($fotoAnterior !== '' && $fotoAnterior !== $nombreNuevo) {
  $fotoAnterior = basename($fotoAnterior);

  $rutaAnterior = $rutaMultimedia . '/' . $fotoAnterior;
  if (is_file($rutaAnterior)) {
    @unlink($rutaAnterior);
  }
}

$_SESSION['foto_perfil'] = $nombreNuevo;

http_response_code(204);
exit;
