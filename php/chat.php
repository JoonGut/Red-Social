<?php
declare(strict_types=1);
session_start();
if (empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    exit('No login');
}
?>

<main class="contenido-principal">
  
  <header class="cabecera">
    <div class="cabecera-left">
      <h1>Mensajes</h1>
      <p class="cabecera-sub">Tus conversaciones</p>
    </div>
  </header>

  <div class="chat-layout" id="chatApp">
    
    <aside class="chat-left">
      <div class="chat-left-head">
        <input id="chatSearch" class="chat-search" type="search" placeholder="Buscar mensajes..." autocomplete="off">
      </div>
      <div id="chatList" class="chat-list">
        </div>
    </aside>

    <section class="chat-right">
      
      <div id="chatEmpty" class="chat-empty">
        <h2>Selecciona una conversación</h2>
        <p>Elige un chat para empezar a hablar.</p>
      </div>

      <div id="chatView" class="chat-view" style="display:none; height: 100%; flex-direction: column;">
        
        <div class="chat-topbar">
          <div class="chat-topbar-user">
            <div id="chatTopName" class="chat-topbar-name">Usuario</div>
            <div id="chatTopUser" class="chat-topbar-handle">@usuario</div>
          </div>
          <button style="background:none; border:none; color:var(--accent2); cursor:pointer; font-size:1.1rem;">
             <i class="fas fa-info-circle"></i>
          </button>
        </div>

        <div id="chatMessages" class="chat-messages">
           </div>

        <form id="chatSendForm" class="chat-send" autocomplete="off">
          <input type="hidden" id="chatId" value="">
          <input id="chatText" class="chat-input" type="text" maxlength="250" placeholder="Escribe un mensaje...">
          <button id="chatSendBtn" class="chat-send-btn" type="submit">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>

      </div>
    </section>
  </div>
</main>