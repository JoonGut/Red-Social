<?php
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json');


if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$accion = $_GET['accion'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';


if ($accion === 'listar_usuarios') {


    $filtroSql = "";
    $types = "";
    $params = [];

    if ($busqueda !== '') {
        $filtroSql = "WHERE (usuario LIKE ? OR email LIKE ?)";
        $types = "ss";
        $term = "%" . $busqueda . "%";
        $params[] = $term;
        $params[] = $term;
    }


    $sqlTotal = "SELECT COUNT(*) as total FROM usuario $filtroSql";
    $stmtT = $mysqli->prepare($sqlTotal);
    if ($busqueda !== '') {
        $stmtT->bind_param($types, ...$params);
    }
    $stmtT->execute();
    $totalItems = $stmtT->get_result()->fetch_assoc()['total'];
    $stmtT->close();

    $totalPaginas = ceil($totalItems / $limite);


    $sql = "SELECT id_usuario, usuario, nombre, email, id_rol 
            FROM usuario 
            $filtroSql
            ORDER BY id_usuario DESC 
            LIMIT ? OFFSET ?";


    $types .= "ii";
    $params[] = $limite;
    $params[] = $offset;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $usuarios = [];
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = $row;
    }

    echo json_encode([
        'items' => $usuarios,
        'paginaActual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'totalItems' => $totalItems
    ]);
    exit;
}


if ($accion === 'listar_posts') {


    $filtroSql = "";
    $types = "";
    $params = [];

    if ($busqueda !== '') {
        $filtroSql = "WHERE (p.texto LIKE ? OR u.usuario LIKE ?)";
        $types = "ss";
        $term = "%" . $busqueda . "%";
        $params[] = $term;
        $params[] = $term;
    }


    $sqlTotal = "SELECT COUNT(*) as total 
                 FROM publicacion p 
                 JOIN usuario u ON p.id_usuario = u.id_usuario 
                 $filtroSql";

    $stmtT = $mysqli->prepare($sqlTotal);
    if ($busqueda !== '') {
        $stmtT->bind_param($types, ...$params);
    }
    $stmtT->execute();
    $totalItems = $stmtT->get_result()->fetch_assoc()['total'];
    $stmtT->close();

    $totalPaginas = ceil($totalItems / $limite);


    $sql = "SELECT p.id_publicacion, p.texto, p.fecha_publicacion, u.usuario 
            FROM publicacion p 
            JOIN usuario u ON p.id_usuario = u.id_usuario 
            $filtroSql
            ORDER BY p.fecha_publicacion DESC 
            LIMIT ? OFFSET ?";

    $types .= "ii";
    $params[] = $limite;
    $params[] = $offset;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $posts = [];
    while ($row = $res->fetch_assoc()) {
        $posts[] = $row;
    }

    echo json_encode([
        'items' => $posts,
        'paginaActual' => $pagina,
        'totalPaginas' => $totalPaginas
    ]);
    exit;
}


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


if ($accion === 'borrar_post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $mysqli->query("DELETE FROM interaccion WHERE id_publicacion = $id");
    $mysqli->query("DELETE FROM publicacion WHERE id_publicacion = $id");
    echo json_encode(['ok' => true]);
    exit;
}
