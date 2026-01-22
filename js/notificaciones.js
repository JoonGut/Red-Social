// js/notificaciones.js

(function() { // Función de aislamiento (IIFE)
    
    // Evita que el script corra dos veces si se incluye múltiple
    if (window.NotificacionesIniciadas) return; 
    window.NotificacionesIniciadas = true;

    console.log("🔔 Script de notificaciones cargado.");

    let lastNotiId = 0; 
    let pollingInterval = null;

    // Esperar a que el HTML exista
    document.addEventListener('DOMContentLoaded', () => {
        console.log("🔔 DOM Cargado. Buscando elementos...");

        const btn = document.getElementById('btnNoti');
        const lista = document.getElementById('listaNoti');
        const badge = document.getElementById('badgeNoti');
        const content = document.getElementById('contenidoNoti');

        if (!btn) console.error("❌ ERROR CRÍTICO: No encuentro el botón con id='btnNoti'");
        if (!lista) console.error("❌ ERROR CRÍTICO: No encuentro el div con id='listaNoti'");

        if (btn && lista) {
            // 1. CLIC EN EL BOTÓN
            btn.addEventListener('click', (e) => {
                console.log("🔔 Click en campana.");
                e.stopPropagation(); // Evita que el clic llegue al documento
                
                // Toggle simple
                if (lista.style.display === 'block') {
                    lista.style.display = 'none';
                } else {
                    lista.style.display = 'block';
                    console.log("🔔 Abriendo menú...");
                    
                    // Ocultar badge visualmente
                    if(badge) badge.style.display = 'none';
                    
                    // Llamar al servidor
                    cargarDatos(true); // true = marcar como leídas
                }
            });

            // 2. CLIC FUERA (Cerrar)
            document.addEventListener('click', (e) => {
                if (lista.style.display === 'block') {
                    if (!lista.contains(e.target) && !btn.contains(e.target)) {
                        lista.style.display = 'none';
                    }
                }
            });

            // Evitar que clic dentro de la lista la cierre
            lista.addEventListener('click', (e) => e.stopPropagation());
        }

        // 3. FUNCIÓN DE CARGA
        async function cargarDatos(marcarLeidas = false) {
            if (!content) return;

            try {
                // Si hay que marcar leídas, lanzamos petición en segundo plano
                if (marcarLeidas) {
                    fetch('get_notificaciones.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'marcar_leidas=1'
                    }).catch(e => console.log(e));
                }

                // Pedir datos
                const res = await fetch('get_notificaciones.php');
                const json = await res.json();

                if (!json.ok) return;

                // Badge
                if (badge && !marcarLeidas) {
                    if (json.sin_leer > 0) {
                        badge.style.display = 'block';
                        badge.textContent = json.sin_leer;
                    } else {
                        badge.style.display = 'none';
                    }
                }

                // Popup Toast
                if (json.items.length > 0) {
                    const latest = Number(json.items[0].id_notificacion);
                    if (lastNotiId > 0 && latest > lastNotiId) {
                        showToast(json.items[0]);
                    }
                    lastNotiId = latest;
                }

                // Renderizar (Solo si está visible para ahorrar)
                if (lista && lista.style.display === 'block') {
                    content.innerHTML = '';
                    if (json.items.length === 0) {
                        content.innerHTML = '<div style="padding:15px; text-align:center; color:#777">No hay notificaciones.</div>';
                    } else {
                        json.items.forEach(n => {
                            const div = document.createElement('div');
                            div.className = `noti-item ${n.leido == 0 ? 'sin-leer' : ''}`;
                            div.style.padding = '10px';
                            div.style.borderBottom = '1px solid #333';
                            div.style.cursor = 'pointer';
                            div.style.display = 'flex'; 
                            div.style.gap = '10px';
                            div.style.alignItems = 'center';
                            
                            const foto = n.actor_foto ? `../multimedia/${n.actor_foto}` : '../multimedia/file.svg';
                            const jsAction = n.link_accion ? n.link_accion.replace('javascript:', '') : '';
                            
                            div.setAttribute('onclick', jsAction);
                            div.innerHTML = `
                                <img src="${foto}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                                <div>
                                    <strong style="color:#fff">${n.actor_usuario}</strong> 
                                    <span style="color:#ccc; font-size:0.9rem">${n.texto_formato}</span>
                                    ${n.texto_extra ? `<div style="font-size:0.8rem; color:#777; font-style:italic">"${n.texto_extra}"</div>` : ''}
                                </div>
                            `;
                            content.appendChild(div);
                        });
                    }
                }
            } catch (e) {
                console.error("Error cargando notificaciones:", e);
            }
        }

        // 4. TOAST
        function showToast(n) {
            const container = document.getElementById('toastContainer');
            if(!container) return;
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<strong>@${n.actor_usuario}</strong> ${n.texto_formato}`;
            container.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 4000);
        }

        // Arrancar ciclo
        cargarDatos();
        setInterval(() => cargarDatos(), 5000);
    });

})();