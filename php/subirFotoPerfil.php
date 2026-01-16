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

/* Validar extensión */
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $permitidas, true)) {
  http_response_code(400);
  exit;
}

/* Validar tamaño: 3MB */
$max = 3 * 1024 * 1024;
if ($archivo['size'] > $max) {
  http_response_code(400);
  exit;
}

/* Carpeta destino */
$rutaMultimedia = dirname(__DIR__) . '/multimedia';
if (!is_dir($rutaMultimedia)) {
  mkdir($rutaMultimedia, 0777, true);
}

/* 1) Obtener foto anterior desde BD */
$fotoAnterior = '';
$stmt = $mysqli->prepare('SELECT foto_perfil FROM usuario WHERE id_usuario = ? LIMIT 1');
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row && !empty($row['foto_perfil'])) {
  $fotoAnterior = (string)$row['foto_perfil'];
}

/* 2) Generar nombre nuevo y mover */
$nombreNuevo = 'pf_' . $idUsuario . '_' . time() . '.' . $ext;
$destino = $rutaMultimedia . '/' . $nombreNuevo;

if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
  http_response_code(500);
  exit;
}

/* 3) Actualizar BD con la nueva foto */
$stmt = $mysqli->prepare('UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?');
$stmt->bind_param('si', $nombreNuevo, $idUsuario);

if (!$stmt->execute()) {
  $stmt->close();

  // rollback: borra la nueva si no se pudo guardar en BD
  @unlink($destino);

  http_response_code(500);
  exit;
}
$stmt->close();

/* 4) Borrar archivo anterior (si existe y no es igual al nuevo) */
if ($fotoAnterior !== '' && $fotoAnterior !== $nombreNuevo) {
  // Seguridad: evita rutas raras
  $fotoAnterior = basename($fotoAnterior);

  $rutaAnterior = $rutaMultimedia . '/' . $fotoAnterior;
  if (is_file($rutaAnterior)) {
    @unlink($rutaAnterior);
  }
}

/* 5) Actualizar sesión */
$_SESSION['foto_perfil'] = $nombreNuevo;

/* 6) No devolver nada */
http_response_code(204);
exit;
