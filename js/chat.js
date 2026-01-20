(() => {
  "use strict";

  const $ = (id) => document.getElementById(id);

  function normalizeBaseFromPath(pathname) {
    const p = String(pathname || "");
    const m = p.match(/^(.*)\/php\/[^/]+$/i);
    if (m && m[1]) return m[1].replace(/\/$/, "");
    return p.replace(/\/$/, "");
  }

  const BASE = (window.__BASE__ || "").replace(/\/$/, "");
  const PHP = (p) => BASE + "/php/" + p;
  const MEDIA = (p) => BASE + "/multimedia/" + p;

  const state = {
    chatId: 0,
    lastId: 0,
    chats: [],
    pollTimer: null,
    historyLoaded: false,
    otherReadId: 0, // <--- NUEVO: Guardamos hasta dónde leyó el otro
  };

  // --- UTILIDADES ---
  async function fetchJson(url, options) {
    try {
      const res = await fetch(url, options);
      const text = await res.text();
      return { ok: true, data: JSON.parse(text), status: res.status };
    } catch {
      return { ok: false, data: null, status: 500 };
    }
  }

  function fmtTime(ts) {
    if (!ts) return "";
    const d = new Date(String(ts).replace(" ", "T"));
    return isNaN(d.getTime()) ? String(ts).slice(11, 16) : d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
  }

  // --- NOTIFICACIONES ---
  function tryRequestNotification() {
    if ("Notification" in window && Notification.permission === "default") {
      Notification.requestPermission();
    }
  }

  function sendNotification(title, body) {
    if ("Notification" in window && Notification.permission === "granted") {
      // Solo notificar si la pestaña está oculta o no tiene el foco
      if (document.hidden || !document.hasFocus()) {
        new Notification(title, { body: body, icon: "../multimedia/file.svg" });
      }
    }
  }

  // --- VISTAS Y SCROLL ---
  function showView(open) {
    const empty = $("chatEmpty");
    const view = $("chatView");
    if (!empty || !view) return;
    empty.style.display = open ? "none" : "";
    view.style.display = open ? "" : "none";
  }

  function scrollBottom() {
    const box = $("chatMessages");
    if (box) box.scrollTop = box.scrollHeight;
  }

  // --- RENDERIZADO ---
  function updateReadStatus() {
    // Busca todos los ticks que sean míos y verifica si deben ponerse azules
    const myTicks = document.querySelectorAll(".msg-ticks[data-id]");
    myTicks.forEach(span => {
      const msgId = Number(span.dataset.id);
      if (msgId <= state.otherReadId) {
        span.classList.add("leido"); // Se vuelve azul
        span.textContent = "✓✓";     // Doble check
      }
    });
  }

  function appendMessage(m) {
    const box = $("chatMessages");
    if (!box) return;

    // Evitar duplicados
    if (document.getElementById("msg-" + m.id_mensaje)) return;

    const myId = Number(window.__MY_ID__ || 0);
    const isMe = Number(m.id_usuario) === myId;

    // Notificación: Si es nuevo, no soy yo, y ya cargó el historial
    if (!isMe && state.historyLoaded) {
      const chatName = $("chatTopName") ? $("chatTopName").textContent : "Nuevo mensaje";
      sendNotification(chatName, m.texto);
    }

    const div = document.createElement("div");
    div.id = "msg-" + m.id_mensaje;
    div.className = "msg" + (isMe ? " me" : "");
    div.textContent = String(m.texto || "");

    const meta = document.createElement("div");
    meta.className = "msg-meta";
    
    // Hora
    const timeSpan = document.createElement("span");
    timeSpan.textContent = fmtTime(m.creado_en || "");
    meta.appendChild(timeSpan);

    // Ticks (Solo si el mensaje es mío)
    if (isMe) {
      const tickSpan = document.createElement("span");
      tickSpan.className = "msg-ticks";
      tickSpan.dataset.id = m.id_mensaje; // Guardamos ID para buscarlo luego
      
      // Si el ID del mensaje es menor o igual a lo que leyó el otro -> Azul
      if (Number(m.id_mensaje) <= state.otherReadId) {
        tickSpan.textContent = "✓✓";
        tickSpan.classList.add("leido");
      } else {
        tickSpan.textContent = "✓"; // Un solo check gris
      }
      meta.appendChild(tickSpan);
    }

    div.appendChild(meta);
    box.appendChild(div);
    state.lastId = Math.max(state.lastId, Number(m.id_mensaje || 0));
  }

  // --- LÓGICA DE CARGA ---
  async function loadMessages(afterId) {
    if (state.chatId <= 0) return;

    const url = PHP("chat_get.php") +
      "?id_chat=" + encodeURIComponent(String(state.chatId)) +
      (afterId > 0 ? "&after_id=" + encodeURIComponent(String(afterId)) : "");

    const r = await fetchJson(url, { credentials: "same-origin" });
    if (!r.ok || !r.data || !r.data.ok) return;

    // 1. Actualizamos hasta dónde leyó el otro usuario
    const nuevoLeido = Number(r.data.ultimo_leido_otro || 0);
    if (nuevoLeido > state.otherReadId) {
      state.otherReadId = nuevoLeido;
      updateReadStatus(); // Actualizar colores en tiempo real
    }

    // 2. Pintar mensajes nuevos
    const items = Array.isArray(r.data.items) ? r.data.items : [];
    items.forEach(appendMessage);

    if (items.length) {
      scrollBottom();
      // Marcar que YO he leído hasta el último recibido
      markRead(state.lastId).catch(() => {});
    }
  }

  async function markRead(ultimoId) {
    if (state.chatId <= 0) return;
    await fetch(PHP("chat_mark_read.php"), {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id_chat=" + state.chatId + "&ultimo_id=" + ultimoId,
    });
  }

  async function loadChats() {
    const r = await fetchJson(PHP("chat_list.php"), { credentials: "same-origin" });
    if (!r.ok || !r.data || !r.data.ok) return [];
    
    const items = r.data.items || [];
    state.chats = items;
    renderChatList(items);
  }

  function renderChatList(items) {
    const list = $("chatList");
    if (!list) return;
    list.innerHTML = "";

    items.forEach((it) => {
      const cid = Number(it.id_chat);
      const row = document.createElement("div");
      row.className = "chat-item" + (cid === state.chatId ? " active" : "");
      
      // Avatar
      const avatar = document.createElement("div");
      avatar.className = "chat-avatar";
      if (it.other_foto) {
        const img = document.createElement("img");
        img.src = MEDIA(encodeURIComponent(it.other_foto));
        avatar.appendChild(img);
      } else {
        avatar.textContent = (it.other_usuario || "U")[0].toUpperCase();
      }

      // Info
      const meta = document.createElement("div");
      meta.className = "chat-meta";
      meta.innerHTML = `
        <div class="chat-name">
            <strong>${it.other_nombre || it.other_usuario}</strong>
            <small>@${it.other_usuario}</small>
        </div>
        <div class="chat-preview">${it.last_texto || "Imagen enviada..."}</div>
      `;

      // Badge no leídos
      const badge = document.createElement("div");
      if (it.unread_count > 0) {
        badge.className = "chat-badge";
        badge.textContent = it.unread_count;
      }

      row.appendChild(avatar);
      row.appendChild(meta);
      row.appendChild(badge);

      row.addEventListener("click", () => {
        tryRequestNotification(); // <--- PEDIR PERMISO AL CLICKAR
        openChat({
          id_chat: cid,
          other: { usuario: it.other_usuario, nombre: it.other_nombre }
        });
      });

      list.appendChild(row);
    });
  }

  async function openChat(payload) {
    const cid = Number(payload?.id_chat || 0);
    if (cid <= 0) return;

    state.chatId = cid;
    state.historyLoaded = false;
    state.otherReadId = 0; // Resetear lectura del otro
    
    if($("chatId")) $("chatId").value = cid;
    
    const topName = $("chatTopName");
    if(topName) topName.textContent = payload.other?.nombre || payload.other?.usuario;
    
    showView(true);
    const box = $("chatMessages");
    if(box) box.innerHTML = "";
    state.lastId = 0;

    await loadMessages(0);
    state.historyLoaded = true;

    renderChatList(state.chats); // Re-render para quitar badge de no leídos

    if (state.pollTimer) clearInterval(state.pollTimer);
    state.pollTimer = setInterval(() => loadMessages(state.lastId).catch(()=>{}), 1500);
  }

  // --- ENVÍO ---
  function wireSend() {
    const form = $("chatSendForm");
    const input = $("chatText");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const txt = input.value.trim();
      if (!txt || state.chatId <= 0) return;

      input.value = ""; // Limpiar rápido para sensación de velocidad

      const r = await fetchJson(PHP("chat_send.php"), {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id_chat=" + state.chatId + "&texto=" + encodeURIComponent(txt),
      });

      if (r.ok && r.data.ok) {
        appendMessage({
          id_mensaje: r.data.id_mensaje,
          texto: txt,
          id_usuario: window.__MY_ID__,
          creado_en: r.data.creado_en
        });
        scrollBottom();
      }
    });
  }

  function wireSearch() {
    const s = $("chatSearch");
    if(s) s.addEventListener("input", () => {
        const q = s.value.toLowerCase();
        document.querySelectorAll(".chat-item").forEach(row => {
            const txt = row.innerText.toLowerCase();
            row.style.display = txt.includes(q) ? "" : "none";
        });
    });
  }

  // --- INICIALIZACIÓN ---
  window.__chatInit = async function () {
    // Intentar pedir permiso al cargar (algunos navegadores lo permiten)
    tryRequestNotification();

    showView(false);
    if (state.pollTimer) clearInterval(state.pollTimer);
    
    wireSend();
    wireSearch();

    await loadChats();

    // Auto-abrir desde perfil
    const u = sessionStorage.getItem("chatUser");
    if (u) {
      sessionStorage.removeItem("chatUser");
      const r = await fetchJson(PHP("chat_open_whit_user.php") + "?u=" + encodeURIComponent(u));
      if (r.ok && r.data.ok) {
        openChat({ id_chat: r.data.id_chat, other: r.data.other });
      }
    }
  };

})();