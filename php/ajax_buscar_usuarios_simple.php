<?php
require 'db.php';
$q = $_GET['q'] ?? '';
if(!$q) exit(json_encode([]));

$stmt = $mysqli->prepare("SELECT usuario, foto_perfil FROM usuario WHERE usuario LIKE ? LIMIT 5");
$param = $q . '%';
$stmt->bind_param('s', $param);
$stmt->execute();
$res = $stmt->get_result();
$users = [];
while($row = $res->fetch_assoc()) {
    $users[] = ['usuario' => $row['usuario'], 'foto' => $row['foto_perfil']];
}
echo json_encode($users);
?>