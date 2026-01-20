<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

// Marcamos como leídas las que se piden (si el JS manda marcar)
if (isset($_POST['marcar_leidas'])) {
    $mysqli->query("UPDATE notificaciones SET leido = 1 WHERE id_usuario = $yo");
    echo json_encode(['ok' => true]);
    exit;
}

// Obtenemos las últimas 20 notificaciones (leídas o no)
$sql = "
    SELECT 
        n.id_notificacion, n.tipo, n.texto_extra, n.leido, n.creado_en,
        u.usuario AS actor_usuario, u.foto_perfil AS actor_foto
    FROM notificaciones n
    JOIN usuario u ON u.id_usuario = n.id_actor
    WHERE n.id_usuario = ?
    ORDER BY n.id_notificacion DESC
    LIMIT 20
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $yo);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);

$resCount = $mysqli->query("SELECT COUNT(*) c FROM notificaciones WHERE id_usuario = $yo AND leido = 0");
$rowC = $resCount->fetch_assoc();
$sinLeer = $rowC['c'];

echo json_encode(['ok' => true, 'items' => $data, 'sin_leer' => $sinLeer]);
?>