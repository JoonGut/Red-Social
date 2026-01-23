<?php
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json');

// SEGURIDAD
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$accion = $_GET['accion'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$limite = 10; // MÁXIMO 10 POR PÁGINA
$offset = ($pagina - 1) * $limite;

// --- LISTAR USUARIOS (Paginado) ---
if ($accion === 'listar_usuarios') {
    // 1. Contar total de usuarios
    $sqlTotal = "SELECT COUNT(*) as total FROM usuario";
    $resTotal = $mysqli->query($sqlTotal);
    $totalItems = $resTotal->fetch_assoc()['total'];
    $totalPaginas = ceil($totalItems / $limite);

    // 2. Obtener usuarios de la página actual
    $sql = "SELECT id_usuario, usuario, nombre, email, id_rol 
            FROM usuario 
            ORDER BY id_usuario DESC 
            LIMIT $limite OFFSET $offset";
    
    $res = $mysqli->query($sql);
    $usuarios = [];
    while($row = $res->fetch_assoc()) {
        $usuarios[] = $row;
    }

    // Devolvemos estructura completa
    echo json_encode([
        'items' => $usuarios,
        'paginaActual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'totalItems' => $totalItems
    ]);
    exit;
}

// --- BORRAR USUARIO ---
if ($accion === 'borrar_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)$_SESSION['id_usuario']) {
        echo json_encode(['ok' => false, 'error' => 'No puedes autoborrarte']);
        exit;
    }
    $mysqli->query("DELETE FROM interaccion WHERE id_usuario = $id");
    $mysqli->query("DELETE FROM publicacion WHERE id_usuario = $id");
    $mysqli->query("DELETE FROM seguidores WHERE id_usuario = $id OR id_seguidor = $id");
    $mysqli->query("DELETE FROM usuario WHERE id_usuario = $id");
    echo json_encode(['ok' => true]);
    exit;
}

// --- LISTAR POSTS (Paginado) ---
if ($accion === 'listar_posts') {
    // 1. Contar total posts
    $sqlTotal = "SELECT COUNT(*) as total FROM publicacion";
    $resTotal = $mysqli->query($sqlTotal);
    $totalItems = $resTotal->fetch_assoc()['total'];
    $totalPaginas = ceil($totalItems / $limite);

    // 2. Obtener posts
    $sql = "SELECT p.id_publicacion, p.texto, p.fecha_publicacion, u.usuario 
            FROM publicacion p 
            JOIN usuario u ON p.id_usuario = u.id_usuario 
            ORDER BY p.fecha_publicacion DESC 
            LIMIT $limite OFFSET $offset";
            
    $res = $mysqli->query($sql);
    $posts = [];
    while($row = $res->fetch_assoc()) {
        $posts[] = $row;
    }

    echo json_encode([
        'items' => $posts,
        'paginaActual' => $pagina,
        'totalPaginas' => $totalPaginas
    ]);
    exit;
}

// --- BORRAR POST ---
if ($accion === 'borrar_post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $mysqli->query("DELETE FROM interaccion WHERE id_publicacion = $id");
    $mysqli->query("DELETE FROM publicacion WHERE id_publicacion = $id");
    echo json_encode(['ok' => true]);
    exit;
}
?>