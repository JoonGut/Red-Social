<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

// 1. Verificar Login
$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    http_response_code(401);
    exit('no-login');
}

// 2. Validar ID
$idPub = (int)($_POST['id'] ?? 0);
if ($idPub <= 0) {
    exit('error-id');
}

// 3. Verificar propiedad y obtener nombre de imagen (para borrarla del disco)
$stmt = $mysqli->prepare("SELECT imagen, id_usuario FROM publicacion WHERE id_publicacion = ? LIMIT 1");
$stmt->bind_param('i', $idPub);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
$stmt->close();

if (!$post) {
    exit('not-found');
}

// Seguridad: ¿Es mía?
if ((int)$post['id_usuario'] !== $yo) {
    http_response_code(403);
    exit('forbidden'); // No puedes borrar lo que no es tuyo
}

// 4. Eliminar de la Base de Datos
$stmt = $mysqli->prepare("DELETE FROM publicacion WHERE id_publicacion = ?");
$stmt->bind_param('i', $idPub);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // 5. Si se borró de la BD, borramos el archivo físico
    $imagen = trim((string)$post['imagen']);
    if ($imagen !== '') {
        $rutaArchivo = __DIR__ . '/../multimedia/' . $imagen;
        if (file_exists($rutaArchivo)) {
            @unlink($rutaArchivo); // Borra el archivo del disco
        }
    }
    echo 'ok';
} else {
    echo 'error-db';
}
?>