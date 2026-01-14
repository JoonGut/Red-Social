<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$login = trim($_POST['usuario'] ?? '');
$contraseña  = $_POST['password'] ?? '';

if ($login === '' || $contraseña === '') {
    header('Location: ../login.html?error=1');
    exit;
}

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
    header('Location: ../login.html?error=1');
    exit;
}

if ($contraseña !== $usuario['password']) {
    header('Location: ../login.html?error=1');
    exit;
}

session_regenerate_id(true);
$_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
$_SESSION['usuario']   = $usuario['usuario'];
$_SESSION['email']     = $usuario['email'];
$_SESSION['id_rol']    = (int)$usuario['id_rol'];

header('Location: ../html/index.html');
exit;
