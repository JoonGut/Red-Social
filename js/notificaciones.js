document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnNoti');
    const lista = document.getElementById('listaNoti');
    const badge = document.getElementById('badgeNoti');
    const content = document.getElementById('contenidoNoti');
    let lastNotiId = 0; // Para saber cual es la última y lanzar popup

    // 1. Abrir/Cerrar menú
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const esVisible = lista.style.display === 'block';
        lista.style.display = esVisible ? 'none' : 'block';
        
        if (!esVisible) {
            // Marcar como leídas al abrir
            fetch('../php/get_notificaciones.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'marcar_leidas=1'
            });
            badge.style.display = 'none'; // Quitar bolita roja
        }
    });

    // Cerrar si clic fuera
    document.addEventListener('click', () => lista.style.display = 'none');
    lista.addEventListener('click', e => e.stopPropagation());

    // 2. Función principal de carga
    async function checkNotificaciones() {
        try {
            const res = await fetch('../php/get_notificaciones.php');
            const json = await res.json();
            
            if (!json.ok) return;

            // Actualizar Badge
            if (json.sin_leer > 0) {
                badge.textContent = json.sin_leer;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }

            // Renderizar lista
            content.innerHTML = '';
            json.items.forEach(n => {
                // Detectar si es nueva para lanzar POPUP (Toast)
                if (Number(n.id_notificacion) > lastNotiId && lastNotiId !== 0) {
                    showToast(n);
                }

                // Crear HTML de la lista
                const div = document.createElement('div');
                div.className = `noti-item ${n.leido == 0 ? 'sin-leer' : ''}`;
                
                // Icono según tipo
                let icono = '🔔';
                let accion = 'Notificación';
                if(n.tipo === 'mensaje') { icono = '💬'; accion = 'Nuevo mensaje'; }
                if(n.tipo === 'seguir') { icono = '👤'; accion = 'Nuevo seguidor'; }
                if(n.tipo === 'dejar_seguir') { icono = '💔'; accion = 'Ya no te sigue'; }
                if(n.tipo === 'comentario') { icono = '📝'; accion = 'Comentó tu post'; }

                const foto = n.actor_foto ? `../multimedia/${n.actor_foto}` : '../multimedia/file.svg';

                div.innerHTML = `
                    <img src="${foto}" class="noti-avatar">
                    <div class="noti-text">
                        <strong>${n.actor_usuario}</strong> <small>${accion}</small>
                        <div style="font-size:0.85em; opacity:0.8">"${n.texto_extra}"</div>
                    </div>
                `;
                content.appendChild(div);
            });

            // Guardar el ID más alto para la próxima comparación
            if (json.items.length > 0) {
                lastNotiId = Math.max(...json.items.map(i => Number(i.id_notificacion)));
            } else {
                content.innerHTML = '<p class="noti-empty" style="padding:10px; color:#aaa">Sin novedades.</p>';
            }

        } catch (e) { console.error(e); }
    }

    // 3. Mostrar Popup (Toast)
    function showToast(n) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <div>${n.tipo === 'mensaje' ? '💬' : '🔔'}</div>
            <div>
                <strong>${n.actor_usuario}</strong>
                <div>${n.texto_extra}</div>
            </div>
        `;
        container.appendChild(toast);
        
        // Sonido opcional
        // new Audio('../multimedia/notification.mp3').play().catch(e=>{});

        // Quitar a los 4 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // Arrancar bucle (Polling) cada 3 segundos
    checkNotificaciones(); // Primera vez inmediata
    setInterval(checkNotificaciones, 3000);
});