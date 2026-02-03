<?php
declare(strict_types=1);

// Configuraciones para evitar que errores sucios rompan el JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

// Gestión de conexión
if (isset($mysqli)) $con = $mysqli;
elseif (isset($conn)) $con = $conn;
else exit(json_encode([]));

if (session_status() === PHP_SESSION_NONE) session_start();
$miId = (int)($_SESSION['id_usuario'] ?? 0);

$tipo = $_GET['tipo'] ?? '';
$idUsuarioPerfil = (int)($_GET['id_usuario'] ?? 0);

if ($idUsuarioPerfil <= 0) exit(json_encode([]));

// Subconsulta segura
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
        
        // CORRECCIÓN DE CODIFICACIÓN DE TEXTO
        // Si tu DB ya está en UTF8, estas líneas de mb_convert_encoding podrían no ser necesarias,
        // pero las dejo porque las tenías.
        $nombre = mb_convert_encoding($row['nombre'], 'UTF-8', 'ISO-8859-1');
        $bio = mb_convert_encoding($row['biografia'] ?? '', 'UTF-8', 'ISO-8859-1');

        // --- CORRECCIÓN CRÍTICA DE IMAGEN (EL FIX) ---
        // Convertimos el BLOB binario a Base64 para que pueda viajar en el JSON
        $fotoBase64 = null;
        if (!empty($row['foto_perfil'])) {
            $fotoBase64 = base64_encode($row['foto_perfil']);
        }

        $lista[] = [
            'id_usuario' => $row['id_usuario'],
            'usuario' => $row['usuario'],
            'nombre' => $nombre,
            // Enviamos la cadena codificada, NO el binario
            'foto_perfil' => $fotoBase64, 
            'biografia' => $bio,
            'lo_sigo' => (int)$row['lo_sigo'],
            'soy_yo' => ($row['id_usuario'] == $miId)
        ];
    }
    
    // Ahora sí, el json_encode funcionará
    echo json_encode($lista);

} catch (Exception $e) {
    echo json_encode([]);
}
?>