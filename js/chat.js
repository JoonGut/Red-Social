(() => {
  "use strict";

  const $ = (id) => document.getElementById(id);

  function normalizeBaseFromPath(pathname) {
  
    const p = String(pathname || "");
    const m = p.match(/^(.*)\/php\/[^/]+$/i);
    if (m && m[1]) return m[1].replace(/\/$/, "");
    return p.replace(/\/$/, "");
  }

  function getBase() {
   
    return normalizeBaseFromPath(location.pathname);
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
  };

  async function fetchJson(url, options) {
    const res = await fetch(url, options);
    const text = await res.text();
    try {
      return { ok: true, data: JSON.parse(text), status: res.status };
    } catch {
      console.error("[chat] Respuesta NO JSON desde", url, "status", res.status, text.slice(0, 300));
      return { ok: false, data: null, status: res.status };
    }
  }

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

  function fmtTime(ts) {
    if (!ts) return "";
    const d = new Date(String(ts).replace(" ", "T"));
    return isNaN(d.getTime()) ? String(ts) : d.toLocaleString();
  }

  function renderChatList(items) {
    const list = $("chatList");
    if (!list) return;

    list.innerHTML = "";

    items.forEach((it) => {
      const cid = Number(it.id_chat || 0);

      const row = document.createElement("div");
      row.className = "chat-item" + (cid === state.chatId ? " active" : "");
      row.dataset.otherUsuario = String(it.other_usuario || "");
      row.dataset.otherNombre = String(it.other_nombre || "");

      const avatar = document.createElement("div");
      avatar.className = "chat-avatar";

      const foto = String(it.other_foto || "").trim();
      if (foto) {
        const img = document.createElement("img");
        img.src = MEDIA(encodeURIComponent(foto));
        img.alt = "";
        avatar.appendChild(img);
      } else {
        avatar.textContent = (String(it.other_usuario || "U")[0] || "U").toUpperCase();
      }

      const meta = document.createElement("div");
      meta.className = "chat-meta";

      const name = document.createElement("div");
      name.className = "chat-name";

      const strong = document.createElement("strong");
      strong.textContent = String(it.other_nombre || it.other_usuario || "");

      const small = document.createElement("small");
      small.textContent = "@" + String(it.other_usuario || "");

      name.appendChild(strong);
      name.appendChild(small);

      const preview = document.createElement("div");
      preview.className = "chat-preview";
      preview.textContent = String(it.last_texto || "");

      meta.appendChild(name);
      meta.appendChild(preview);

      const unread = Number(it.unread_count || 0);
      const badge = document.createElement("div");
      if (unread > 0) {
        badge.className = "chat-badge";
        badge.textContent = String(unread);
      } else {
        badge.className = "chat-dot";
        badge.style.opacity = "0";
      }

      row.appendChild(avatar);
      row.appendChild(meta);
      row.appendChild(badge);

      row.addEventListener("click", () => {
        openChat({
          id_chat: cid,
          other: {
            usuario: it.other_usuario,
            nombre: it.other_nombre,
            foto_perfil: it.other_foto,
            id_usuario: it.other_id,
          },
        }).catch(() => { });
      });

      list.appendChild(row);
    });
  }

  async function loadChats() {
    const r = await fetchJson(PHP("chat_list.php"), { credentials: "same-origin" });
    if (!r.ok || !r.data || !r.data.ok) return [];
    const items = Array.isArray(r.data.items) ? r.data.items : [];
    state.chats = items;
    renderChatList(items);
    return items;
  }

  function setTopbar(other) {
    const topName = $("chatTopName");
    const topUser = $("chatTopUser");
    if (topName) topName.textContent = String(other?.nombre || other?.usuario || "");
    if (topUser) topUser.textContent = other?.usuario ? "@" + String(other.usuario) : "";
  }

  function clearMessages() {
    const box = $("chatMessages");
    if (box) box.innerHTML = "";
    state.lastId = 0;
  }

  function appendMessage(m) {
    const box = $("chatMessages");
    if (!box) return;

    const existingMsg = document.getElementById("msg-" + m.id_mensaje);
    if (existingMsg) return;

    const myId = Number(window.__MY_ID__ || 0);
    const isMe = Number(m.id_usuario) === myId;

    if (!isMe && state.historyLoaded) {
      
      const chatName = $("chatTopName") ? $("chatTopName").textContent : "Nuevo mensaje";
      
      if (Notification.permission === "granted") {
         new Notification(chatName, {
           body: m.texto,
           icon: "../multimedia/file.svg" 
         });
      }
    }
    // -----------------------------

    const div = document.createElement("div");
    div.id = "msg-" + m.id_mensaje; 

    div.className = "msg" + (isMe ? " me" : "");
    div.textContent = String(m.texto || "");

    const meta = document.createElement("div");
    meta.className = "msg-meta";
    meta.textContent = fmtTime(m.creado_en || "");
    div.appendChild(meta);

    box.appendChild(div);
    state.lastId = Math.max(state.lastId, Number(m.id_mensaje || 0));
  }
  async function markRead(ultimoId) {
    if (state.chatId <= 0) return;

    await fetch(PHP("chat_mark_read.php"), {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "id_chat=" +
        encodeURIComponent(String(state.chatId)) +
        "&ultimo_id=" +
        encodeURIComponent(String(Number(ultimoId || 0))),
    });
  }

  async function loadMessages(afterId) {
    if (state.chatId <= 0) return;

    const url =
      PHP("chat_get.php") +
      "?id_chat=" +
      encodeURIComponent(String(state.chatId)) +
      (afterId > 0 ? "&after_id=" + encodeURIComponent(String(afterId)) : "");

    const r = await fetchJson(url, { credentials: "same-origin" });
    if (!r.ok || !r.data || !r.data.ok) return;

    const items = Array.isArray(r.data.items) ? r.data.items : [];
    items.forEach(appendMessage);

    if (items.length) {
      scrollBottom();
      markRead(state.lastId).catch(() => { });
    }
  }

  async function openChat(payload) {
    const cid = Number(payload?.id_chat || 0);
    if (cid <= 0) return;

    state.chatId = cid;
    state.historyLoaded = false; 

    const hidden = $("chatId");
    if (hidden) hidden.value = String(cid);

    setTopbar(payload.other || {});
    showView(true);
    clearMessages();
    await loadMessages(0); 

    state.historyLoaded = true; 

    renderChatList(state.chats);

    if (state.pollTimer) clearInterval(state.pollTimer);
    state.pollTimer = setInterval(() => loadMessages(state.lastId).catch(() => { }), 1500);
  }

  function requestedUser() {
    const s = String(sessionStorage.getItem("chatUser") || "").trim();
    if (s) return s;
    try {
      const q = new URLSearchParams(location.search).get("chatUser");
      return String(q || "").trim();
    } catch {
      return "";
    }
  }

  async function autoOpenFromProfile() {
    const u = requestedUser();
    if (!u) return;

    sessionStorage.removeItem("chatUser");

    const r = await fetchJson(
      PHP("chat_open_whit_user.php") + "?u=" + encodeURIComponent(u),
      { credentials: "same-origin" }
    );
    if (!r.ok || !r.data || !r.data.ok) return;

    await loadChats();
    await openChat({ id_chat: Number(r.data.id_chat), other: r.data.other || { usuario: u } });
  }

  function wireSend() {
    const form = $("chatSendForm");
    const input = $("chatText");
    if (!form || !input) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const txt = String(input.value || "").trim();
      if (!txt || state.chatId <= 0) return;

      const r = await fetchJson(PHP("chat_send.php"), {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
          "id_chat=" +
          encodeURIComponent(String(state.chatId)) +
          "&texto=" +
          encodeURIComponent(txt),
      });

      if (!r.ok || !r.data || !r.data.ok) return;

      appendMessage({
        id_mensaje: Number(r.data.id_mensaje || 0),
        texto: txt,
        id_usuario: Number(window.__MY_ID__ || 0),
        creado_en: r.data.creado_en || "",
      });

      input.value = "";
      scrollBottom();
      markRead(state.lastId).catch(() => { });
      loadChats().catch(() => { });
    });
  }

  function wireSearch() {
    const s = $("chatSearch");
    const list = $("chatList");
    if (!s || !list) return;

    s.addEventListener("input", () => {
      const q = String(s.value || "").trim().toLowerCase();
      const nodes = list.querySelectorAll(".chat-item");
      nodes.forEach((n) => {
        const u = String(n.dataset.otherUsuario || "").toLowerCase();
        const nm = String(n.dataset.otherNombre || "").toLowerCase();
        n.style.display = !q || u.includes(q) || nm.includes(q) ? "" : "none";
      });
    });
  }

  window.__chatInit = async function () {
    showView(false);
    if ("Notification" in window && Notification.permission !== "granted") {
      Notification.requestPermission();
    }

    if (state.pollTimer) {
      clearInterval(state.pollTimer);
      state.pollTimer = null;
    }
    if (state.pollTimer) {
      clearInterval(state.pollTimer);
      state.pollTimer = null;
    }

    wireSend();
    wireSearch();

    await loadChats();
    await autoOpenFromProfile();
  };
})();
