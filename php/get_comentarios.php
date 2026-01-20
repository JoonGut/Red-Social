<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$idPub = (int)($_GET['id_publicacion'] ?? 0);

if ($idPub <= 0) {
    echo json_encode(['ok' => false, 'items' => []]);
    exit;
}

$sql = "
    SELECT 
        c.id_comentario, c.texto, c.creado_en, c.id_padre,
        u.usuario, u.nombre, u.foto_perfil
    FROM comentarios c
    JOIN usuario u ON u.id_usuario = c.id_usuario
    WHERE c.id_publicacion = ?
    ORDER BY c.creado_en ASC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $idPub);
$stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['ok' => true, 'items' => $res]);