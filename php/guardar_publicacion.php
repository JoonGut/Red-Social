<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id_usuario = (int) $_SESSION['id_usuario'];
$texto      = trim($_POST['texto'] ?? '');
$ubicacion  = trim($_POST['ubicacion'] ?? '');
$pie_foto   = trim($_POST['pie_foto'] ?? '');

// Validación básica
if ($texto === '' && empty($_FILES['imagen']['name'])) {
    // No permitir post vacío (ni texto ni foto)
    header('Location: index.php?error=vacio');
    exit;
}

// 1. PROCESAR IMAGEN
$nombreImagen = null;
if (!empty($_FILES['imagen']['name'])) {
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) { header('Location: index.php?error=imagen'); exit; }
    if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) { header('Location: index.php?error=imagen_size'); exit; }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['imagen']['tmp_name']);
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($permitidos[$mime])) { header('Location: index.php?error=imagen_tipo'); exit; }

    $extension = $permitidos[$mime];
    $nombreImagen = uniqid('pub_', true) . '.' . $extension;
    $rutaDestino = __DIR__ . '/../multimedia/' . $nombreImagen;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
        header('Location: index.php?error=imagen_guardar');
        exit;
    }
}

try {
    // 2. INSERTAR PUBLICACIÓN
    $sql = "INSERT INTO publicacion (id_usuario, imagen, fecha_publicacion, ubicacion, pie_foto, texto) VALUES (?, ?, NOW(), ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('issss', $id_usuario, $nombreImagen, $ubicacion, $pie_foto, $texto);
    $stmt->execute();
    
    // Obtener ID de la publicación recién creada
    $id_publicacion = $stmt->insert_id;
    $stmt->close();

    // 3. PROCESAR ETIQUETAS (@MENCIONES)
    // Buscamos palabras que empiecen por @ (ej: @Pepe)
    preg_match_all('/@(\w+)/', $texto, $coincidencias);
    
    if (!empty($coincidencias[1])) {
        // $coincidencias[1] es un array con los nombres de usuario sin la arroba
        $nombresUnicos = array_unique($coincidencias[1]);
        
        // Preparamos consultas para buscar ID y para insertar etiqueta
        $stmtBuscar = $mysqli->prepare("SELECT id_usuario FROM usuario WHERE usuario = ?");
        $stmtEtiqueta = $mysqli->prepare("INSERT INTO etiquetas (id_usuario, id_publicacion) VALUES (?, ?)");

        foreach ($nombresUnicos as $nombreUser) {
            // Buscar si el usuario existe
            $stmtBuscar->bind_param('s', $nombreUser);
            $stmtBuscar->execute();
            $res = $stmtBuscar->get_result();
            
            if ($row = $res->fetch_assoc()) {
                $idEtiquetado = $row['id_usuario'];
                
                // Evitar auto-etiquetarse (opcional, pero recomendado)
                if ($idEtiquetado !== $id_usuario) {
                    $stmtEtiqueta->bind_param('ii', $idEtiquetado, $id_publicacion);
                    // Usamos try-catch por si ya existe la etiqueta (clave duplicada)
                    try {
                        $stmtEtiqueta->execute();
                        
                        // OPCIONAL: Crear Notificación aquí
                        // "Te han etiquetado en una publicación"
                         $stmtNoti = $mysqli->prepare("INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, texto_extra) VALUES (?, ?, 'etiqueta', ?, 'te etiquetó en un post')");
                         $stmtNoti->bind_param('iii', $idEtiquetado, $id_usuario, $id_publicacion);
                         $stmtNoti->execute();
                        
                    } catch (Exception $e) {
                        // Ignorar error si ya estaba etiquetado
                    }
                }
            }
        }
    }

} catch (mysqli_sql_exception $e) {
    header('Location: index.php?error=sql&msg=' . urlencode($e->getMessage()));
    exit;
}

header('Location: index.php');
exit;
?>