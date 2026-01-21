<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    echo json_encode(['ok'=>false]); 
    exit;
}

// Obtenemos a la gente que sigues (candidatos para grupo)
// Ajusta 'usuario' o 'nombre' según tus preferencias visuales
$sql = "
    SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil 
    FROM seguidores s
    JOIN usuario u ON u.id_usuario = s.id_usuario
    WHERE s.id_seguidor = ?
    ORDER BY u.usuario ASC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $yo);
$stmt->execute();
$res = $stmt->get_result();

$amigos = [];
while($row = $res->fetch_assoc()){
    $amigos[] = $row;
}
echo json_encode(['ok'=>true, 'items'=>$amigos]);
?>