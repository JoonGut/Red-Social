<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'no-login']);
    exit;
}

$sql = "
SELECT
  c.id_chat,
  u.id_usuario AS other_id,
  u.usuario AS other_usuario,
  u.nombre AS other_nombre,
  u.foto_perfil AS other_foto,

  (SELECT em.texto
   FROM enviar_mensaje em
   WHERE em.id_chat = c.id_chat
   ORDER BY em.id_mensaje DESC
   LIMIT 1) AS last_texto,

  (SELECT em.creado_en
   FROM enviar_mensaje em
   WHERE em.id_chat = c.id_chat
   ORDER BY em.id_mensaje DESC
   LIMIT 1) AS last_fecha,

  (SELECT COUNT(*)
   FROM enviar_mensaje em
   LEFT JOIN chat_lectura cl2
     ON cl2.id_chat = em.id_chat AND cl2.id_usuario = ?
   WHERE em.id_chat = c.id_chat
     AND em.id_usuario <> ?
     AND em.id_mensaje > COALESCE(cl2.ultimo_leido_id_mensaje, 0)
  ) AS unread_count

FROM pertenece_chat pc
JOIN chat c ON c.id_chat = pc.id_chat
JOIN pertenece_chat pc2 ON pc2.id_chat = c.id_chat AND pc2.id_usuario <> ?
JOIN usuario u ON u.id_usuario = pc2.id_usuario
WHERE pc.id_usuario = ?
ORDER BY last_fecha DESC, c.id_chat DESC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iiii', $yo, $yo, $yo, $yo);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['ok' => true, 'items' => $rows]);