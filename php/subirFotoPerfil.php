<?php
declare(strict_types=1);

ob_start();

session_start();
require __DIR__ . '/db.php';


header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    
    $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
    
    if ($idUsuario <= 0) {
        throw new Exception('No has iniciado sesión');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir el archivo');
    }

    $archivo = $_FILES['foto_perfil'];
    $tmpName = $archivo['tmp_name'];
    $size    = $archivo['size'];

    
    
    $maxSize = 5 * 1024 * 1024; 
    if ($size > $maxSize) {
        throw new Exception('La imagen es demasiado grande (Máx 5MB)');
    }

    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($mimeType, $tiposPermitidos)) {
        throw new Exception('Formato de archivo no válido. Solo JPG, PNG, WEBP o GIF.');
    }

    
    
    
    $datosBinarios = file_get_contents($tmpName);

    
    $stmt = $mysqli->prepare("UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?");
    
    
    
    
    $stmt->bind_param('si', $datosBinarios, $idUsuario);
    
    if ($stmt->execute()) {
        
        
        $base64 = base64_encode($datosBinarios);
        $_SESSION['foto_perfil'] = $base64;
        
        $response['ok'] = true;
    } else {
        throw new Exception('Error al guardar en la base de datos');
    }
    $stmt->close();

} catch (Exception $e) {
    
    $response['error'] = $e->getMessage();
}


ob_clean();
echo json_encode($response);
exit;
?>