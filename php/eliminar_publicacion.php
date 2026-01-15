<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if (!isset($_POST['id'])) {
  http_response_code(400);
  echo 'ID no recibido';
  exit;
}

$id = (int)$_POST['id'];



$stmt = $mysqli->prepare("DELETE FROM publicacion WHERE id_publicacion = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo 'ok';
