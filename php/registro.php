<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/registro.html');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// 1. Validar campos vacíos
if ($nombre === '' || $usuario === '' || $email === '' || $password === '' || $password_confirm === '') {
    header('Location: ../html/registro.html?error=1');
    exit;
}

// 2. Validar que las contraseñas coincidan
if ($password !== $password_confirm) {
    header('Location: ../html/registro.html?error=2');
    exit;
}

// 3. Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../html/registro.html?error=3');
    exit;
}

// 4. Verificar si el usuario o email ya existen
$sql_check = "SELECT id_usuario FROM usuario WHERE usuario = ? OR email = ? LIMIT 1";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param('ss', $usuario, $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    header('Location: ../html/registro.html?error=4');
    exit;
}

// --- CAMBIO CLAVE: HASHING DE CONTRASEÑA ---
// Transformamos la contraseña en una cadena segura e irreversible
$password_segura = password_hash($password, PASSWORD_DEFAULT);
// --------------------------------------------

// 5. Insertar el nuevo usuario (Usamos $password_segura en lugar de $password)
$sql_insert = "INSERT INTO usuario (`usuario`, `email`, `password`, `nombre`, `id_rol`) VALUES (?, ?, ?, ?, 1)";
$stmt_insert = $mysqli->prepare($sql_insert);

// Bind con el hash generado
$stmt_insert->bind_param('ssss', $usuario, $email, $password_segura, $nombre);

if ($stmt_insert->execute()) {
    header('Location: ../login.html?success=1');
    exit;
} else {
    header('Location: ../html/registro.html?error=5');
    exit;
}
?>