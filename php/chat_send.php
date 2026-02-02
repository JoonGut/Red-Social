<?php
declare(strict_types=1);


ob_start();
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) throw new Exception('no-login');

    $idChat = (int)($_POST['id_chat'] ?? 0);
    $texto = trim((string)($_POST['texto'] ?? ''));

    if ($idChat <= 0 || $texto === '') throw new Exception('bad-request');
    if (mb_strlen($texto) > 250) throw new Exception('too-long');

    
    $stmt = $mysqli->prepare("SELECT 1 FROM pertenece_chat WHERE id_chat = ? AND id_usuario = ? LIMIT 1");
    $stmt->bind_param('ii', $idChat, $yo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) throw new Exception('forbidden');
    $stmt->close();

    
    $stmt = $mysqli->prepare("INSERT INTO enviar_mensaje (texto, id_usuario, id_chat) VALUES (?, ?, ?)");
    $stmt->bind_param('sii', $texto, $yo, $idChat);
    $stmt->execute();
    $newId = (int)$stmt->insert_id;
    $stmt->close();

    
    
    
    
    
    $sqlParticipantes = "SELECT id_usuario FROM pertenece_chat WHERE id_chat = ? AND id_usuario != ?";
    $stmtPart = $mysqli->prepare($sqlParticipantes);
    
    if ($stmtPart) {
        $stmtPart->bind_param('ii', $idChat, $yo);
        $stmtPart->execute();
        $resPart = $stmtPart->get_result();
        
        
        
        $sqlNoti = "INSERT INTO notificaciones (id_usuario, id_actor, tipo, leido, referencia_id, creado_en) 
                    VALUES (?, ?, 'mensaje', 0, ?, NOW())";
        $stmtNoti = $mysqli->prepare($sqlNoti);

        if ($stmtNoti) {
            while ($row = $resPart->fetch_assoc()) {
                $receptorId = (int)$row['id_usuario'];
                
                $stmtNoti->bind_param('iii', $receptorId, $yo, $idChat);
                $stmtNoti->execute();
            }
            $stmtNoti->close();
        }
        $stmtPart->close();
    }
    


    
    $stmt = $mysqli->prepare("SELECT creado_en FROM enviar_mensaje WHERE id_mensaje = ? LIMIT 1");
    $stmt->bind_param('i', $newId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $response = ['ok' => true, 'id_mensaje' => $newId, 'creado_en' => ($row['creado_en'] ?? null)];

} catch (Exception $e) {
    $response = ['ok' => false, 'error' => $e->getMessage()];
}

ob_clean();
echo json_encode($response);
?>