<?php
session_start();

// --- ZONA DE DIAGNÓSTICO (Descomenta esto si sigue saliendo Acceso Restringido) ---
/*
echo "<div style='background:white; padding:20px; z-index:9999; position:relative;'>";
echo "<h3>DEBUG SESIÓN:</h3>";
echo "ID ROL: " . (isset($_SESSION['id_rol']) ? $_SESSION['id_rol'] : 'No definido') . "<br>";
echo "Datos completos: "; var_dump($_SESSION);
echo "</div>";
*/
// ----------------------------------------------------------------------------------

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
                <button onclick="window.cambiarTabAdmin('usuarios')" id="btnTabUsuarios" class="boton-registrarse" style="flex:1;">
                    👥 Usuarios
                </button>
                <button onclick="window.cambiarTabAdmin('posts')" id="btnTabPosts" class="boton-registrarse" style="flex:1; background:var(--card2); color:var(--text); border:1px solid var(--border);">
                    📝 Posts
                </button>
                <button onclick="window.cambiarTabAdmin('sistema')" id="btnTabSistema" class="boton-registrarse" style="flex:1; background:var(--card2); color:var(--text); border:1px solid var(--border);">
                    💻 Sistema
                </button>
            </div>

            <div id="containerBuscadorAdmin" style="margin-bottom:20px; display:flex; gap:10px;">
                <input type="search"
                    id="inputBuscadorAdmin"
                    placeholder="Buscar usuario o email..."
                    style="flex:1; padding:12px; border-radius:10px; border:1px solid var(--border); background:var(--card2); color:var(--text); outline:none;"
                    onkeypress="if(event.key === 'Enter') window.realizarBusquedaAdmin()">

                <button onclick="window.realizarBusquedaAdmin()" class="boton-registrarse" style="padding:0 20px;">
                    🔍
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
            window.tabActual = 'usuarios';
            window.busquedaActual = '';

            // --- 1. GESTIÓN DE TABS ---
            window.cambiarTabAdmin = function(tab) {
                window.tabActual = tab;
                window.busquedaActual = '';
                const inputBusq = document.getElementById('inputBuscadorAdmin');
                const divBusq = document.getElementById('containerBuscadorAdmin');
                if (inputBusq) inputBusq.value = '';

                // Reset estilos botones
                const btns = ['btnTabUsuarios', 'btnTabPosts', 'btnTabSistema'];
                btns.forEach(id => {
                    const b = document.getElementById(id);
                    b.style.background = 'var(--card2)';
                    b.style.color = 'var(--text)';
                    b.style.border = '1px solid var(--border)';
                });

                // Estilo Activo
                let activeBtn;
                if (tab === 'usuarios') activeBtn = document.getElementById('btnTabUsuarios');
                else if (tab === 'posts') activeBtn = document.getElementById('btnTabPosts');
                else if (tab === 'sistema') activeBtn = document.getElementById('btnTabSistema');

                if (activeBtn) {
                    activeBtn.style.background = 'var(--accent)';
                    activeBtn.style.color = 'white';
                    activeBtn.style.border = 'none';
                }

                // Lógica por Tab
                if (tab === 'usuarios') {
                    divBusq.style.display = 'flex';
                    inputBusq.placeholder = "Buscar usuario o email...";
                    window.cargarAdminUsuarios(1);
                } else if (tab === 'posts') {
                    divBusq.style.display = 'flex';
                    inputBusq.placeholder = "Buscar contenido o autor...";
                    window.cargarAdminPosts(1);
                } else if (tab === 'sistema') {
                    divBusq.style.display = 'none'; // Ocultar buscador en sistema
                    document.getElementById('admin-pagination').innerHTML = ''; // Sin paginación
                    window.renderTabSistema();
                }
            };

            window.realizarBusquedaAdmin = function() {
                const texto = document.getElementById('inputBuscadorAdmin').value.trim();
                window.busquedaActual = texto;
                if (window.tabActual === 'usuarios') window.cargarAdminUsuarios(1);
                else if (window.tabActual === 'posts') window.cargarAdminPosts(1);
            };

            // --- 2. RENDERIZADO DE TAB SISTEMA (GIT PULL) ---
            window.renderTabSistema = function() {
                const div = document.getElementById('admin-view-content');
                div.innerHTML = `
                    <div style="text-align:center; padding:40px;">
                        <h2 style="margin-bottom:20px; color:var(--text);">⚙️ Mantenimiento del Servidor</h2>
                        <p style="color:var(--muted); margin-bottom:30px;">
                            Utiliza estas herramientas con precaución. Las acciones afectan al servidor en tiempo real.
                        </p>
                        
                        <div style="display:inline-block; border:1px solid var(--border); padding:30px; border-radius:15px; background:var(--card2);">
                            <div style="font-size:3rem; margin-bottom:15px;">🚀</div>
                            <h3 style="margin-bottom:10px;">Actualizar Código (Git Pull)</h3>
                            <p style="font-size:0.9rem; color:var(--muted); margin-bottom:20px;">
                                Descarga los últimos cambios del repositorio 'main'.
                            </p>
                            <button id="btnGitAction" onclick="window.ejecutarGitPull()" class="boton-registrarse" 
                                    style="background:#2ed573; color:white; width:100%; border:none;">
                                Ejecutar Git Pull
                            </button>
                        </div>

                        <div id="git-output-console" style="display:none; margin-top:30px; text-align:left;">
                            <label style="font-weight:bold; color:var(--text);">Salida de la terminal:</label>
                            <pre id="git-output-text" style="background:#1e272e; color:#00d2d3; padding:15px; border-radius:8px; overflow-x:auto; margin-top:10px; font-family:monospace;"></pre>
                        </div>
                    </div>
                `;
            };

            window.ejecutarGitPull = async function() {
                if (!confirm("⚠️ ¿Estás seguro de ejecutar GIT PULL en producción?")) return;

                const btn = document.getElementById('btnGitAction');
                const consoleDiv = document.getElementById('git-output-console');
                const consoleText = document.getElementById('git-output-text');
                
                const txtOriginal = btn.innerHTML;
                btn.innerHTML = '⏳ Ejecutando...';
                btn.disabled = true;
                consoleDiv.style.display = 'none';

                try {
                    // LLAMADA AL ARCHIVO PHP (Asegúrate de crearlo)
                    const res = await fetch('git_pull.php');
                    const data = await res.json();

                    consoleDiv.style.display = 'block';
                    if (data.ok) {
                        consoleText.style.color = '#2ed573'; // Verde
                        consoleText.textContent = "✅ ÉXITO:\n" + data.output;
                    } else {
                        consoleText.style.color = '#ff4757'; // Rojo
                        consoleText.textContent = "❌ ERROR:\n" + (data.msg || data.output || 'Error desconocido');
                    }

                } catch (err) {
                    consoleDiv.style.display = 'block';
                    consoleText.style.color = '#ff4757';
                    consoleText.textContent = "❌ ERROR DE CONEXIÓN: " + err.message;
                } finally {
                    btn.innerHTML = txtOriginal;
                    btn.disabled = false;
                }
            };

            // --- 3. CARGA DE USUARIOS ---
            window.cargarAdminUsuarios = async function(pagina = 1) {
                window.paginaUsuariosActual = pagina;
                const div = document.getElementById('admin-view-content');
                const pagDiv = document.getElementById('admin-pagination');
                div.innerHTML = '<div style="text-align:center; padding:20px;">Cargando usuarios...</div>';
                pagDiv.innerHTML = '';

                try {
                    const searchParam = window.busquedaActual ? `&busqueda=${encodeURIComponent(window.busquedaActual)}` : '';
                    const res = await fetch(`api_admin.php?accion=listar_usuarios&pagina=${pagina}${searchParam}`);
                    const data = await res.json();

                    if (data.items.length === 0) {
                        div.innerHTML = '<p style="text-align:center; padding:20px;">No se encontraron usuarios.</p>';
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
                        const badge = esAdmin ?
                            '<span style="color:#ff4757; font-weight:bold;">ADMIN</span>' :
                            '<span style="color:var(--muted);">Usuario</span>';
                        
                        // Botón borrar (solo si no es admin)
                        const btnBorrar = esAdmin ? '' : `
                            <button onclick="event.stopPropagation(); window.borrarUsuario(${u.id_usuario})" 
                                    style="background:transparent; border:1px solid #ff4757; color:#ff4757; padding:4px 8px; border-radius:5px; cursor:pointer; font-size:0.8rem;">
                                🗑️
                            </button>`;

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

                } catch (e) {
                    console.error(e);
                    div.innerHTML = '<p style="color:red">Error cargando usuarios.</p>';
                }
            };

            // --- 4. CARGA DE POSTS ---
            window.cargarAdminPosts = async function(pagina = 1) {
                window.paginaPostsActual = pagina;
                const div = document.getElementById('admin-view-content');
                const pagDiv = document.getElementById('admin-pagination');
                div.innerHTML = '<div style="text-align:center; padding:20px;">Cargando posts...</div>';
                pagDiv.innerHTML = '';

                try {
                    const searchParam = window.busquedaActual ? `&busqueda=${encodeURIComponent(window.busquedaActual)}` : '';
                    const res = await fetch(`api_admin.php?accion=listar_posts&pagina=${pagina}${searchParam}`);
                    const data = await res.json();

                    if (data.items.length === 0) {
                        div.innerHTML = '<p style="text-align:center; padding:20px;">No se encontraron posts.</p>';
                        return;
                    }

                    let html = '<div style="display:flex; flex-direction:column; gap:10px;">';
                    data.items.forEach(p => {
                        let textoPost = p.texto;
                        html += `
                        <div onclick="window.cargarVistaPublicacion(${p.id_publicacion})"
                             style="display:flex; justify-content:space-between; align-items:flex-start; padding:15px; background:var(--card2); border-radius:8px; cursor:pointer; transition:transform 0.1s;"
                             onmouseover="this.style.transform='translateX(5px)'"
                             onmouseout="this.style.transform='translateX(0)'">
                            <div>
                                <strong style="color:var(--accent);">@${p.usuario}</strong> 
                                <span style="font-size:0.8rem; color:var(--muted);"> · ${p.fecha_publicacion}</span>
                                <p style="margin:5px 0; color:var(--text); font-size:0.9rem;">${textoPost}</p>
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

                } catch (e) {
                    div.innerHTML = '<p style="color:red">Error cargando posts.</p>';
                }
            };

            // --- 5. FUNCIONES AUXILIARES ---
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
                html += `<span style="display:flex; align-items:center; margin-left:10px; font-weight:bold; color:var(--text);">Pág ${actual} / ${total}</span>`;
                if (actual < total) {
                    html += `<button onclick="${fn}(${actual + 1})" class="boton-registrarse" style="padding:5px 15px; margin-left:10px; font-size:0.9rem;">Siguiente »</button>`;
                } else {
                    html += `<button disabled class="boton-registrarse" style="padding:5px 15px; font-size:0.9rem; opacity:0.5; cursor:not-allowed;">Siguiente »</button>`;
                }
                pagDiv.innerHTML = html;
            }

            window.borrarUsuario = async function(id) {
                if (!confirm('¿Eliminar usuario y TODOS sus datos?')) return;
                const fd = new URLSearchParams();
                fd.append('id', id);
                await fetch('api_admin.php?accion=borrar_usuario', { method: 'POST', body: fd });
                window.cargarAdminUsuarios(window.paginaUsuariosActual);
            };

            window.borrarPostAdmin = async function(id) {
                if (!confirm('¿Eliminar post?')) return;
                const fd = new URLSearchParams();
                fd.append('id', id);
                await fetch('api_admin.php?accion=borrar_post', { method: 'POST', body: fd });
                window.cargarAdminPosts(window.paginaPostsActual);
            };

            // Inicializar en Usuarios
            window.cargarAdminUsuarios(1);
        </script>
    </main>
</body>

</html>