<?php
// includes/chatbot.php
// Chatbot inteligente MineHostX (usa ../api/chatbot.php)
// Este archivo se incluye al final del <body> o en footer.php
?>
<style>
/* --- Estilos del Chatbot MineHostX --- */

/* Botón flotante */
#chatbot-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #4FC3F7;
  color: #000;
  border-radius: 50%;
  width: 55px;
  height: 55px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 28px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.4);
  z-index: 9999;
  transition: transform 0.2s;
}
#chatbot-btn:hover { transform: scale(1.05); }

/* Ventana principal */
#chatbot-window {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 340px;
  max-width: calc(100% - 40px);
  background: #1E1E1E;
  border-radius: 12px;
  display: none;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0,0,0,0.6);
  z-index: 9999;
}

/* Cabecera */
#chat-header {
  background: #4FC3F7;
  padding: 12px;
  font-weight: bold;
  color: #000;
  text-align: center;
  font-size: 1.1em;
}
#chat-close { transition: opacity 0.2s; }
#chat-close:hover { opacity: 0.8; }

/* Área de mensajes */
#chat-messages {
  height: 350px;
  padding: 10px;
  overflow-y: auto;
  color: #fff;
  font-size: 15px;
  background: linear-gradient(180deg,#151515,#171717);
  line-height: 1.4;
}
/* Scroll personalizado */
#chat-messages::-webkit-scrollbar { width: 6px; }
#chat-messages::-webkit-scrollbar-thumb { background: #4FC3F7; border-radius: 3px; }
#chat-messages::-webkit-scrollbar-track { background: #1E1E1E; }

/* Burbuja Bot */
#chat-messages .bot {
  background: #333;
  padding: 12px;
  border-radius: 8px 12px 12px 4px; 
  margin: 8px 0;
  max-width: 90%;
  text-align: left;
}
/* Markdown dentro del bot */
#chat-messages .bot strong { color: #FF9800; font-weight: bold; }
#chat-messages .bot ul { list-style: disc; padding-left: 20px; margin: 5px 0; }
#chat-messages .bot li { margin-bottom: 5px; }

/* Burbuja Usuario */
#chat-messages .user {
  background: #4FC3F7;
  color: #000;
  padding: 12px;
  border-radius: 12px 4px 4px 12px; 
  margin: 8px 0;
  text-align: right;
  margin-left: auto;
  max-width: 90%;
}

/* Botones de acción rápida */
.quick-action-btns {
  margin-top: 10px;
  padding-top: 5px;
  border-top: 1px dashed #444;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.quick-action-btns button {
  background: #FF9800;
  color: #000;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9em;
  font-weight: bold;
  transition: background 0.2s;
}
.quick-action-btns button:hover { background: #FFB74D; }

/* Input y botón de envío */
#chat-input {
  display: flex;
  gap: 8px;
  padding: 10px;
  background:#141414;
}
#chat-input input {
  flex: 1;
  padding: 10px;
  border: none;
  outline: none;
  background: #222;
  color: #fff;
  border-radius: 6px;
}
#chat-input button {
  background: #4FC3F7;
  border: none;
  padding: 10px 12px;
  cursor: pointer;
  color: #000;
  font-weight: bold;
  border-radius: 6px;
  transition: opacity 0.2s;
}
#chat-input button:hover { opacity: 0.9; }

#chat-loading { font-size:12px; color:#aaa; padding:6px 10px; text-align: left; }
</style>

<div id="chatbot-btn" aria-label="Abrir chat">💬</div>

<div id="chatbot-window" role="dialog" aria-hidden="true" aria-label="Soporte MineHostX">
  <div id="chat-header">
    Asistente MineHostX
    <span id="chat-close" style="float:right; cursor:pointer;">✖</span>
  </div>
  <div id="chat-messages" aria-live="polite"></div>
  <div id="chat-loading" style="display:none;">🤖 Escribiendo...</div>
  <div id="chat-input">
    <input type="text" id="chat-text" placeholder="Escribe tu duda..." aria-label="Mensaje">
    <button id="chat-send">➤</button>
  </div>
</div>

<script>
(function(){
  const btn = document.getElementById("chatbot-btn");
  const win = document.getElementById("chatbot-window");
  const closeBtn = document.getElementById("chat-close");
  const messages = document.getElementById("chat-messages");
  const input = document.getElementById("chat-text");
  const sendBtn = document.getElementById("chat-send");
  const loading = document.getElementById("chat-loading");

  if (!btn || !win || !messages || !input || !sendBtn) return;

  function toggleWindow() {
    if (win.style.display === "flex") {
      win.style.display = "none";
      win.setAttribute('aria-hidden','true');
    } else {
      win.style.display = "flex";
      win.style.flexDirection = "column";
      win.setAttribute('aria-hidden','false');
      input.focus();
      scrollBottom();
    }
  }
  btn.addEventListener("click", toggleWindow);
  closeBtn.addEventListener("click", toggleWindow);

  function scrollBottom(){ messages.scrollTop = messages.scrollHeight; }

  function markdownToHtml(md) {
    let html = md.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\n/g, '<br>');
    const listPattern = /<br>- (.*?)($|<br>)/g;
    if (listPattern.test(html)) {
      html = html.replace(/<br>- /g, '<li>');
      html = html.replace(/(<li>.*?)(<br>)/g, '$1</li>');
      html = '<ul>' + html.replace(/<\/li><br><li>/g, '</li><li>') + '</ul>';
      html = html.replace(/^<br>/, '').replace(/<br>$/, '');
    }
    return html;
  }

  function addMessage(text, who='bot'){
    const d = document.createElement('div');
    d.className = who === 'user' ? 'user' : 'bot';

    if (who === 'bot') {
      d.innerHTML = markdownToHtml(text);

      if (text.toLowerCase().includes("crear servidor") || text.toLowerCase().includes("panel")) {
        const actions = document.createElement('div');
        actions.className = 'quick-action-btns';
        actions.innerHTML = `
          <button onclick="window.location.href='panel.php'">Ir al Panel</button>
          <button onclick="window.location.href='planes.php'">Ver Planes</button>
        `;
        d.appendChild(actions);
      }

    } else {
      d.textContent = text;
    }
    messages.appendChild(d);
    scrollBottom();
  }

  window.sendQuickReply = function(replyText) {
    input.value = replyText;
    sendMessage();
  }

  function fallbackReply(text){
    text = text.toLowerCase();
    if (text.includes('hola') || text.includes('buenas')) 
      return "**¡Hola!** 👋 Soy el asistente de **MineHostX**.\n¿En qué puedo ayudarte hoy?";
    if (text.includes('crear servidor')) 
      return "Para crear un servidor ve a **Panel**  y pulsa **➕ Crear nuevo servidor**.";
    if (text.includes('precio') || text.includes('plan')) 
      return "Consulta los planes de hosting en la sección **Planes** en tu Panel de Control.";
    return "No tengo una respuesta para eso todavía. Por favor, contacta con soporte: **soporte@minehostx.local**";
  }

  async function sendMessage(){
    const text = input.value.trim();
    if (!text) return;
    addMessage(text, 'user');
    input.value = "";
    loading.style.display = "block";

    const form = new FormData();
    form.append('message', text);
    const rutaApi = "../api/chatbot.php";

    try {
      const res = await fetch(rutaApi, { method: 'POST', body: form });
      let reply;

      if (!res.ok) {
        reply = fallbackReply(text);
      } else {
        const data = await res.json();
        reply = (data && data.reply) ? data.reply : fallbackReply(text);
      }
      addMessage(reply, 'bot');
    } catch (e) {
      addMessage("**Error de conexión** con el servicio de chat. Por favor, inténtalo más tarde.", 'bot');
      console.error("Chatbot fetch error:", e);
    } finally {
      loading.style.display = "none";
    }
  }

  input.addEventListener("keydown", function(e){
    if (e.key === "Enter") {
      e.preventDefault();
      sendMessage();
    }
  });

  sendBtn.addEventListener("click", sendMessage);

  // Mensaje inicial
  addMessage("Hola 👋 Soy el asistente de **MineHostX**. Estoy aquí para ayudarte con tus dudas sobre servidores, planes o soporte técnico.");
})();
</script>
