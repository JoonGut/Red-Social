// js/notificaciones.js

(function() { // Función de aislamiento (IIFE)
    
    // Evita que el script corra dos veces si se incluye múltiple
    if (window.NotificacionesIniciadas) return; 
    window.NotificacionesIniciadas = true;

    console.log("🔔 Script de notificaciones cargado.");

    let lastNotiId = 0; 
    let pollingInterval = null;

    // Helper para imágenes (Igual que en chat.js)
    const MEDIA = (p) => {
        if (!p) return "../multimedia/file.svg";
        if (String(p).startsWith("data:")) return p; // Ya es Base64
        return "../multimedia/" + p; // Legacy archivo
    };

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
            // No requerimos 'content' para pedir datos (para el badge), pero sí para pintar lista
            
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
                const text = await res.text(); // Leemos texto primero para depurar errores PHP
                
                let json;
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    console.error("Error JSON Notificaciones:", text);
                    return;
                }

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

                // Popup Toast (Solo si es nueva y no estamos marcando leídas)
                if (json.items.length > 0 && !marcarLeidas) {
                    const latest = Number(json.items[0].id_notificacion);
                    // Solo mostramos toast si hay una ID mayor que la última conocida y no es la primera carga (lastNotiId > 0)
                    if (lastNotiId > 0 && latest > lastNotiId) {
                        showToast(json.items[0]);
                    }
                    lastNotiId = latest;
                } else if (json.items.length > 0) {
                     // Primera carga o refresco, actualizamos ID pero no mostramos toast
                     lastNotiId = Number(json.items[0].id_notificacion);
                }

                // Renderizar Lista (Solo si el contenedor existe y la lista está visible)
                if (content && lista && lista.style.display === 'block') {
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
                            
                            // USAR LA FUNCIÓN MEDIA PARA BASE64
                            const foto = MEDIA(n.actor_foto);
                            
                            // Limpiar javascript: del enlace por seguridad básica
                            let action = n.link_accion || "";
                            if(action.startsWith("javascript:")) {
                                div.setAttribute('onclick', action); // Si es función JS global
                            } else if (action) {
                                div.onclick = () => window.location.href = action; // Si es URL
                            }
                            
                            div.innerHTML = `
                                <img src="${foto}" style="width:35px; height:35px; border-radius:50%; object-fit:cover; background:var(--card2);">
                                <div>
                                    <strong style="color:var(--text)">${n.actor_usuario}</strong> 
                                    <span style="color:var(--muted); font-size:0.9rem">${n.texto_formato}</span>
                                    ${n.texto_extra ? `<div style="font-size:0.8rem; color:var(--muted); font-style:italic">"${n.texto_extra}"</div>` : ''}
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
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '10px';
            
            // Foto en el toast también
            const foto = MEDIA(n.actor_foto);
            
            toast.innerHTML = `
                <img src="${foto}" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
                <div>
                    <strong>@${n.actor_usuario}</strong> ${n.texto_formato}
                </div>
            `;
            
            container.appendChild(toast);
            
            // Animación entrada
            requestAnimationFrame(() => toast.classList.add('show'));
            
            setTimeout(() => { 
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300); // Esperar transición CSS
            }, 4000);
        }

        // Arrancar ciclo
        cargarDatos();
        setInterval(() => cargarDatos(), 5000);
    });

})();