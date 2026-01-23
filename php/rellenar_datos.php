<?php
require __DIR__ . '/db.php';

// Aumentar tiempo de ejecución
set_time_limit(300); 

echo "<h1>⚙️ Generando datos aleatorios...</h1>";

// 1. OBTENER TODOS LOS IDS DISPONIBLES
$usuarios = $mysqli->query("SELECT id_usuario FROM usuario")->fetch_all(MYSQLI_ASSOC);
$posts    = $mysqli->query("SELECT id_publicacion FROM publicacion")->fetch_all(MYSQLI_ASSOC);

if (count($usuarios) < 1 || count($posts) < 1) {
    die("❌ Necesitas tener al menos 1 usuario y 1 publicación creados.");
}

// 2. LISTA DE COMENTARIOS
$textos = [
    "¡Qué buena foto! 📸", "Me encanta esto 😍", "Totalmente de acuerdo.", 
    "Increíble 🔥", "¿Dónde es esto?", "Jajaja buenísimo 😂", 
    "Top 🔝", "Interesante reflexión.", "¡Saludos!", 
    "Vaya fotaza", "Muy fan", "Brutal 🚀", "Claro que sí", 
    "No lo sabía, gracias por compartir.", "Wow 😯"
];

$likesInsertados = 0;
$comentsInsertados = 0;

// 3. RECORRER CADA PUBLICACIÓN
foreach ($posts as $p) {
    $idPost = $p['id_publicacion'];

    // --- A) GENERAR LIKES (tipo_interaccion = 'LIKE') ---
    $numLikes = rand(0, 8); // Entre 0 y 8 likes por post
    shuffle($usuarios);     // Mezclar usuarios
    
    for ($i = 0; $i < $numLikes; $i++) {
        if (!isset($usuarios[$i])) break;
        
        $idUser = $usuarios[$i]['id_usuario'];
        
        // CORRECCIÓN: Usamos tus nombres de columna exactos
        $sql = "INSERT IGNORE INTO interaccion (id_usuario, id_publicacion, tipo_interaccion, fecha_creacion) 
                VALUES ($idUser, $idPost, 'LIKE', NOW() - INTERVAL FLOOR(RAND() * 10) DAY)";
        
        try {
            if ($mysqli->query($sql)) {
                if ($mysqli->affected_rows > 0) $likesInsertados++;
            }
        } catch (Exception $e) {
            // Ignorar duplicados
        }
    }

    // --- B) GENERAR COMENTARIOS (tipo_interaccion = 'COMENTARIO') ---
    $numComents = rand(0, 3); // Entre 0 y 3 comentarios por post
    shuffle($usuarios);

    for ($j = 0; $j < $numComents; $j++) {
        if (!isset($usuarios[$j])) break;

        $idUser = $usuarios[$j]['id_usuario'];
        $textoRandom = $textos[array_rand($textos)];
        
        // CORRECCIÓN: Usamos 'comentario' para el texto y 'tipo_interaccion' para el tipo
        $sql = "INSERT INTO interaccion (id_usuario, id_publicacion, tipo_interaccion, comentario, fecha_creacion) 
                VALUES ($idUser, $idPost, 'COMENTARIO', '$textoRandom', NOW() - INTERVAL FLOOR(RAND() * 5) DAY)";
        
        try {
            if ($mysqli->query($sql)) $comentsInsertados++;
        } catch (Exception $e) {
            // Ignorar errores
        }
    }
}

echo "<h3>✅ Proceso terminado con éxito</h3>";
echo "<ul>";
echo "<li>Likes generados: <strong>$likesInsertados</strong></li>";
echo "<li>Comentarios generados: <strong>$comentsInsertados</strong></li>";
echo "</ul>";
echo "<a href='index.php'>Volver al Inicio</a>";
?>