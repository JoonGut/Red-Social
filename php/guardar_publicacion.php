<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

/* 1️⃣ Comprobar login */
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../html/login.html');
    exit;
}

/* 2️⃣ Solo POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/index.php');
    exit;
}

/* 3️⃣ Recoger datos */
$id_usuario = (int) $_SESSION['id_usuario'];
$texto      = trim($_POST['texto'] ?? '');
$ubicacion  = trim($_POST['ubicacion'] ?? '');
$pie_foto   = trim($_POST['pie_foto'] ?? '');

/* 4️⃣ Validar texto */
if ($texto === '' || mb_strlen($texto) > 250) {
    header('Location: ../html/index.php?error=texto');
    exit;
}

/* 5️⃣ Imagen (opcional) */
$nombreImagen = null;

if (!empty($_FILES['imagen']['name'])) {

    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../html/index.php?error=imagen');
        exit;
    }

    // Tamaño máximo: 5MB
    if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
        header('Location: ../html/index.php?error=imagen_size');
        exit;
    }

    // Validar tipo MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['imagen']['tmp_name']);

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($permitidos[$mime])) {
        header('Location: ../html/index.php?error=imagen_tipo');
        exit;
    }

    // Nombre único
    $extension = $permitidos[$mime];
    $nombreImagen = uniqid('pub_', true) . '.' . $extension;

    $rutaDestino = __DIR__ . '/../uploads/' . $nombreImagen;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
        header('Location: ../html/index.php?error=imagen_guardar');
        exit;
    }
}

/* 6️⃣ Insertar en BD */
$sql = "INSERT INTO publicacion 
        (id_usuario, imagen, fecha_publicacion, ubicacion, pie_foto, texto)
        VALUES (?, ?, CURDATE(), ?, ?, ?)";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    'issss',
    $id_usuario,
    $nombreImagen,
    $ubicacion,
    $pie_foto,
    $texto
);

$stmt->execute();

/* 7️⃣ Volver al inicio */
header('Location: ../html/index.php');
exit;
