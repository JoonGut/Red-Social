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
    header('Location: index.php?error=vacio');
    exit;
}

// 1. PROCESAR IMAGEN (MODIFICADO PARA BLOB)
$imagenContenido = null; // Variable para guardar los datos binarios

if (!empty($_FILES['imagen']['name'])) {
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) { header('Location: index.php?error=imagen'); exit; }
    
    // Verificamos tamaño (5MB)
    if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) { header('Location: index.php?error=imagen_size'); exit; }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['imagen']['tmp_name']);
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime, $permitidos)) { header('Location: index.php?error=imagen_tipo'); exit; }

    // --- CAMBIO PRINCIPAL AQUI ---
    // En lugar de mover el archivo, LEEMOS su contenido binario
    $imagenContenido = file_get_contents($_FILES['imagen']['tmp_name']);
}

try {
    // 2. INSERTAR PUBLICACIÓN
    // La consulta es igual, pero ahora el segundo ? recibirá los datos binarios
    $sql = "INSERT INTO publicacion (id_usuario, imagen, fecha_publicacion, ubicacion, pie_foto, texto) VALUES (?, ?, NOW(), ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    // Nota: 's' sirve para strings y también datos binarios en PHP/MySQLi básico.
    // Si la imagen es muy grande, a veces se requiere send_long_data, pero para 5MB suele funcionar así.
    $stmt->bind_param('issss', $id_usuario, $imagenContenido, $ubicacion, $pie_foto, $texto);
    
    $stmt->execute();
    
    $id_publicacion = $stmt->insert_id;
    $stmt->close();

    // 3. PROCESAR ETIQUETAS (@MENCIONES)
    // (Esta parte no cambia, sigue igual)
    preg_match_all('/@(\w+)/', $texto, $coincidencias);
    
    if (!empty($coincidencias[1])) {
        $nombresUnicos = array_unique($coincidencias[1]);
        $stmtBuscar = $mysqli->prepare("SELECT id_usuario FROM usuario WHERE usuario = ?");
        $stmtEtiqueta = $mysqli->prepare("INSERT INTO etiquetas (id_usuario, id_publicacion) VALUES (?, ?)");

        foreach ($nombresUnicos as $nombreUser) {
            $stmtBuscar->bind_param('s', $nombreUser);
            $stmtBuscar->execute();
            $res = $stmtBuscar->get_result();
            
            if ($row = $res->fetch_assoc()) {
                $idEtiquetado = $row['id_usuario'];
                if ($idEtiquetado !== $id_usuario) {
                    $stmtEtiqueta->bind_param('ii', $idEtiquetado, $id_publicacion);
                    try {
                        $stmtEtiqueta->execute();
                        // Notificaciones (opcional)
                         $stmtNoti = $mysqli->prepare("INSERT INTO notificaciones (id_usuario, id_actor, tipo, referencia_id, texto_extra) VALUES (?, ?, 'etiqueta', ?, 'te etiquetó en un post')");
                         $stmtNoti->bind_param('iii', $idEtiquetado, $id_usuario, $id_publicacion);
                         $stmtNoti->execute();
                    } catch (Exception $e) { }
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