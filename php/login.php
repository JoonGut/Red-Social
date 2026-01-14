<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/login.html');
    exit;
}

$login = trim($_POST['usuario'] ?? '');
$contraseña  = $_POST['password'] ?? '';

if ($login === '' || $contraseña === '') {
    header('Location: ../html/login.html?error=1');
    exit;
}

// Buscar por usuario o email
$sql = "SELECT id_usuario, usuario, email, password, id_rol
        FROM usuario
        WHERE usuario = ? OR email = ?
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ss', $login, $login);
$stmt->execute();

$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header('Location: ../html/login.html?error=1');
    exit;
}

// Contraseña en texto plano (como tienes ahora)
if ($contraseña !== $usuario['password']) {
    header('Location: ../html/login.html?error=1');
    exit;
}

// Login correcto
session_regenerate_id(true);
$_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
$_SESSION['usuario']   = $usuario['usuario'];
$_SESSION['email']     = $usuario['email'];
$_SESSION['id_rol']    = (int)$usuario['id_rol'];

header('Location: ../html/index.html');
exit;
