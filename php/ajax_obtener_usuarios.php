<?php
// php/ajax_obtener_usuarios.php
require 'db.php';
session_start();

$miId = $_SESSION['id_usuario'] ?? 0;
// El ID del perfil que estamos viendo (se recibe por GET)
$perfilId = (int)($_GET['id_perfil'] ?? $miId); 
$tipo = $_GET['tipo'] ?? 'seguidores'; // 'seguidores' o 'siguiendo'

if ($tipo === 'seguidores') {
    // Buscar quién sigue al usuario del perfil
    $sql = "
        SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil, u.biografia
        FROM usuario u
        INNER JOIN seguidores s ON u.id_usuario = s.id_seguidor
        WHERE s.id_usuario = ?
    ";
} else {
    // Buscar a quién sigue el usuario del perfil
    $sql = "
        SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil, u.biografia
        FROM usuario u
        INNER JOIN seguidores s ON u.id_usuario = s.id_usuario
        WHERE s.id_seguidor = ?
    ";
}

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $perfilId);
$stmt->execute();
$res = $stmt->get_result();

$listaHTML = "";

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $uId = $row['id_usuario'];
        $uUser = htmlspecialchars($row['usuario']);
        $uNombre = htmlspecialchars($row['nombre'] ?? $uUser);
        $uBio = htmlspecialchars($row['biografia'] ?? '');
        $uFoto = $row['foto_perfil'] ? '../multimedia/' . rawurlencode($row['foto_perfil']) : null;
        $inicial = strtoupper(substr($uUser, 0, 1));

        // Verificar si lo sigo
        $sigo = false;
        if ($miId > 0 && $miId != $uId) {
            $check = $mysqli->prepare("SELECT 1 FROM seguidores WHERE id_usuario = ? AND id_seguidor = ?");
            $check->bind_param('ii', $uId, $miId);
            $check->execute();
            if ($check->get_result()->num_rows > 0) $sigo = true;
            $check->close();
        }

        // Renderizar fila
        $listaHTML .= '<div class="user-row">';
        
        // --- Avatar + Info (Enlace al perfil) ---
        // Se usa 'user-link' para navegación AJAX y estilos flex del CSS
        $listaHTML .= '<a href="#" class="user-link" data-user="'.$uUser.'" style="display:flex; align-items:center; flex:1; text-decoration:none; color:inherit; overflow:hidden; margin-right:10px;">';

            // Avatar
            $listaHTML .= '<div class="mini-avatar">';
            if ($uFoto) {
                $listaHTML .= '<img src="'.$uFoto.'" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
            } else {
                $listaHTML .= $inicial;
            }
            $listaHTML .= '</div>';

            // Info
            $listaHTML .= '<div class="user-info" style="margin-left:10px;">';
            $listaHTML .= '<h4>'.$uNombre.'</h4>';
            $listaHTML .= '<span>@'.$uUser.'</span>';
            // Opcional: Mostrar bio recortada si quieres
            // if($uBio) $listaHTML .= '<span class="user-bio" style="font-size:0.75rem; opacity:0.7;">'.substr($uBio,0,30).'...</span>';
            $listaHTML .= '</div>';
        
        $listaHTML .= '</a>'; 

        // Botón Seguir
        if ($miId != $uId) {
            $txtBtn = $sigo ? 'Siguiendo' : 'Seguir';
            // El atributo data-sigo permite al CSS cambiar el estilo (borde vs relleno) automáticamente
            $listaHTML .= '<button class="btn-accion-seguir" 
                                    data-id="'.$uId.'" 
                                    data-sigo="'.($sigo?'1':'0').'">
                                    '.$txtBtn.'
                           </button>';
        }

        $listaHTML .= '</div>';
    }
} else {
    // Mensaje de vacío usando variables CSS implícitas (color muted)
    $listaHTML .= '<div style="padding:20px; text-align:center; opacity:0.6;">No hay usuarios aquí.</div>';
}

echo $listaHTML;
?>