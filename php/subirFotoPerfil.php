<?php
declare(strict_types=1);
// 1. Buffer para proteger la salida JSON de errores
ob_start();

session_start();
require __DIR__ . '/db.php';

// Cabecera para que JS sepa que respondemos JSON
header('Content-Type: application/json; charset=UTF-8');

$response = ['ok' => false];

try {
    // 2. Validaciones básicas
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

    // 3. Validar tamaño (Ejemplo: Máximo 5MB)
    // MySQL por defecto tiene un límite (max_allowed_packet), asegúrate de que sea suficiente.
    $maxSize = 5 * 1024 * 1024; 
    if ($size > $maxSize) {
        throw new Exception('La imagen es demasiado grande (Máx 5MB)');
    }

    // 4. Validar tipo de imagen (Seguridad)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($mimeType, $tiposPermitidos)) {
        throw new Exception('Formato de archivo no válido. Solo JPG, PNG, WEBP o GIF.');
    }

    // --- 5. LÓGICA BLOB (El cambio importante) ---
    
    // Leemos el contenido binario del archivo temporal
    $datosBinarios = file_get_contents($tmpName);

    // Preparamos la consulta para guardar el binario
    $stmt = $mysqli->prepare("UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?");
    
    // 's' = string (MySQLi trata los BLOBs como strings largos), 'i' = integer
    // Nota: Si la imagen es muy grande, a veces se requiere send_long_data, 
    // pero para fotos de perfil (<5MB) bind_param suele funcionar bien.
    $stmt->bind_param('si', $datosBinarios, $idUsuario);
    
    if ($stmt->execute()) {
        // 6. Éxito: Actualizamos la sesión con la nueva imagen en Base64
        // Esto permite mostrarla inmediatamente sin volver a consultar la BD
        $base64 = base64_encode($datosBinarios);
        $_SESSION['foto_perfil'] = $base64;
        
        $response['ok'] = true;
    } else {
        throw new Exception('Error al guardar en la base de datos');
    }
    $stmt->close();

} catch (Exception $e) {
    // Si algo falla, enviamos el mensaje de error
    $response['error'] = $e->getMessage();
}

// 7. Limpiar buffer y enviar respuesta JSON
ob_clean();
echo json_encode($response);
exit;
?>