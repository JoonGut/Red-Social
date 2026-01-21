<?php
// php/toggle_like.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

$miId = $_SESSION['id_usuario'] ?? 0;
$idPub = $_POST['id_publicacion'] ?? 0;

if(!$miId || !$idPub) exit;

// Verificar si ya existe
$check = $mysqli->query("SELECT id_interaccion FROM interaccion WHERE id_usuario=$miId AND id_publicacion=$idPub AND tipo_interaccion='LIKE'");

if($check->num_rows > 0) {
    // Quitar Like
    $mysqli->query("DELETE FROM interaccion WHERE id_usuario=$miId AND id_publicacion=$idPub AND tipo_interaccion='LIKE'");
    echo "removed";
} else {
    // Poner Like
    $mysqli->query("INSERT INTO interaccion (id_usuario, id_publicacion, tipo_interaccion) VALUES ($miId, $idPub, 'LIKE')");
    echo "added";
}
?>