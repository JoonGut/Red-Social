(() => {
  "use strict";

  const $ = (id) => document.getElementById(id);

  const BASE = (window.__BASE__ || "").replace(/\/$/, "");
  const PHP = (p) => p; 
  const MEDIA = (p) => "../multimedia/" + p;

  const state = {
    chatId: 0,
    lastId: 0,
    chats: [],
    pollTimer: null,
    historyLoaded: false,
    otherReadId: 0,
    currentMembers: [] // Para el selector de grupo
  };

  // --- UTILIDADES ---
  async function fetchJson(url, options) {
    try {
      const res = await fetch(url, options);
      const text = await res.text();
      try {
        return { ok: true, data: JSON.parse(text), status: res.status };
      } catch (e) {
        console.error("Error parseando JSON:", text);
        return { ok: false, status: 500 };
      }
    } catch (err) {
      console.error("Fetch error:", err);
      return { ok: false, status: 500 };
    }
  }

  function fmtTime(ts) {
    if (!ts) return "";
    const d = new Date(String(ts).replace(" ", "T"));
    return isNaN(d.getTime()) 
      ? String(ts).slice(11, 16) 
      : d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
  }

  // --- MODAL CREAR GRUPO (Dinámico) ---
  function buildGroupModal() {
    if ($("modalGrupo")) return; // Ya existe

    const html = `
    <div id="modalGrupo" class="modal-overlay" style="display:none; position:fixed; inset:0; bg:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div class="modal-content" style="background:#000; border:1px solid #333; width:90%; max-width:400px; padding:20px; border-radius:10px;">
        <h2 style="margin-top:0;">Nuevo Grupo</h2>
        
        <label style="display:block; margin-bottom:5px;">Nombre del Grupo</label>
        <input type="text" id="groupName" placeholder="Ej: Proyecto Final" maxlength="50" style="width:100%; padding:10px; margin-bottom:15px; background:#222; border:1px solid #333; color:#fff; border-radius:5px;">
        
        <label style="display:block; margin-bottom:5px;">Añadir Participantes</label>
        <div id="groupCandidates" style="max-height:200px; overflow-y:auto; border:1px solid #333; padding:5px; margin-bottom:15px; background:#111;">
          <div style="padding:10px; color:#777;">Cargando amigos...</div>
        </div>
        
        <div style="text-align:right; gap:10px; display:flex; justify-content:flex-end;">
          <button id="btnCancelGroup" style="background:none; border:1px solid #555; color:#fff; padding:8px 15px; border-radius:20px; cursor:pointer;">Cancelar</button>
          <button id="btnCreateGroup" style="background:#1d9bf0; border:none; color:#fff; padding:8px 15px; border-radius:20px; cursor:pointer;">Crear Grupo</button>
        </div>
      </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);

    // Eventos del modal
    $("btnCancelGroup").onclick = () => $("modalGrupo").style.display = "none";
    
    $("btnCreateGroup").onclick = async () => {
      const name = $("groupName").value.trim();
      const checks = document.querySelectorAll(".candidate-check:checked");
      const ids = Array.from(checks).map(c => c.value);

      if (!name) return alert("Ponle un nombre al grupo");
      if (ids.length === 0) return alert("Selecciona al menos un amigo");

      const btn = $("btnCreateGroup");
      btn.textContent = "Creando...";
      btn.disabled = true;

      const r = await fetch(PHP("crear_grupo.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre: name, miembros: ids })
      });
      const json = await r.json();

      if (json.ok) {
        $("modalGrupo").style.display = "none";
        $("groupName").value = "";
        // Recargar lista y abrir el nuevo chat
        await loadChats();
        openChat({ 
           id_chat: json.id_chat, 
           other: { nombre: name, usuario: "grupo" },
           es_grupo: true 
        });
      } else {
        alert(json.msg || "Error creando grupo");
      }
      btn.textContent = "Crear Grupo";
      btn.disabled = false;
    };
  }

  async function showGroupModal() {
    buildGroupModal();
    const modal = $("modalGrupo");
    const list = $("groupCandidates");
    modal.style.display = "flex"; // Usar flex para centrar
    
    // Cargar amigos
    const r = await fetchJson(PHP("get_seguidos_chat.php"));
    if (r.ok && r.data.items) {
       if(r.data.items.length === 0) {
           list.innerHTML = '<div style="padding:10px; color:#777;">No sigues a nadie aún.</div>';
           return;
       }
       let h = '';
       r.data.items.forEach(u => {
           const foto = u.foto_perfil ? MEDIA(u.foto_perfil) : '../multimedia/file.svg';
           h += `
           <label style="display:flex; align-items:center; padding:8px; border-bottom:1px solid #222; cursor:pointer;">
             <input type="checkbox" class="candidate-check" value="${u.id_usuario}" style="margin-right:10px;">
             <img src="${foto}" style="width:30px; height:30px; border-radius:50%; margin-right:10px; object-fit:cover;">
             <div>
               <div style="font-weight:bold;">${u.nombre || u.usuario}</div>
               <small style="color:#777;">@${u.usuario}</small>
             </div>
           </label>`;
       });
       list.innerHTML = h;
    }
  }


  // --- NOTIFICACIONES ---
  function tryRequestNotification() {
    if ("Notification" in window && Notification.permission === "default") {
      Notification.requestPermission();
    }
  }

  function sendNotification(title, body) {
    if ("Notification" in window && Notification.permission === "granted") {
      if (document.hidden || !document.hasFocus()) {
        new Notification(title, { body: body, icon: "../multimedia/file.svg" });
      }
    }
  }

  // --- VISTAS ---
  function showView(open) {
    const empty = $("chatEmpty");
    const view = $("chatView");
    if (!empty || !view) return;
    
    if(window.innerWidth < 768) {
       // Móvil: Ocultar lista si abrimos chat
       $("chatList").parentElement.style.display = open ? "none" : "block";
    }
    
    empty.style.display = open ? "none" : "";
    view.style.display = open ? "flex" : "none";
  }

  function scrollBottom() {
    const box = $("chatMessages");
    if (box) box.scrollTop = box.scrollHeight;
  }

  // --- RENDERIZADO MENSAJES ---
  function updateReadStatus() {
    const myTicks = document.querySelectorAll(".msg-ticks[data-id]");
    myTicks.forEach(span => {
      const msgId = Number(span.dataset.id);
      if (msgId <= state.otherReadId) {
        span.classList.add("leido");
        span.textContent = "✓✓";
        span.style.color = "#1d9bf0"; // Azul
      }
    });
  }

  function appendMessage(m) {
    const box = $("chatMessages");
    if (!box) return;
    if (document.getElementById("msg-" + m.id_mensaje)) return;

    const myId = Number(window.__MY_ID__ || 0);
    const isMe = Number(m.id_usuario) === myId;

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
    
    const timeSpan = document.createElement("span");
    timeSpan.textContent = fmtTime(m.creado_en || "");
    meta.appendChild(timeSpan);

    if (isMe) {
      const tickSpan = document.createElement("span");
      tickSpan.className = "msg-ticks";
      tickSpan.dataset.id = m.id_mensaje;
      
      if (Number(m.id_mensaje) <= state.otherReadId) {
        tickSpan.textContent = "✓✓";
        tickSpan.classList.add("leido");
        tickSpan.style.color = "#1d9bf0";
      } else {
        tickSpan.textContent = "✓";
      }
      meta.appendChild(tickSpan);
    }

    div.appendChild(meta);
    box.appendChild(div);
    state.lastId = Math.max(state.lastId, Number(m.id_mensaje || 0));
  }

  // --- CARGA DE DATOS ---
  async function loadMessages(afterId) {
    if (state.chatId <= 0) return;

    const url = PHP("chat_get.php") + // Asegúrate de que tu archivo se llame así o get_chat_mensajes.php
      "?id_chat=" + encodeURIComponent(String(state.chatId)) +
      (afterId > 0 ? "&after_id=" + encodeURIComponent(String(afterId)) : "");

    const r = await fetchJson(url, { credentials: "same-origin" });
    if (!r.ok || !r.data || !r.data.ok) return;

    const nuevoLeido = Number(r.data.ultimo_leido_otro || 0);
    if (nuevoLeido > state.otherReadId) {
      state.otherReadId = nuevoLeido;
      updateReadStatus();
    }

    const items = Array.isArray(r.data.items) ? r.data.items : [];
    items.forEach(appendMessage);

    if (items.length) {
      scrollBottom();
      markRead(state.lastId).catch(() => {});
    }
  }

  async function markRead(ultimoId) {
    if (state.chatId <= 0) return;
    // Asegúrate de que el nombre del PHP coincida con el que creaste (marcar_leido.php)
    await fetch(PHP("chat_mark_read.php"), {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id_chat=" + state.chatId + "&ultimo_id=" + ultimoId,
    });
  }

  async function loadChats() {
    // Asegúrate de que el nombre coincida (chat_list.php)
    const r = await fetchJson(PHP("chat_list.php"), { credentials: "same-origin" });
    if (!r.ok || !r.data || !r.data.ok) return [];
    
    const items = r.data.items || [];
    state.chats = items;
    renderChatList(items);
  }

  function renderChatList(items) {
    const list = $("chatList");
    if (!list) return;
    
    // Inyectamos cabecera con botón "+"
    let htmlHeader = `
       <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid #333;">
          <h3 style="margin:0; font-size:1.1rem;">Chats</h3>
          <button id="btnNewGroup" style="background:none; border:none; color:#1d9bf0; font-size:1.5rem; cursor:pointer;" title="Crear Grupo">+</button>
       </div>
    `;
    
    // Si la lista está vacía, mostramos el header igual
    list.innerHTML = htmlHeader;

    // Listener para botón "+"
    const btn = $("btnNewGroup");
    if(btn) btn.onclick = showGroupModal;

    items.forEach((it) => {
      const cid = Number(it.id_chat);
      const row = document.createElement("div");
      row.className = "chat-item" + (cid === state.chatId ? " active" : "");
      
      // LÓGICA DE AVATAR: Si es grupo, icono. Si es user, su foto.
      const avatarDiv = document.createElement("div");
      avatarDiv.className = "chat-avatar";
      
      if (it.es_grupo) {
          avatarDiv.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#333; color:#fff; font-size:1.2rem;">👥</div>`;
      } else if (it.other_foto) {
          const img = document.createElement("img");
          img.src = MEDIA(encodeURIComponent(it.other_foto));
          avatarDiv.appendChild(img);
      } else {
          // Inicial si no tiene foto
          avatarDiv.textContent = (it.other_usuario || "U")[0].toUpperCase();
          avatarDiv.style.display = "flex";
          avatarDiv.style.alignItems = "center";
          avatarDiv.style.justifyContent = "center";
      }

      const meta = document.createElement("div");
      meta.className = "chat-meta";
      
      // Si es grupo, usamos nombre_grupo. Si es user, su nombre/usuario
      const displayName = it.es_grupo ? it.other_nombre : (it.other_nombre || it.other_usuario);
      const displayHandle = it.es_grupo ? "Grupo" : "@" + it.other_usuario;

      meta.innerHTML = `
        <div class="chat-name">
            <strong>${displayName}</strong>
        </div>
        <div class="chat-preview" style="color:#71767b; font-size:0.9rem;">
            ${it.last_texto ? (it.last_texto.length>25?it.last_texto.substring(0,25)+'...':it.last_texto) : "Comienza a charlar..."}
        </div>
      `;

      if (it.unread_count > 0) {
        const badge = document.createElement("div");
        badge.className = "chat-badge";
        badge.textContent = it.unread_count;
        row.appendChild(badge);
      }

      row.prepend(avatarDiv); // Añadir avatar al principio
      row.insertBefore(meta, row.lastChild); // Insertar meta antes del badge (o al final)

      row.addEventListener("click", () => {
        tryRequestNotification();
        openChat(it);
      });

      list.appendChild(row);
    });
  }

  async function openChat(chatData) {
    const cid = Number(chatData.id_chat || 0);
    if (cid <= 0) return;

    state.chatId = cid;
    state.historyLoaded = false;
    state.otherReadId = 0;
    
    if($("chatId")) $("chatId").value = cid;
    
    // Actualizar cabecera del chat abierto
    const topName = $("chatTopName");
    const topUser = $("chatTopUser");
    
    const displayName = chatData.es_grupo ? chatData.other_nombre : (chatData.other_nombre || chatData.other_usuario);
    const displayHandle = chatData.es_grupo ? (chatData.miembros + " miembros") : "@" + chatData.other_usuario;

    if(topName) topName.textContent = displayName;
    if(topUser) topUser.textContent = displayHandle;
    
    showView(true);
    const box = $("chatMessages");
    if(box) box.innerHTML = "";
    state.lastId = 0;

    await loadMessages(0);
    state.historyLoaded = true;

    // Quitamos badge localmente sin recargar todo
    renderChatList(state.chats.map(c => c.id_chat == cid ? {...c, unread_count:0} : c));

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

      input.value = ""; 

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

  // --- BUSCADOR LATERAL ---
  function wireSearch() {
    const s = $("chatSearch");
    if(s) s.addEventListener("input", () => {
        const q = s.value.toLowerCase();
        document.querySelectorAll(".chat-item").forEach(row => {
            const txt = row.innerText.toLowerCase();
            row.style.display = txt.includes(q) ? "flex" : "none";
        });
    });
  }

  // --- INIT ---
  window.__chatInit = async function () {
    tryRequestNotification();
    showView(false);
    if (state.pollTimer) clearInterval(state.pollTimer);
    
    wireSend();
    wireSearch();

    await loadChats();

    // Auto-abrir desde perfil
    const chatUser = sessionStorage.getItem("chatUser");
    if (chatUser) {
      sessionStorage.removeItem("chatUser");
      // Llamamos a tu archivo de iniciar chat 1vs1
      const r = await fetchJson(PHP("iniciar_chat.php") + "?u=" + encodeURIComponent(chatUser));
      if (r.ok && r.data.ok) {
        openChat({ 
           id_chat: r.data.id_chat, 
           other_usuario: r.data.other.usuario, 
           other_nombre: r.data.other.nombre,
           other_foto: r.data.other.foto_perfil,
           es_grupo: false
        });
      }
    }
  };

})();