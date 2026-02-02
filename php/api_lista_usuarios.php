<?php



ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';


if (isset($mysqli)) $con = $mysqli;
elseif (isset($conn)) $con = $conn;
else exit(json_encode([]));


if (session_status() === PHP_SESSION_NONE) session_start();
$miId = (int)($_SESSION['id_usuario'] ?? 0);

$tipo = $_GET['tipo'] ?? '';
$idUsuarioPerfil = (int)($_GET['id_usuario'] ?? 0);

if ($idUsuarioPerfil <= 0) exit(json_encode([]));




$subConsulta = ", (SELECT COUNT(*) FROM seguidores WHERE id_seguidor = $miId AND id_usuario = u.id_usuario) as lo_sigo";

$sql = "";

if ($tipo === 'seguidores') {


    $sql = "
        SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil, u.biografia $subConsulta
        FROM seguidores s
        JOIN usuario u ON u.id_usuario = s.id_seguidor
        WHERE s.id_usuario = ?
    ";
} elseif ($tipo === 'siguiendo' || $tipo === 'seguidos') {


    $sql = "
        SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil, u.biografia $subConsulta
        FROM seguidores s
        JOIN usuario u ON u.id_usuario = s.id_usuario
        WHERE s.id_seguidor = ?
    ";
} else {
    exit(json_encode([]));
}

try {
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $idUsuarioPerfil);
    $stmt->execute();
    $res = $stmt->get_result();

    $lista = [];
    while ($row = $res->fetch_assoc()) {

        $nombre = mb_convert_encoding($row['nombre'], 'UTF-8', 'ISO-8859-1');
        $bio = mb_convert_encoding($row['biografia'] ?? '', 'UTF-8', 'ISO-8859-1');

        $lista[] = [
            'id_usuario' => $row['id_usuario'],
            'usuario' => $row['usuario'],
            'nombre' => $nombre,
            'foto_perfil' => $row['foto_perfil'],
            'biografia' => $bio,
            'lo_sigo' => (int)$row['lo_sigo'],
            'soy_yo' => ($row['id_usuario'] == $miId)
        ];
    }
    echo json_encode($lista);
} catch (Exception $e) {

    echo json_encode([]);
}
