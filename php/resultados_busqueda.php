<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

// Obtener término de búsqueda y ID del usuario actual
$busqueda = $_GET['q'] ?? '';
$miId = (int)($_SESSION['id_usuario'] ?? 0);

// Si no hay búsqueda, redirigir al inicio
if (empty($busqueda)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados: <?php echo htmlspecialchars($busqueda); ?> - NeonNest</title>
</head>
<body>
    <main class="contenido-principal">
        <header class="cabecera">
            <div class="cabecera-left">
                <h1>Resultados</h1>
                <p class="cabecera-sub">Búsqueda: "<strong><?php echo htmlspecialchars($busqueda); ?></strong>"</p>
            </div>
        </header>

        <div class="resultados-container" style="padding: 20px;">
            
            <h3 style="margin-bottom:15px; border-bottom:1px solid var(--border); padding-bottom:10px; color:var(--text);">👥 Usuarios</h3>
            <div class="lista-usuarios-busqueda">
                <?php
                // Busca usuarios que coincidan con el nombre y NO seas tú
                $sqlUser = "SELECT id_usuario, usuario, foto_perfil FROM usuario WHERE usuario LIKE ? AND id_usuario != ? LIMIT 5";
                $stmt = $mysqli->prepare($sqlUser);
                
                if ($stmt) {
                    $param = "%" . $busqueda . "%";
                    $stmt->bind_param('si', $param, $miId);
                    $stmt->execute();
                    $resUser = $stmt->get_result();

                    if ($resUser->num_rows > 0) {
                        while ($u = $resUser->fetch_assoc()) {
                            $uNombre = htmlspecialchars($u['usuario']);
                            
                            // --- CAMBIO 1: FOTO USUARIO A BASE64 ---
                            $img = '';
                            if (!empty($u['foto_perfil'])) {
                                $base64 = base64_encode($u['foto_perfil']);
                                $img = 'data:image/jpeg;base64,' . $base64;
                            }
                            
                            $inicial = strtoupper(substr($uNombre, 0, 1));
                            ?>
                            <div class="follow-row" style="background: var(--card2); padding: 0; border-radius: 10px; margin-bottom: 10px; overflow:hidden; border:1px solid var(--border);">
                                <a href="#" class="user-link" data-user="<?php echo $uNombre; ?>" style="display:flex; align-items:center; text-decoration:none; color:inherit; width:100%; padding: 10px;">
                                    <?php if ($img): ?>
                                        <img src="<?php echo $img; ?>" alt="Avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;">
                                    <?php else: ?>
                                        <div style="width:40px; height:40px; border-radius:50%; background:var(--bg); color:var(--text); display:flex; align-items:center; justify-content:center; margin-right:10px; font-weight:bold;"><?php echo $inicial; ?></div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <div style="font-weight:bold; color:var(--text);">@<?php echo $uNombre; ?></div>
                                        <small style="color:var(--muted);">Ver perfil</small>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p style='color:var(--muted);'>No se encontraron usuarios.</p>";
                    }
                    $stmt->close();
                }
                ?>
            </div>

            <h3 style="margin-top:30px; margin-bottom:15px; border-bottom:1px solid var(--border); padding-bottom:10px; color:var(--text);">📝 Publicaciones</h3>
            <div class="feed-busqueda">
                <?php
                // CONSULTA DE PUBLICACIONES
                $sqlPost = "
                    SELECT 
                        p.id_publicacion,
                        p.texto,
                        p.imagen,
                        p.fecha_publicacion,
                        u.usuario, 
                        u.foto_perfil,
                        (SELECT COUNT(*) 
                         FROM interaccion i 
                         WHERE i.id_publicacion = p.id_publicacion 
                         AND i.tipo_interaccion = 'LIKE') as num_likes,
                        (SELECT COUNT(*) 
                         FROM interaccion i 
                         WHERE i.id_publicacion = p.id_publicacion 
                         AND i.id_usuario = ? 
                         AND i.tipo_interaccion = 'LIKE') as liked_by_me,
                        (SELECT COUNT(*) 
                         FROM interaccion i 
                         WHERE i.id_publicacion = p.id_publicacion 
                         AND i.tipo_interaccion = 'COMENTARIO') as num_comentarios
                    FROM publicacion p
                    JOIN usuario u ON p.id_usuario = u.id_usuario
                    WHERE (p.texto LIKE ? OR u.usuario LIKE ?) 
                    ORDER BY p.fecha_publicacion DESC
                    LIMIT 20
                ";
                
                $stmtP = $mysqli->prepare($sqlPost);
                if ($stmtP) {
                    $paramPost = "%" . $busqueda . "%";
                    $stmtP->bind_param('iss', $miId, $paramPost, $paramPost);
                    $stmtP->execute();
                    $resPost = $stmtP->get_result();

                    if ($resPost->num_rows > 0) {
                        while ($row = $resPost->fetch_assoc()) {
                            // Variables básicas
                            $postId = $row['id_publicacion'];
                            $pUser = htmlspecialchars($row['usuario']);
                            $textoRaw = htmlspecialchars($row['texto'] ?? '');
                            $pContenido = nl2br($textoRaw);
                            $pFecha = date('d M H:i', strtotime($row['fecha_publicacion']));
                            
                            // --- CAMBIO 2: IMÁGENES A BASE64 ---
                            
                            // A. Foto perfil autor
                            $pFoto = '';
                            if (!empty($row['foto_perfil'])) {
                                $base64P = base64_encode($row['foto_perfil']);
                                $pFoto = 'data:image/jpeg;base64,' . $base64P;
                            }

                            // B. Imagen del post
                            $pImgPost = '';
                            if (!empty($row['imagen'])) {
                                $base64Img = base64_encode($row['imagen']);
                                $pImgPost = 'data:image/jpeg;base64,' . $base64Img;
                            }
                            
                            // Likes
                            $numLikes = $row['num_likes'] ?? 0;
                            $numComents = $row['num_comentarios'] ?? 0;
                            $isLiked = ($row['liked_by_me'] ?? 0) > 0;
                            $heartClass = $isLiked ? 'fas fa-heart' : 'far fa-heart';
                            $colorStyle = $isLiked ? 'color: #e0245e;' : 'color: var(--muted);';

                            // RENDERIZADO
                            ?>
                            <article class="publicaciones post" 
                                     style="background:var(--card); padding:15px; border-radius:12px; margin-bottom:15px; border:1px solid var(--border); cursor:pointer; transition: background 0.2s;"
                                     data-id="<?php echo $postId; ?>"
                                     data-usuario="<?php echo $pUser; ?>"
                                     data-fecha="<?php echo $pFecha; ?>"
                                     data-texto="<?php echo $textoRaw; ?>"
                                     data-img="<?php echo $pImgPost; ?>"
                                     data-pie=""
                                     data-ubicacion="">
                                
                                <div class="post-header" style="display:flex; gap:10px; margin-bottom:10px;">
                                    <?php if($pFoto): ?>
                                        <img src="<?php echo $pFoto; ?>" class="stop-prop" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                    <?php else: ?>
                                        <div class="stop-prop" style="width:40px; height:40px; border-radius:50%; background:var(--card2);"></div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <a href="#" class="user-link stop-prop" data-user="<?php echo $pUser; ?>" style="color:var(--text); font-weight:bold; text-decoration:none;">
                                            @<?php echo $pUser; ?>
                                        </a>
                                        <div style="color:var(--muted); font-size:0.8rem;"><?php echo $pFecha; ?></div>
                                    </div>
                                </div>
                                
                                <div class="post-body" style="color:var(--text); line-height:1.5;">
                                    <?php echo $pContenido; ?>
                                </div>
                                
                                <?php if ($pImgPost): ?>
                                    <div class="post-img" style="margin-top:10px;">
                                        <img src="<?php echo $pImgPost; ?>" style="max-width:100%; border-radius:8px;">
                                    </div>
                                <?php endif; ?>

                                <div class="post-footer" style="margin-top:15px; padding-top:10px; border-top:1px solid var(--border); display:flex; gap:20px;">
                                    <button class="btn-like-inline stop-prop" data-id="<?php echo $postId; ?>" data-liked="<?php echo $isLiked?'1':'0'; ?>" style="background:none; border:none; cursor:pointer; font-size:1.1rem; <?php echo $colorStyle; ?>">
                                        <i class="<?php echo $heartClass; ?>"></i> <span class="count-like"><?php echo $numLikes > 0 ? $numLikes : ''; ?></span>
                                    </button>
                                    
                                    <button class="btn-comment-inline stop-prop" data-id="<?php echo $postId; ?>" style="background:none; border:none; cursor:pointer; color:var(--muted); font-size:1.1rem;">
                                        <i class="far fa-comment"></i> <span class="count-comment"><?php echo $numComents > 0 ? $numComents : ''; ?></span>
                                    </button>
                                </div>

                                <div class="inline-comment-box stop-prop" id="comment-box-<?php echo $postId; ?>" style="display:none; margin-top:10px; border-top:1px solid var(--border); padding-top:10px;">
                                    <form class="form-inline-comment" data-id="<?php echo $postId; ?>" style="display:flex; gap:10px;">
                                        <input type="text" name="texto" placeholder="Postea tu respuesta" style="flex:1; background:var(--bg); border:1px solid var(--border); color:var(--text); padding:8px 12px; border-radius:20px; outline:none;">
                                        <button type="submit" style="background:var(--accent); color:#fff; border:none; padding:6px 15px; border-radius:20px; cursor:pointer; font-weight:bold;">Responder</button>
                                    </form>
                                </div>
                            </article>
                            <?php
                        }
                    } else {
                        echo "<p style='color:var(--muted);'>No hay publicaciones que coincidan con la búsqueda.</p>";
                    }
                    $stmtP->close();
                } else {
                    echo "<p style='color:red;'>Error al buscar publicaciones: " . $mysqli->error . "</p>";
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>