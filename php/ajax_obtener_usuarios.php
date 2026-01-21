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
    // JOIN tabla usuario para sacar nombre y foto
    // JOIN seguidores donde id_usuario = perfilId (los que le siguen a él)
    $sql = "
        SELECT u.id_usuario, u.usuario, u.nombre, u.foto_perfil, u.biografia
        FROM usuario u
        INNER JOIN seguidores s ON u.id_usuario = s.id_seguidor
        WHERE s.id_usuario = ?
    ";
} else {
    // Buscar a quién sigue el usuario del perfil
    // JOIN seguidores donde id_seguidor = perfilId (a los que él sigue)
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
    // ... (código anterior igual hasta el while) ...

    while ($row = $res->fetch_assoc()) {
        $uId = $row['id_usuario'];
        $uUser = htmlspecialchars($row['usuario']);
        $uNombre = htmlspecialchars($row['nombre'] ?? $uUser);
        $uBio = htmlspecialchars($row['biografia'] ?? '');
        $uFoto = $row['foto_perfil'] ? '../multimedia/' . $row['foto_perfil'] : null;
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
        
        // --- AQUÍ ESTÁ EL CAMBIO: Envolvemos Avatar + Info en un enlace ---
        // Usamos la clase "user-link" y "data-user" para que tu JS lo detecte automáticamente
        $listaHTML .= '<a href="#" class="user-link" data-user="'.$uUser.'" style="display:flex; align-items:center; flex:1; text-decoration:none; color:inherit; overflow:hidden; margin-right:10px;">';

            // Avatar
            $listaHTML .= '<div class="mini-avatar" style="width:40px; height:40px; font-size:1.2rem; flex-shrink:0;">';
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
            if($uBio) $listaHTML .= '<span class="user-bio">'.$uBio.'</span>';
            $listaHTML .= '</div>';
        
        $listaHTML .= '</a>'; 
        // --- FIN DEL CAMBIO ---

        // Botón Seguir (Se queda fuera del enlace para poder pulsarlo sin ir al perfil)
        if ($miId != $uId) {
            $txtBtn = $sigo ? 'Siguiendo' : 'Seguir';
            // Nota: Si ya tienes CSS para esto, puedes quitar el style inline
            $estiloBtn = $sigo ? 'background:transparent; border:1px solid #555; color:#fff;' : 'background:#fff; color:#000; border:none;';
            
            $listaHTML .= '<button class="btn-accion-seguir" 
                            style="padding: 6px 15px; border-radius: 20px; font-weight:bold; cursor:pointer; font-size:0.8rem; '.$estiloBtn.'"
                            data-id="'.$uId.'" 
                            data-sigo="'.($sigo?'1':'0').'">
                            '.$txtBtn.'
                           </button>';
        }

        $listaHTML .= '</div>';
    }
} else {
    $listaHTML = '<div style="padding:20px; text-align:center; color:#777;">No hay usuarios aquí.</div>';
}

echo $listaHTML;
?>