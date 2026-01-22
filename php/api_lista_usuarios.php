<?php
// php/api_lista_usuarios.php

// 1. Configuración de errores (ocultos para no romper JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

// Conexión
if (isset($mysqli)) $con = $mysqli;
elseif (isset($conn)) $con = $conn;
else exit(json_encode([]));

// Sesión para saber TU ID
if (session_status() === PHP_SESSION_NONE) session_start();
$miId = (int)($_SESSION['id_usuario'] ?? 0);

$tipo = $_GET['tipo'] ?? ''; 
$idUsuarioPerfil = (int)($_GET['id_usuario'] ?? 0);

if ($idUsuarioPerfil <= 0) exit(json_encode([]));

// LA SUBCONSULTA CORREGIDA:
// Verifica si TU ($miId) eres seguidor de ese usuario (u.id_usuario)
// Usamos 'id_usuario' porque así se llama la columna del "seguido" en tu tabla.
$subConsulta = ", (SELECT COUNT(*) FROM seguidores WHERE id_seguidor = $miId AND id_usuario = u.id_usuario) as lo_sigo";

$sql = "";

if ($tipo === 'seguidores') {
    // QUIÉN SIGUE AL PERFIL (El perfil es el 'id_usuario' en la tabla seguidores)
    // Queremos los datos del FAN (u.id_usuario se une con s.id_seguidor)
    $sql = "
        SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil, u.biografia $subConsulta
        FROM seguidores s
        JOIN usuario u ON u.id_usuario = s.id_seguidor
        WHERE s.id_usuario = ?
    ";
} elseif ($tipo === 'siguiendo' || $tipo === 'seguidos') { 
    // A QUIÉN SIGUE EL PERFIL (El perfil es el 'id_seguidor')
    // Queremos los datos del ÍDOLO (u.id_usuario se une con s.id_usuario)
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
        // Codificación segura
        $nombre = mb_convert_encoding($row['nombre'], 'UTF-8', 'ISO-8859-1');
        $bio = mb_convert_encoding($row['biografia'] ?? '', 'UTF-8', 'ISO-8859-1');

        $lista[] = [
            'id_usuario' => $row['id_usuario'],
            'usuario' => $row['usuario'],
            'nombre' => $nombre,
            'foto_perfil' => $row['foto_perfil'],
            'biografia' => $bio,
            'lo_sigo' => (int)$row['lo_sigo'], // 1 si lo sigues, 0 si no
            'soy_yo' => ($row['id_usuario'] == $miId)
        ];
    }
    echo json_encode($lista);

} catch (Exception $e) {
    // Si falla, devuelve array vacío (o podrías loguear el error)
    echo json_encode([]);
}
?>