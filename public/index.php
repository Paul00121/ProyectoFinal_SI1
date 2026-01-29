<!-- Burbuja del chatbot -->
<div id="chatbot-bubble" onclick="toggleChatbox()">
  💬
</div>

<!-- Chatbox -->
<div id="chatbox">
  <div id="chatbox-header">
    <span>Asistente Virtual</span>
    <button onclick="toggleChatbox()">✖</button>
  </div>

  <div id="chatbox-messages"></div>
  <div class="chatbox-options"></div>
</div>

<script src="js/chatbot.js"></script>
