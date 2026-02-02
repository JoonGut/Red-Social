<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

// Limpieza total del búfer para evitar el error de "Unexpected end of JSON"
if (ob_get_length()) ob_clean();

$idPub = (int)($_GET['id_publicacion'] ?? 0);

if ($idPub <= 0) {
    echo json_encode(['ok' => false, 'items' => []]);
    exit;
}

try {
    // IMPORTANTE: Seleccionamos id_padre para que el JS sepa quién es hijo de quién
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
    $res = $stmt->get_result();
    
    $items = [];
    while ($row = $res->fetch_assoc()) {
        // Convertimos el BLOB de la foto a Base64 si existe
        if (!empty($row['foto_perfil'])) {
            $row['foto_perfil'] = base64_encode($row['foto_perfil']);
        }
        // Aseguramos que id_padre sea un número o null para el JS
        $row['id_padre'] = $row['id_padre'] ? (int)$row['id_padre'] : null;
        $items[] = $row;
    }

    echo json_encode(['ok' => true, 'items' => $items]);

} catch (Exception $e) {
    // Si hay error, enviamos un JSON con el error, NO texto plano
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
exit;