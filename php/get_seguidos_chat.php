<?php
// get_seguidos_chat.php
ob_start();

declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$items = [];
$ok = false;

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo > 0) {
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

        while($row = $res->fetch_assoc()){
            // --- CONVERSIÓN BLOB A BASE64 ---
            if (!empty($row['foto_perfil'])) {
                $row['foto_perfil'] = 'data:image/jpeg;base64,' . base64_encode($row['foto_perfil']);
            }
            $items[] = $row;
        }
        $ok = true;
    }
} catch (Exception $e) {
    // Si falla, ok sigue en false
}

ob_clean();
echo json_encode(['ok' => $ok, 'items' => $items]);
?>