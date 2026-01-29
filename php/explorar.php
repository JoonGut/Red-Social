<?php
session_start();
require __DIR__ . '/db.php';

// Capturamos el término de búsqueda (si existe)
$busqueda = trim((string)($_GET['q'] ?? ''));

// SQL Base
$sqlBase = "SELECT p.*, u.usuario, u.foto_perfil, 
            (SELECT COUNT(*) FROM interaccion i WHERE i.id_publicacion = p.id_publicacion) as num_likes
            FROM publicacion p
            JOIN usuario u ON p.id_usuario = u.id_usuario";

if ($busqueda !== '') {
    // BÚSQUEDA ESPECÍFICA
    $sql = "$sqlBase WHERE p.texto LIKE ? OR u.usuario LIKE ? ORDER BY p.fecha_publicacion DESC LIMIT 20";
    $stmt = $mysqli->prepare($sql);
    $term = "%$busqueda%";
    $stmt->bind_param('ss', $term, $term);
} else {
    // MODO TENDENCIAS
    // Ordenar por popularidad (num_likes) y luego por fecha
    // Filtramos que la imagen NO sea NULL o vacía (BLOB con contenido)
    $sql = "$sqlBase WHERE p.imagen IS NOT NULL AND LENGTH(p.imagen) > 0 ORDER BY num_likes DESC, p.fecha_publicacion DESC LIMIT 21";
    $stmt = $mysqli->prepare($sql);
}

$stmt->execute();
$res = $stmt->get_result();
$posts = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Explorar</title>
</head>
<body>
    <main class="contenido-principal">
        
        <header style="padding: 20px 20px 0 20px;">
            <h1 style="margin:0 0 15px 0;">🌍 Explorar</h1>
            
            <form onsubmit="event.preventDefault(); window.realizarBusquedaExplorar(this.querySelector('input').value);" 
                  style="display:flex; gap:10px;">
                <input type="search" 
                       placeholder="Buscar tendencias, personas o temas..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>"
                       style="flex:1; padding:12px 20px; border-radius:30px; border:1px solid var(--border); background:var(--card2); color:var(--text); outline:none;">
            </form>
        </header>

        <section style="padding: 20px;">
            <div onclick="window.location.href='../libreria/index.html'" 
                 style="
                    background: linear-gradient(135deg, #6c5ce7, #a29bfe); 
                    border-radius: 15px; 
                    padding: 20px; 
                    color: white; 
                    display:flex; 
                    align-items:center; 
                    justify-content:space-between; 
                    cursor:pointer; 
                    box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
                    transition: transform 0.2s;
                  "
                 onmouseover="this.style.transform='scale(1.02)'"
                 onmouseout="this.style.transform='scale(1)'">
                <div>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                        <span style="font-size:1.5rem;">📚</span>
                        <h3 style="margin:0; font-size:1.2rem;">NeonBooks</h3>
                    </div>
                    <p style="margin:0; font-size:0.9rem; opacity:0.9;">Accede a nuestra librería oficial y descubre nuevas historias.</p>
                </div>
                <div style="background:rgba(255,255,255,0.2); padding:10px; border-radius:50%; width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
                    ➔
                </div>
            </div>
        </section>

        <section style="padding: 0 20px 50px;">
            <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:15px;">
                <h3 style="margin:0; color:var(--text);">
                    <?php echo $busqueda ? 'Resultados' : 'Tendencias 🔥'; ?>
                </h3>
                <?php if (!$busqueda): ?>
                    <small style="color:var(--muted);">Lo más viral hoy</small>
                <?php endif; ?>
            </div>

            <?php if (count($posts) === 0): ?>
                <div style="text-align:center; padding:40px; color:var(--muted); background:var(--card2); border-radius:10px;">
                    <p>No se encontraron resultados.</p>
                </div>
            <?php else: ?>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px;">
                    <?php foreach ($posts as $index => $p): 
                        
                        // --- CORRECCIÓN: IMAGEN BLOB A BASE64 ---
                        $img = '';
                        if (!empty($p['imagen'])) {
                            $base64 = base64_encode($p['imagen']);
                            $img = 'data:image/jpeg;base64,' . $base64;
                        }
                        
                        $hasImg = !empty($img);
                        
                        $esTop1 = ($index === 0 && !$busqueda); 
                        $borde = $esTop1 ? '2px solid #ffd32a' : '1px solid var(--border)';
                    ?>
                        <div onclick="window.cargarVistaPublicacion(<?php echo $p['id_publicacion']; ?>)"
                             class="post-preview-click"
                             data-id="<?php echo $p['id_publicacion']; ?>"
                             style="
                                cursor: pointer; 
                                aspect-ratio: 1/1; 
                                background: var(--card2); 
                                border-radius: 5px; 
                                overflow: hidden; 
                                position: relative;
                                border: <?php echo $borde; ?>;
                             ">
                            
                            <?php if ($hasImg): ?>
                                <img src="<?php echo $img; ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.3s;"
                                     onmouseover="this.style.transform='scale(1.1)'"
                                     onmouseout="this.style.transform='scale(1)'">
                                
                                <div style="
                                    position:absolute; 
                                    bottom:0; left:0; right:0; 
                                    background:linear-gradient(to top, rgba(0,0,0,0.8), transparent); 
                                    color:white; 
                                    padding:8px 5px 5px; 
                                    font-size:0.75rem; 
                                    display:flex; 
                                    align-items:center; 
                                    gap:4px;
                                    font-weight:bold;">
                                    ❤️ <?php echo $p['num_likes']; ?>
                                </div>

                                <?php if($esTop1): ?>
                                    <div style="position:absolute; top:5px; right:5px; background:#ffd32a; color:black; font-size:0.7rem; padding:2px 6px; border-radius:10px; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.2);">
                                        #1
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div style="padding:10px; height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; font-size:0.7rem; color:var(--text); background:var(--card);">
                                    <div style="margin-bottom:5px; color:var(--accent); font-weight:bold;">@<?php echo htmlspecialchars($p['usuario']); ?></div>
                                    "<?php echo htmlspecialchars(mb_strimwidth($p['texto'], 0, 40, '...')); ?>"
                                    <div style="margin-top:5px; color:var(--muted);">❤️ <?php echo $p['num_likes']; ?></div>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <script>
            window.realizarBusquedaExplorar = function(termino) {
                const contenedor = document.querySelector('.contenido-principal');
                fetch(`explorar.php?q=${encodeURIComponent(termino)}`)
                    .then(r => r.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newMain = doc.querySelector('.contenido-principal');
                        if(newMain) {
                            contenedor.innerHTML = newMain.innerHTML;
                        }
                    });
            };
        </script>
    </main>
</body>
</html>