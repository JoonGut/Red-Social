(() => {
  "use strict";

  const $ = (id) => document.getElementById(id);

  const BASE = (window.__BASE__ || "").replace(/\/$/, "");
  const PHP = (p) => p; 
  
  // --- CORRECCIÓN CLAVE: Función MEDIA inteligente ---
  // Detecta si es Base64 o archivo normal
  const MEDIA = (p) => {
    if (!p) return "../multimedia/file.svg";
    // Si empieza por data:, es Base64 (viene de la BD)
    if (String(p).startsWith("data:")) return p;
    // Si no, asumimos que es un archivo en la carpeta (Legacy)
    return "../multimedia/" + p;
  };

  const state = {
    chatId: 0,
    lastId: 0,
    chats: [],
    pollTimer: null,
    historyLoaded: false,
    otherReadId: 0,
    currentMembers: [] 
  };

  // --- UTILIDADES ---
  async function fetchJson(url, options) {
    try {
      const res = await fetch(url, options);
      const text = await res.text();
      try {
        // Intentamos parsear. Si falla, mostramos el error de PHP en consola
        return { ok: true, data: JSON.parse(text), status: res.status };
      } catch (e) {
        console.error("Error parseando JSON. Respuesta del servidor:", text);
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
    if ($("modalGrupo")) return; 

    const html = `
    <div id="modalGrupo" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
      <div class="modal-content" style="background:var(--card); border:1px solid var(--border); width:90%; max-width:400px; padding:20px; border-radius:10px; color:var(--text);">
        <h2 style="margin-top:0;">Nuevo Grupo</h2>
        
        <label style="display:block; margin-bottom:5px;">Nombre del Grupo</label>
        <input type="text" id="groupName" placeholder="Ej: Proyecto Final" maxlength="50" style="width:100%; padding:10px; margin-bottom:15px; background:var(--bg); border:1px solid var(--border); color:var(--text); border-radius:5px;">
        
        <label style="display:block; margin-bottom:5px;">Añadir Participantes</label>
        <div id="groupCandidates" style="max-height:200px; overflow-y:auto; border:1px solid var(--border); padding:5px; margin-bottom:15px; background:var(--bg);">
          <div style="padding:10px; color:var(--muted);">Cargando amigos...</div>
        </div>
        
        <div style="text-align:right; gap:10px; display:flex; justify-content:flex-end;">
          <button id="btnCancelGroup" style="background:none; border:1px solid var(--border); color:var(--text); padding:8px 15px; border-radius:20px; cursor:pointer;">Cancelar</button>
          <button id="btnCreateGroup" style="background:var(--accent); border:none; color:#fff; padding:8px 15px; border-radius:20px; cursor:pointer;">Crear Grupo</button>
        </div>
      </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', html);

    $("btnCancelGroup").onclick = () => $("modalGrupo").style.display = "none";
    
    $("btnCreateGroup").onclick = async () => {
      const name = $("groupName").value.trim();
      const checks = document.querySelectorAll(".candidate-check:checked");
      const ids = Array.from(checks).map(c => c.value);

      if (!name) return alert("Ponle un nombre al grupo");
      if (ids.length === 0) return alert("Selecciona al menos un amigo");

      const btn = $("btnCreateGroup");
      const txtOriginal = btn.textContent;
      btn.textContent = "Creando...";
      btn.disabled = true;

      const r = await fetch(PHP("crear_grupo.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre: name, miembros: ids })
      });
      // Aquí también usamos try/catch implícito o fetchJson mejorado, pero mantenemos tu estructura
      try {
        const json = await r.json();
        if (json.ok) {
            $("modalGrupo").style.display = "none";
            $("groupName").value = "";
            await loadChats();
            openChat({ 
               id_chat: json.id_chat, 
               other_nombre: name, // Ajuste para que coincida con la estructura
               es_grupo: true 
            });
          } else {
            alert(json.msg || "Error creando grupo");
          }
      } catch (e) {
          console.error("Error respuesta grupo", e);
      }
      
      btn.textContent = txtOriginal;
      btn.disabled = false;
    };
  }

  async function showGroupModal() {
    buildGroupModal();
    const modal = $("modalGrupo");
    const list = $("groupCandidates");
    modal.style.display = "flex"; 
    
    const r = await fetchJson(PHP("get_seguidos_chat.php"));
    if (r.ok && r.data.items) {
       if(r.data.items.length === 0) {
           list.innerHTML = '<div style="padding:10px; color:var(--muted);">No sigues a nadie aún.</div>';
           return;
       }
       let h = '';
       r.data.items.forEach(u => {
           // Usamos MEDIA() aquí también
           const foto = MEDIA(u.foto_perfil);
           h += `
           <label style="display:flex; align-items:center; padding:8px; border-bottom:1px solid var(--border); cursor:pointer;">
             <input type="checkbox" class="candidate-check" value="${u.id_usuario}" style="margin-right:10px;">
             <img src="${foto}" style="width:30px; height:30px; border-radius:50%; margin-right:10px; object-fit:cover;">
             <div>
               <div style="font-weight:bold;">${u.nombre || u.usuario}</div>
               <small style="color:var(--muted);">@${u.usuario}</small>
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
    const listContainer = document.querySelector(".chat-list-container"); // Ajusta el selector si es diferente en tu HTML

    if (!empty || !view) return;
    
    // Lógica Móvil
    if(window.innerWidth < 768) {
       if (open) {
           // Abrir Chat: Ocultar lista, mostrar chat
           if(listContainer) listContainer.style.display = "none";
           view.style.display = "flex";
           empty.style.display = "none";
       } else {
           // Cerrar Chat: Mostrar lista, ocultar chat
           if(listContainer) listContainer.style.display = "block";
           view.style.display = "none";
           empty.style.display = "none"; // O flex, según diseño
       }
    } else {
       // Escritorio
       empty.style.display = open ? "none" : "flex";
       view.style.display = open ? "flex" : "none";
       if(listContainer) listContainer.style.display = "block";
    }
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
        span.style.color = "#1d9bf0"; 
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

    const url = PHP("chat_get.php") + 
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
    
    let htmlHeader = `
       <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid var(--border);">
          <h3 style="margin:0; font-size:1.1rem; color:var(--text);">Chats</h3>
          <button id="btnNewGroup" style="background:none; border:none; color:var(--accent); font-size:1.5rem; cursor:pointer;" title="Crear Grupo">+</button>
       </div>
    `;
    
    list.innerHTML = htmlHeader;

    const btn = $("btnNewGroup");
    if(btn) btn.onclick = showGroupModal;

    if (items.length === 0) {
        const emptyMsg = document.createElement("div");
        emptyMsg.style.padding = "20px";
        emptyMsg.style.color = "var(--muted)";
        emptyMsg.style.textAlign = "center";
        emptyMsg.textContent = "No tienes chats activos.";
        list.appendChild(emptyMsg);
        return;
    }

    items.forEach((it) => {
      const cid = Number(it.id_chat);
      const row = document.createElement("div");
      row.className = "chat-item" + (cid === state.chatId ? " active" : "");
      
      const avatarDiv = document.createElement("div");
      avatarDiv.className = "chat-avatar";
      
      if (it.es_grupo) {
          avatarDiv.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--card2); color:var(--text); font-size:1.2rem;">👥</div>`;
      } else if (it.other_foto) {
          const img = document.createElement("img");
          // CAMBIO: NO usar encodeURIComponent aquí porque rompe el Base64 (data:image...)
          // MEDIA() ya maneja si es base64 o archivo
          img.src = MEDIA(it.other_foto); 
          avatarDiv.appendChild(img);
      } else {
          avatarDiv.textContent = (it.other_usuario || "U")[0].toUpperCase();
          avatarDiv.style.display = "flex";
          avatarDiv.style.alignItems = "center";
          avatarDiv.style.justifyContent = "center";
          avatarDiv.style.background = "var(--card2)";
      }

      const meta = document.createElement("div");
      meta.className = "chat-meta";
      
      const displayName = it.es_grupo ? it.other_nombre : (it.other_nombre || it.other_usuario);
      
      // Limitar texto preview
      let preview = it.last_texto || "Comienza a charlar...";
      if(preview.length > 25) preview = preview.substring(0, 25) + "...";

      meta.innerHTML = `
        <div class="chat-name">
            <strong>${displayName}</strong>
        </div>
        <div class="chat-preview" style="color:var(--muted); font-size:0.9rem;">
            ${preview}
        </div>
      `;

      if (it.unread_count > 0) {
        const badge = document.createElement("div");
        badge.className = "chat-badge";
        badge.textContent = it.unread_count;
        row.appendChild(badge);
      }

      row.prepend(avatarDiv); 
      row.insertBefore(meta, row.lastChild); 

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
    
    const topName = $("chatTopName");
    const topUser = $("chatTopUser");
    
    const displayName = chatData.es_grupo ? (chatData.other_nombre || chatData.nombre_grupo) : (chatData.other_nombre || chatData.other_usuario);
    const displayHandle = chatData.es_grupo ? (chatData.miembros + " miembros") : "@" + chatData.other_usuario;

    if(topName) topName.textContent = displayName;
    if(topUser) topUser.textContent = displayHandle;
    
    showView(true);
    const box = $("chatMessages");
    if(box) box.innerHTML = "";
    state.lastId = 0;

    await loadMessages(0);
    state.historyLoaded = true;

    // Quitamos badge localmente
    renderChatList(state.chats.map(c => c.id_chat == cid ? {...c, unread_count:0} : c));

    if (state.pollTimer) clearInterval(state.pollTimer);
    state.pollTimer = setInterval(() => loadMessages(state.lastId).catch(()=>{}), 1500);
    
    // Botón volver en móvil (asegurarse que existe en el HTML o crearlo dinámicamente)
    const btnBack = $("btnBackChat");
    if(btnBack) {
        btnBack.onclick = () => showView(false);
    }
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

    const chatUser = sessionStorage.getItem("chatUser");
    if (chatUser) {
      sessionStorage.removeItem("chatUser");
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