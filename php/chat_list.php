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

// 1. Obtener IDs de chats donde estoy yo
$sql = "
    SELECT 
        c.id_chat,
        c.nombre AS nombre_grupo,
        c.miembros
    FROM pertenece_chat pc
    JOIN chat c ON c.id_chat = pc.id_chat
    WHERE pc.id_usuario = ?
    ORDER BY c.id_chat DESC
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $yo);
$stmt->execute();
$res = $stmt->get_result();

$listaBruta = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$items = [];

// 2. Procesar cada chat uno a uno (Más lento pero 100% seguro contra fallos SQL)
foreach ($listaBruta as $chat) {
    $idChat = (int)$chat['id_chat'];
    $esGrupo = !empty($chat['nombre_grupo']);
    
    // Valores por defecto
    $otherUser = null;
    $otherName = $chat['nombre_grupo']; 
    $otherFoto = null;
    $unread = 0;
    
    // A) Si es PRIVADO, buscar quién es el otro
    if (!$esGrupo) {
        $stmtOther = $mysqli->prepare("
            SELECT u.usuario, u.nombre, u.foto_perfil 
            FROM pertenece_chat pc
            JOIN usuario u ON u.id_usuario = pc.id_usuario
            WHERE pc.id_chat = ? AND pc.id_usuario != ?
            LIMIT 1
        ");
        $stmtOther->bind_param('ii', $idChat, $yo);
        $stmtOther->execute();
        $resOther = $stmtOther->get_result();
        if ($rowOther = $resOther->fetch_assoc()) {
            $otherUser = $rowOther['usuario'];
            $otherName = $rowOther['nombre'];
            $otherFoto = $rowOther['foto_perfil'];
        } else {
            // Chat corrupto (solo estoy yo), lo saltamos o mostramos como "Desconocido"
            continue; 
        }
        $stmtOther->close();
    } else {
        $otherUser = 'grupo'; // Marcador para JS
    }

    // B) Obtener último mensaje y fecha
    $stmtMsg = $mysqli->prepare("SELECT texto, creado_en FROM enviar_mensaje WHERE id_chat = ? ORDER BY id_mensaje DESC LIMIT 1");
    $stmtMsg->bind_param('i', $idChat);
    $stmtMsg->execute();
    $lastMsg = $stmtMsg->get_result()->fetch_assoc();
    $stmtMsg->close();

    // C) Contar No Leídos
    $stmtUnread = $mysqli->prepare("
        SELECT COUNT(*) as c
        FROM enviar_mensaje em
        WHERE em.id_chat = ? 
          AND em.id_usuario != ?
          AND em.id_mensaje > (SELECT COALESCE(MAX(ultimo_leido_id_mensaje),0) FROM chat_lectura WHERE id_chat = ? AND id_usuario = ?)
    ");
    $stmtUnread->bind_param('iiii', $idChat, $yo, $idChat, $yo);
    $stmtUnread->execute();
    $unread = $stmtUnread->get_result()->fetch_assoc()['c'];
    $stmtUnread->close();

    // Armar el objeto final
    $items[] = [
        'id_chat' => $idChat,
        'last_texto' => $lastMsg['texto'] ?? '',
        'last_fecha' => $lastMsg['creado_en'] ?? '',
        'unread_count' => (int)$unread,
        'es_grupo' => $esGrupo,
        'other_usuario' => $otherUser,
        'other_nombre' => $otherName,
        'other_foto' => $otherFoto,
        'miembros' => (int)$chat['miembros']
    ];
}

// Ordenar por fecha del último mensaje (PHP side)
usort($items, function($a, $b) {
    return strcmp($b['last_fecha'], $a['last_fecha']);
});

echo json_encode(['ok' => true, 'items' => $items]);
?>