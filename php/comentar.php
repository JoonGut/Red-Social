<?php
declare(strict_types=1);
// php/comentar.php

session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    if ($yo <= 0) {
        http_response_code(401);
        throw new Exception('no-login');
    }

    $idPub = (int)($_POST['id_publicacion'] ?? 0);
    $texto = trim((string)($_POST['texto'] ?? ''));
    $idPadre = !empty($_POST['id_padre']) ? (int)$_POST['id_padre'] : null;

    if ($idPub <= 0 || $texto === '') {
        throw new Exception('vacío');
    }

    // 1. INSERTAR EN INTERACCION (Corregido: fecha_creacion)
    $stmt = $mysqli->prepare("INSERT INTO interaccion (id_publicacion, id_usuario, comentario, id_padre, tipo_interaccion, fecha_creacion) VALUES (?, ?, ?, ?, 'COMENTARIO', NOW())");
    
    $stmt->bind_param('iisi', $idPub, $yo, $texto, $idPadre);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    // 2. Datos para el Frontend
    $stmt = $mysqli->prepare("SELECT usuario, nombre, foto_perfil FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param('i', $yo);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    
    if (!empty($userData['foto_perfil'])) {
        $userData['foto_perfil'] = base64_encode($userData['foto_perfil']);
    }
    $stmt->close();

    // ---------------------------------------------------------
    // 3. NOTIFICACIÓN
    // ---------------------------------------------------------
    $idDestino = 0;
    $tipoNoti = 'comentario';

    // A. Buscar a quién notificar
    if ($idPadre) {
        $q = $mysqli->prepare("SELECT id_usuario FROM interaccion WHERE id_interaccion = ?");
        $q->bind_param('i', $idPadre);
        $q->execute();
        if ($res = $q->get_result()->fetch_assoc()) {
            $idDestino = (int)$res['id_usuario'];
        }
        $q->close();
    } else {
        $q = $mysqli->prepare("SELECT id_usuario FROM publicacion WHERE id_publicacion = ?");
        $q->bind_param('i', $idPub);
        $q->execute();
        if ($res = $q->get_result()->fetch_assoc()) {
            $idDestino = (int)$res['id_usuario'];
        }
        $q->close();
    }

    // B. Insertar notificación
    if ($idDestino > 0 && $idDestino !== $yo) {
        $resumen = mb_strlen($texto) > 30 ? mb_substr($texto, 0, 30) . '...' : $texto;
        
        // Estructura robusta igual que chat_send
        $stmtN = $mysqli->prepare("
            INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, texto_extra, leido, creado_en) 
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        
        // Bind corregido (iisis)
        $stmtN->bind_param('iisis', $idDestino, $yo, $tipoNoti, $idPub, $resumen);
        $stmtN->execute();
        $stmtN->close();
    }
    // ---------------------------------------------------------

    echo json_encode([
        'ok' => true,
        'id_comentario' => $newId,
        'usuario' => $userData['usuario'],
        'nombre' => $userData['nombre'],
        'foto_perfil' => $userData['foto_perfil'],
        'texto' => $texto,
        'creado_en' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>