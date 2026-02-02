

(function () {
    if (window.NotificacionesIniciadas) return;
    window.NotificacionesIniciadas = true;

    console.log("🔔 Script de notificaciones cargado.");

    let lastNotiId = 0;

    const BASE_URL_PHP = window.location.pathname.includes('/php/') ? 'get_notificaciones.php' : 'php/get_notificaciones.php';

    const MEDIA = (p) => {
        if (!p) return "../multimedia/file.svg";
        if (String(p).startsWith("data:")) return p;
        return "../multimedia/" + p;
    };


    function timeAgo(fechaMysql) {
        if (!fechaMysql) return "";




        let isoDate = fechaMysql.replace(' ', 'T') + 'Z';

        const fecha = new Date(isoDate);
        const ahora = new Date();
        const segundos = Math.floor((ahora - fecha) / 1000);



        if (segundos < 0) return "Hace un momento";

        let intervalo = Math.floor(segundos / 31536000);
        if (intervalo >= 1) return "Hace " + intervalo + " años";

        intervalo = Math.floor(segundos / 2592000);
        if (intervalo >= 1) return "Hace " + intervalo + " meses";

        intervalo = Math.floor(segundos / 86400);
        if (intervalo >= 1) return "Hace " + intervalo + " días";

        intervalo = Math.floor(segundos / 3600);
        if (intervalo >= 1) return "Hace " + intervalo + " h";

        intervalo = Math.floor(segundos / 60);
        if (intervalo >= 1) return "Hace " + intervalo + " min";


        return "Hace un momento";
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnNoti');
        const lista = document.getElementById('listaNoti');
        const badge = document.getElementById('badgeNoti');
        const content = document.getElementById('contenidoNoti');

        if (!btn || !lista) {
            console.error("❌ ERROR: Faltan elementos HTML.");
            return;
        }


        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (lista.style.display === 'block') {
                lista.style.display = 'none';
            } else {
                lista.style.display = 'block';
                if (badge) badge.style.display = 'none';
                cargarDatos(true);
            }
        });


        document.addEventListener('click', (e) => {
            if (lista.style.display === 'block') {
                if (!lista.contains(e.target) && !btn.contains(e.target)) {
                    lista.style.display = 'none';
                }
            }
        });
        lista.addEventListener('click', (e) => e.stopPropagation());


        async function cargarDatos(marcarLeidas = false) {
            try {
                if (marcarLeidas) {
                    fetch(BASE_URL_PHP, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'marcar_leidas=1'
                    }).catch(console.error);
                }

                const res = await fetch(BASE_URL_PHP);
                if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);

                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (e) { return; }

                if (!json.ok) return;


                if (badge && !marcarLeidas) {
                    const cantidad = parseInt(json.sin_leer);
                    if (cantidad > 0) {
                        badge.style.display = 'flex';
                        badge.style.alignItems = 'center';
                        badge.style.justifyContent = 'center';
                        badge.style.backgroundColor = '#ff4757';
                        badge.style.color = '#fff';
                        badge.textContent = cantidad > 99 ? '+99' : cantidad;
                    } else {
                        badge.style.display = 'none';
                    }
                }


                if (json.items.length > 0 && !marcarLeidas) {
                    const latest = Number(json.items[0].id_notificacion);
                    if (lastNotiId > 0 && latest > lastNotiId) {
                        showToast(json.items[0]);
                    }
                    lastNotiId = latest;
                } else if (json.items.length > 0) {
                    lastNotiId = Number(json.items[0].id_notificacion);
                }


                if (content && lista.style.display === 'block') {
                    content.innerHTML = '';
                    if (json.items.length === 0) {
                        content.innerHTML = '<div style="padding:15px; text-align:center; color:#777">Sin novedades.</div>';
                    } else {
                        json.items.forEach(n => {
                            const div = document.createElement('div');
                            div.className = `noti-item ${n.leido == 0 ? 'sin-leer' : ''}`;

                            const bgColor = n.leido == 0 ? 'rgba(255, 71, 87, 0.08)' : 'transparent';
                            div.style.cssText = `padding:10px; border-bottom:1px solid #333; cursor:pointer; display:flex; gap:10px; align-items:center; background:${bgColor}`;

                            const foto = MEDIA(n.actor_foto);


                            const tiempoTexto = timeAgo(n.creado_en);

                            let action = n.link_accion || "";
                            if (action.startsWith("javascript:")) {
                                div.setAttribute('onclick', action);
                            } else if (action) {
                                div.onclick = () => window.location.href = action;
                            }

                            div.innerHTML = `
                                <img src="${foto}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; background:#ccc;">
                                <div style="font-size:14px; color:var(--text, #fff);">
                                    <strong>${n.actor_usuario}</strong> 
                                    <span style="color:var(--muted, #aaa)">${n.texto_formato}</span>
                                    <div style="font-size:11px; color:#666; margin-top:4px;">${tiempoTexto}</div>
                                </div>
                            `;
                            content.appendChild(div);
                        });
                    }
                }
            } catch (e) {
                console.error("❌ Error JS Notificaciones:", e);
            }
        }

        function showToast(n) {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.cssText = `
                background: #222; 
                color: #fff; 
                padding: 12px 15px; 
                margin-top: 10px; 
                border-radius: 8px; 
                display: flex; 
                align-items: center; 
                gap: 12px; 
                min-width: 250px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.5);
                border-left: 4px solid #ff4757;
                opacity: 0; 
                transform: translateY(20px);
                transition: all 0.3s ease;
            `;

            const foto = MEDIA(n.actor_foto);
            toast.innerHTML = `
                <img src="${foto}" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                <div>
                    <strong>@${n.actor_usuario}</strong> 
                    <span style="display:block; font-size:0.9em; opacity:0.9">${n.texto_formato}</span>
                </div>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 50);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        cargarDatos();
        setInterval(() => cargarDatos(), 5000);
    });
})();