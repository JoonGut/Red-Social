<?php
// php/editarPerfil.php
session_start();
require 'db.php'; // Tu conexión a la BD

header('Content-Type: application/json');

$idUsuario = $_SESSION['id_usuario'] ?? 0;
$response = ['ok' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idUsuario > 0) {
    
    // 1. Recibir Textos
    $nombre = trim($_POST['nombre'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    // Actualizar Textos en BD
    $stmt = $mysqli->prepare("UPDATE usuario SET nombre = ?, biografia = ? WHERE id_usuario = ?");
    $stmt->bind_param('ssi', $nombre, $bio, $idUsuario);
    
    if ($stmt->execute()) {
        // Actualizar Sesión
        $_SESSION['nombre'] = $nombre;
        $_SESSION['biografia'] = $bio;
        
        $response['ok'] = true;
        $response['nombre'] = $nombre; // Devolver para JS
        $response['biografia'] = $bio; // Devolver para JS
    }
    $stmt->close();

    // 2. Procesar Avatar (Si se subió)
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nuevoNombre = 'avatar_' . $idUsuario . '_' . time() . '.' . $ext;
        $destino = '../multimedia/' . $nuevoNombre;
        
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
            // Guardar en BD
            $stmt = $mysqli->prepare("UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?");
            $stmt->bind_param('si', $nuevoNombre, $idUsuario);
            $stmt->execute();
            
            $_SESSION['foto_perfil'] = $nuevoNombre;
            $response['foto_perfil'] = $nuevoNombre; // IMPORTANTE: Devolver nombre fichero
        }
    }

    // 3. Procesar Portada (Si se subió)
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === 0) {
        $ext = pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION);
        $nuevoNombre = 'cover_' . $idUsuario . '_' . time() . '.' . $ext;
        $destino = '../multimedia/' . $nuevoNombre;
        
        if (move_uploaded_file($_FILES['portada']['tmp_name'], $destino)) {
            // Guardar en BD (Asegúrate de tener columna 'portada' en tu tabla usuario)
            // Si no tienes columna portada, comenta estas líneas de BD
            $stmt = $mysqli->prepare("UPDATE usuario SET portada = ? WHERE id_usuario = ?");
            $stmt->bind_param('si', $nuevoNombre, $idUsuario);
            $stmt->execute();
            
            $_SESSION['portada'] = $nuevoNombre;
            $response['portada'] = $nuevoNombre;
        }
    }
}

echo json_encode($response);
exit;
?>