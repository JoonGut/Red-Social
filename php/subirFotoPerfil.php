<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['id_usuario'])) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: perfil.php');
  exit;
}

if (!isset($_FILES['foto_perfil'])) {
  $_SESSION['error_perfil'] = 'No llegó ningún archivo.';
  header('Location: perfil.php');
  exit;
}

if ($_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
  $_SESSION['error_perfil'] = 'Error al subir archivo (code: ' . $_FILES['foto_perfil']['error'] . ').';
  header('Location: perfil.php');
  exit;
}

$archivo = $_FILES['foto_perfil'];

/* Validar extensión */
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $permitidas, true)) {
  $_SESSION['error_perfil'] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
  header('Location: perfil.php');
  exit;
}

/* Validar tamaño (opcional): 3MB */
$max = 3 * 1024 * 1024;
if ($archivo['size'] > $max) {
  $_SESSION['error_perfil'] = 'La imagen es demasiado grande (máx 3MB).';
  header('Location: perfil.php');
  exit;
}

/* Carpeta DESTINO: /multimedia (tu carpeta real) */
$rutaMultimedia = dirname(__DIR__) . '/multimedia';
if (!is_dir($rutaMultimedia)) {
  mkdir($rutaMultimedia, 0777, true);
}

/* Nombre único */
$nombreNuevo = 'pf_' . (int)$_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
$destino = $rutaMultimedia . '/' . $nombreNuevo;

/* Mover archivo */
if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
  $_SESSION['error_perfil'] = 'No se pudo mover el archivo a multimedia.';
  header('Location: perfil.php');
  exit;
}

/* Guardar en BD */
$idUsuario = (int)$_SESSION['id_usuario'];

$stmt = $mysqli->prepare('UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?');
$stmt->bind_param('si', $nombreNuevo, $idUsuario);

if (!$stmt->execute()) {
  $_SESSION['error_perfil'] = 'No se pudo guardar en la BD.';
  $stmt->close();
  header('Location: perfil.php');
  exit;
}

$stmt->close();

/* Guardar en sesión */
$_SESSION['foto_perfil'] = $nombreNuevo;

$_SESSION['ok_perfil'] = 'Foto actualizada.';
header('Location: perfil.php');
exit;
