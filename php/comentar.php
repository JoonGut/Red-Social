<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

// Cabecera JSON
header('Content-Type: application/json; charset=UTF-8');

// Limpiamos cualquier salida previa accidental
if (ob_get_length()) ob_clean();

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
        throw new Exception('Datos insuficientes');
    }

    // 1. INSERTAR EL COMENTARIO
    $stmt = $mysqli->prepare("INSERT INTO interaccion (id_publicacion, id_usuario, comentario, id_padre, tipo_interaccion, fecha_creacion) VALUES (?, ?, ?, ?, 'COMENTARIO', NOW())");
    $stmt->bind_param('iisi', $idPub, $yo, $texto, $idPadre);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al guardar comentario');
    }
    
    $newId = $stmt->insert_id;
    $stmt->close();

    // 2. OBTENER DATOS DEL USUARIO (Para actualizar la UI sin recargar)
    $stmt = $mysqli->prepare("SELECT usuario, nombre, foto_perfil FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param('i', $yo);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($userData && !empty($userData['foto_perfil'])) {
        $userData['foto_perfil'] = base64_encode($userData['foto_perfil']);
    }

    // 3. LÓGICA DE NOTIFICACIÓN
    $idDestino = 0;
    
    if ($idPadre) {
        // Si es una respuesta, notificamos al dueño del comentario padre
        $q = $mysqli->prepare("SELECT id_usuario FROM interaccion WHERE id_interaccion = ?");
        $q->bind_param('i', $idPadre);
        $q->execute();
        if ($res = $q->get_result()->fetch_assoc()) {
            $idDestino = (int)$res['id_usuario'];
        }
        $q->close();
    } else {
        // Si es comentario directo, notificamos al dueño de la publicación
        $q = $mysqli->prepare("SELECT id_usuario FROM publicacion WHERE id_publicacion = ?");
        $q->bind_param('i', $idPub);
        $q->execute();
        if ($res = $q->get_result()->fetch_assoc()) {
            $idDestino = (int)$res['id_usuario'];
        }
        $q->close();
    }

    // Insertar notificación si el destino es otro usuario
    if ($idDestino > 0 && $idDestino !== $yo) {
        $resumen = mb_strlen($texto) > 30 ? mb_substr($texto, 0, 30) . '...' : $texto;
        $tipoNoti = 'comentario';
        
        $stmtN = $mysqli->prepare("
            INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, texto_extra, leido, creado_en) 
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmtN->bind_param('iisis', $idDestino, $yo, $tipoNoti, $idPub, $resumen);
        $stmtN->execute();
        $stmtN->close();
    }

    // Respuesta final exitosa
    echo json_encode([
        'ok' => true,
        'id_comentario' => $newId,
        'usuario' => $userData['usuario'] ?? 'user',
        'nombre' => $userData['nombre'] ?? 'Nombre',
        'foto_perfil' => $userData['foto_perfil'] ?? null,
        'texto' => $texto,
        'creado_en' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}