<?php
session_start();
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 2) {
    die("<h2 style='color:red; text-align:center; padding:50px;'>⛔ Acceso restringido</h2>");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Admin</title>
</head>
<body>
    <main class="contenido-principal">
        
        <div class="admin-container" style="padding:20px; max-width:1000px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h1>🛡️ Panel de Administración</h1>
                <div style="background:#ff4757; color:white; padding:5px 15px; border-radius:20px; font-weight:bold; font-size:0.8rem;">
                    ADMIN
                </div>
            </div>

            <div class="admin-tabs" style="display:flex; gap:10px; margin-bottom:20px;">
                <button onclick="window.cargarAdminUsuarios(1)" class="boton-registrarse" style="flex:1;">
                    👥 Usuarios
                </button>
                <button onclick="window.cargarAdminPosts(1)" class="boton-registrarse" style="flex:1; background:var(--card2); color:var(--text); border:1px solid var(--border);">
                    📝 Posts
                </button>
            </div>

            <div id="admin-view-content" style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:20px; min-height:300px;">
                <p style="text-align:center; color:var(--muted); margin-top:50px;">Cargando...</p>
            </div>
            
            <div id="admin-pagination" style="display:flex; justify-content:center; gap:15px; margin-top:20px;"></div>
        </div>

        <script>
            window.paginaUsuariosActual = 1;
            window.paginaPostsActual = 1;

            // --- 1. CARGAR USUARIOS (Clickable) ---
            window.cargarAdminUsuarios = async function(pagina = 1) {
                window.paginaUsuariosActual = pagina;
                const div = document.getElementById('admin-view-content');
                const pagDiv = document.getElementById('admin-pagination');
                
                div.innerHTML = '<div style="text-align:center; padding:20px;">Cargando usuarios...</div>';
                pagDiv.innerHTML = '';

                try {
                    const res = await fetch(`api_admin.php?accion=listar_usuarios&pagina=${pagina}`);
                    const data = await res.json();
                    
                    if(data.items.length === 0) {
                        div.innerHTML = '<p>No hay usuarios.</p>';
                        return;
                    }

                    let html = `
                    <table style="width:100%; border-collapse:collapse; color:var(--text);">
                        <thead style="border-bottom:2px solid var(--border); text-align:left;">
                            <tr>
                                <th style="padding:10px;">ID</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th style="text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>`;
                    
                    data.items.forEach(u => {
                        const esAdmin = (u.id_rol == 2);
                        const badge = esAdmin 
                            ? '<span style="color:#ff4757; font-weight:bold;">ADMIN</span>' 
                            : '<span style="color:var(--muted);">Usuario</span>';
                        
                        // IMPORTANTE: event.stopPropagation() evita entrar al perfil al borrar
                        const btnBorrar = esAdmin ? '' : `
                            <button onclick="event.stopPropagation(); window.borrarUsuario(${u.id_usuario})" 
                                    style="background:transparent; border:1px solid #ff4757; color:#ff4757; padding:4px 8px; border-radius:5px; cursor:pointer; font-size:0.8rem;">
                                🗑️
                            </button>`;

                        // AÑADIDO: onclick en el <tr> para ir al perfil
                        html += `
                        <tr onclick="window.loadUserProfile('${u.usuario}')" 
                            style="border-bottom:1px solid var(--border); cursor:pointer; transition:background 0.2s;"
                            onmouseover="this.style.background='var(--card2)'" 
                            onmouseout="this.style.background='transparent'">
                            
                            <td style="padding:12px 10px;">#${u.id_usuario}</td>
                            <td>
                                <strong>@${u.usuario}</strong><br>
                                <small style="color:var(--muted);">${u.email}</small>
                            </td>
                            <td>${badge}</td>
                            <td style="text-align:right;">${btnBorrar}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    div.innerHTML = html;

                    renderPagination(data.paginaActual, data.totalPaginas, 'usuarios');

                } catch(e) {
                    console.error(e);
                    div.innerHTML = '<p style="color:red">Error cargando usuarios.</p>';
                }
            };

            // --- 2. CARGAR POSTS (Clickable) ---
            window.cargarAdminPosts = async function(pagina = 1) {
                window.paginaPostsActual = pagina;
                const div = document.getElementById('admin-view-content');
                const pagDiv = document.getElementById('admin-pagination');
                
                div.innerHTML = '<div style="text-align:center; padding:20px;">Cargando posts...</div>';
                pagDiv.innerHTML = '';

                try {
                    const res = await fetch(`api_admin.php?accion=listar_posts&pagina=${pagina}`);
                    const data = await res.json();
                    
                    if(data.items.length === 0) {
                        div.innerHTML = '<p>No hay posts.</p>';
                        return;
                    }

                    let html = '<div style="display:flex; flex-direction:column; gap:10px;">';
                    data.items.forEach(p => {
                        // AÑADIDO: onclick en el div principal para ir al post
                        html += `
                        <div onclick="window.cargarVistaPublicacion(${p.id_publicacion})"
                             style="display:flex; justify-content:space-between; align-items:flex-start; padding:15px; background:var(--card2); border-radius:8px; cursor:pointer; transition:transform 0.1s;"
                             onmouseover="this.style.transform='translateX(5px)'"
                             onmouseout="this.style.transform='translateX(0)'">
                            
                            <div>
                                <strong style="color:var(--accent);">@${p.usuario}</strong> 
                                <span style="font-size:0.8rem; color:var(--muted);"> · ${p.fecha_publicacion}</span>
                                <p style="margin:5px 0; color:var(--text); font-size:0.9rem;">${p.texto.substring(0, 100)}...</p>
                            </div>
                            
                            <button onclick="event.stopPropagation(); window.borrarPostAdmin(${p.id_publicacion})" 
                                    style="background:#ff4757; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:0.8rem; margin-left:10px; z-index:2;">
                                Eliminar
                            </button>
                        </div>`;
                    });
                    html += '</div>';
                    div.innerHTML = html;

                    renderPagination(data.paginaActual, data.totalPaginas, 'posts');

                } catch(e) {
                    div.innerHTML = '<p style="color:red">Error cargando posts.</p>';
                }
            };

            // --- 3. HELPER PAGINACIÓN ---
            function renderPagination(actual, total, tipo) {
                if (total <= 1) return; 

                const pagDiv = document.getElementById('admin-pagination');
                const fn = tipo === 'usuarios' ? 'window.cargarAdminUsuarios' : 'window.cargarAdminPosts';

                let html = '';
                
                if (actual > 1) {
                    html += `<button onclick="${fn}(${actual - 1})" class="boton-registrarse" style="padding:5px 15px; font-size:0.9rem;">« Anterior</button>`;
                } else {
                    html += `<button disabled class="boton-registrarse" style="padding:5px 15px; font-size:0.9rem; opacity:0.5; cursor:not-allowed;">« Anterior</button>`;
                }

                html += `<span style="display:flex; align-items:center; font-weight:bold; color:var(--text);">Página ${actual} de ${total}</span>`;

                if (actual < total) {
                    html += `<button onclick="${fn}(${actual + 1})" class="boton-registrarse" style="padding:5px 15px; font-size:0.9rem;">Siguiente »</button>`;
                } else {
                    html += `<button disabled class="boton-registrarse" style="padding:5px 15px; font-size:0.9rem; opacity:0.5; cursor:not-allowed;">Siguiente »</button>`;
                }

                pagDiv.innerHTML = html;
            }

            // --- 4. ACCIONES BORRAR ---
            window.borrarUsuario = async function(id) {
                if(!confirm('¿Eliminar usuario y TODOS sus datos?')) return;
                const fd = new URLSearchParams(); fd.append('id', id);
                await fetch('api_admin.php?accion=borrar_usuario', { method:'POST', body:fd });
                window.cargarAdminUsuarios(window.paginaUsuariosActual);
            };

            window.borrarPostAdmin = async function(id) {
                if(!confirm('¿Eliminar post?')) return;
                const fd = new URLSearchParams(); fd.append('id', id);
                await fetch('api_admin.php?accion=borrar_post', { method:'POST', body:fd });
                window.cargarAdminPosts(window.paginaPostsActual);
            };

            // Cargar usuarios por defecto
            window.cargarAdminUsuarios(1);
        </script>
    </main>
</body>
</html>