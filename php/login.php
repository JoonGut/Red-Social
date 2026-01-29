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

$sql = "SELECT `id_usuario`, `usuario`, `nombre`, `email`, `password`, `id_rol`,`foto_perfil`, `biografia`
        FROM usuario
        WHERE usuario = ? OR email = ?
        LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ss', $login, $login);
$stmt->execute();

$result = $stmt->get_result();
$usuario = $result->fetch_assoc(); // Aquí guardas los datos en $usuario

if (!$usuario) {
    header('Location: ../login.html?error=1');
    exit;
}

// Nota: Te recomiendo usar password_verify si las contraseñas están hasheadas
if ($contraseña !== $usuario['password']) {
    header('Location: ../login.html?error=1');
    exit;
}

session_regenerate_id(true);
$_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
$_SESSION['usuario']    = $usuario['usuario'];
$_SESSION['nombre']     = $usuario['nombre'];   
$_SESSION['email']      = $usuario['email'];
$_SESSION['id_rol']     = (int)$usuario['id_rol'];
$_SESSION['biografia']  = $usuario['biografia'];

// CORRECCIÓN AQUÍ: Usamos $usuario en lugar de $row y validamos si existe la foto
if (!empty($usuario['foto_perfil'])) {
    $_SESSION['foto_perfil'] = base64_encode($usuario['foto_perfil']);
} else {
    $_SESSION['foto_perfil'] = '';
}

header('Location: index.php');
exit;