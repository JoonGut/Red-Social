<?php
// chat_list.php
declare(strict_types=1);
ob_start();
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$yo = (int)($_SESSION['id_usuario'] ?? 0);
$items = [];

try {
    if ($yo > 0) {
        // SQL original
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

        foreach ($listaBruta as $chat) {
            $idChat = (int)$chat['id_chat'];
            $esGrupo = !empty($chat['nombre_grupo']);
            
            $otherUser = null;
            $otherName = $chat['nombre_grupo']; 
            $otherFoto = null;
            
            if (!$esGrupo) {
                // Buscar al otro usuario
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
                    
                    // --- CONVERSIÓN BLOB A BASE64 ---
                    if (!empty($rowOther['foto_perfil'])) {
                        $otherFoto = 'data:image/jpeg;base64,' . base64_encode($rowOther['foto_perfil']);
                    }
                } else {
                    continue; 
                }
                $stmtOther->close();
            } else {
                $otherUser = 'grupo';
            }

            // Último mensaje
            $stmtMsg = $mysqli->prepare("SELECT texto, creado_en FROM enviar_mensaje WHERE id_chat = ? ORDER BY id_mensaje DESC LIMIT 1");
            $stmtMsg->bind_param('i', $idChat);
            $stmtMsg->execute();
            $lastMsg = $stmtMsg->get_result()->fetch_assoc();
            $stmtMsg->close();

            // Mensajes no leídos
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

            $items[] = [
                'id_chat' => $idChat,
                'last_texto' => $lastMsg['texto'] ?? '',
                'last_fecha' => $lastMsg['creado_en'] ?? '',
                'unread_count' => (int)$unread,
                'es_grupo' => $esGrupo,
                'other_usuario' => $otherUser,
                'other_nombre' => $otherName,
                'other_foto' => $otherFoto, // Ahora es Base64 completo
                'miembros' => (int)$chat['miembros']
            ];
        }

        // Ordenar por fecha PHP
        usort($items, function($a, $b) {
            return strcmp($b['last_fecha'], $a['last_fecha']);
        });
    }

    ob_clean(); // 2. Limpiar basura
    echo json_encode(['ok' => true, 'items' => $items]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>