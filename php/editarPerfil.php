<?php
// php/editarPerfil.php

// 1. PROTECCIÓN JSON: Iniciamos buffer para capturar errores invisibles
ob_start();

session_start();
require 'db.php'; // Tu conexión a la BD

header('Content-Type: application/json');

$idUsuario = $_SESSION['id_usuario'] ?? 0;
$response = ['ok' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idUsuario > 0) {
        
        // --- 1. PROCESAR TEXTOS (IGUAL QUE ANTES) ---
        $nombre = trim($_POST['nombre'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        $stmt = $mysqli->prepare("UPDATE usuario SET nombre = ?, biografia = ? WHERE id_usuario = ?");
        $stmt->bind_param('ssi', $nombre, $bio, $idUsuario);
        
        if ($stmt->execute()) {
            // Actualizar Sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['biografia'] = $bio;
            
            $response['ok'] = true;
            $response['nombre'] = $nombre;
            $response['biografia'] = $bio;
        }
        $stmt->close();

        // --- 2. PROCESAR AVATAR (MODIFICADO A BLOB) ---
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
            
            // A. Leer el archivo binario directamente (sin moverlo a carpetas)
            $datosBinarios = file_get_contents($_FILES['foto_perfil']['tmp_name']);

            // B. Guardar el binario en la BD
            $stmt = $mysqli->prepare("UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?");
            // Usamos 's' porque MySQLi trata los BLOBs como strings de paquetes largos
            $stmt->bind_param('si', $datosBinarios, $idUsuario);
            $stmt->execute();
            $stmt->close();
            
            // C. Convertir a Base64 para la Sesión y el JS
            $base64 = base64_encode($datosBinarios);
            $_SESSION['foto_perfil'] = $base64;
            $response['foto_perfil'] = $base64; // Devolvemos el código de imagen, no la ruta
        }

        // --- 3. PROCESAR PORTADA (MODIFICADO A BLOB) ---
        if (isset($_FILES['portada']) && $_FILES['portada']['error'] === 0) {
            
            // A. Leer binario
            $datosBinariosPortada = file_get_contents($_FILES['portada']['tmp_name']);

            // B. Guardar en BD
            $stmt = $mysqli->prepare("UPDATE usuario SET portada = ? WHERE id_usuario = ?");
            $stmt->bind_param('si', $datosBinariosPortada, $idUsuario);
            $stmt->execute();
            $stmt->close();
            
            // C. Convertir a Base64
            $base64Portada = base64_encode($datosBinariosPortada);
            $_SESSION['portada'] = $base64Portada;
            $response['portada'] = $base64Portada;
        }
    }

    // Limpiamos cualquier "eco" o error sucio de PHP antes de enviar el JSON
    ob_clean();
    echo json_encode($response);

} catch (Exception $e) {
    // Si falla algo grave
    ob_clean();
    echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}

exit;
?>