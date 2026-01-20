<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$idPub = (int)($_GET['id_publicacion'] ?? 0);

if ($idPub <= 0) {
    echo json_encode(['ok' => false, 'items' => []]);
    exit;
}

// CAMBIOS IMPORTANTES AQUÍ:
// 1. Buscamos en la tabla 'interaccion' en lugar de 'comentarios'.
// 2. Filtramos por tipo_interaccion = 'COMENTARIO'.
// 3. Usamos 'AS' para que el JSON devuelva nombres compatibles con tu JS actual
//    (ej: devolvemos 'texto' aunque en la base de datos se llame 'comentario').

$sql = "
    SELECT 
        i.id_interaccion AS id_comentario, 
        i.comentario AS texto, 
        i.fecha_creacion AS creado_en, 
        i.id_padre,
        u.usuario, 
        u.nombre, 
        u.foto_perfil
    FROM interaccion i
    JOIN usuario u ON u.id_usuario = i.id_usuario
    WHERE i.id_publicacion = ? 
      AND i.tipo_interaccion = 'COMENTARIO'
    ORDER BY i.fecha_creacion ASC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $idPub);
$stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['ok' => true, 'items' => $res]);
?>