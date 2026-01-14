<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../html/login.html');
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

if ($texto === '' || mb_strlen($texto) > 250) {
    header('Location: index.php?error=texto');
    exit;
}

$nombreImagen = null;

if (!empty($_FILES['imagen']['name'])) {

    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        header('Location: index.php?error=imagen');
        exit;
    }

    if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
        header('Location: index.php?error=imagen_size');
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['imagen']['tmp_name']);

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($permitidos[$mime])) {
        header('Location: index.php?error=imagen_tipo');
        exit;
    }

    $extension = $permitidos[$mime];
    $nombreImagen = uniqid('pub_', true) . '.' . $extension;

    $rutaDestino = __DIR__ . '/../multimedia/' . $nombreImagen;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
        header('Location: index.php?error=imagen_guardar');
        exit;
    }
}

try {
    $sql = "INSERT INTO publicacion 
            (id_usuario, imagen, fecha_publicacion, ubicacion, pie_foto, texto)
            VALUES (?, ?, NOW(), ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param('issss',
        $id_usuario,
        $nombreImagen,
        $ubicacion,
        $pie_foto,
        $texto
    );

    $stmt->execute();

} catch (mysqli_sql_exception $e) {
    header('Location: index.php?error=sql&msg=' . urlencode($e->getMessage()));
    exit;
}

header('Location: index.php');
exit;
