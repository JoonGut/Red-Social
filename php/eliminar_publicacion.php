<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* 1️⃣ Comprobar sesión */
if (!isset($_SESSION['id_usuario'])) {
  http_response_code(401);
  echo 'No autenticado';
  exit;
}

/* 2️⃣ Comprobar ID recibido */
if (!isset($_POST['id'])) {
  http_response_code(400);
  echo 'ID no recibido';
  exit;
}

$idPublicacion = (int) $_POST['id'];
$idUsuario     = (int) $_SESSION['id_usuario'];

/* 3️⃣ Comprobar que la publicación pertenece al usuario */
$stmt = $mysqli->prepare("
  SELECT id_usuario
  FROM publicacion
  WHERE id_publicacion = ?
  LIMIT 1
");
$stmt->bind_param("i", $idPublicacion);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo 'Publicación no encontrada';
  exit;
}

$row = $result->fetch_assoc();

if ((int)$row['id_usuario'] !== $idUsuario) {
  http_response_code(403);
  echo 'No tienes permiso para eliminar esta publicación';
  exit;
}

/* 4️⃣ Borrar la publicación */
$stmt = $mysqli->prepare("
  DELETE FROM publicacion
  WHERE id_publicacion = ?
");
$stmt->bind_param("i", $idPublicacion);
$stmt->execute();

echo 'ok';

