<?php
// Mostrar SOLO si:
// 1) No hay sesión iniciada (index intro)
// 2) O el rol es CLIENTE (rol_id = 2)

$mostrarChatbot = false;

if (!isset($_SESSION['rol_id'])) {
    // No logueado → index
    $mostrarChatbot = true;
} elseif ($_SESSION['rol_id'] == 2) {
    // Cliente
    $mostrarChatbot = true;
}

if (!$mostrarChatbot) {
    return;
}
?>

<!-- Burbuja -->
<div id="chatbot-bubble" onclick="toggleChatbox()">🤖</div>

<!-- Chat -->
<div id="chatbox">
  <div id="chatbox-header">
    <span>Asistente Virtual</span>
    <button onclick="toggleChatbox()">✖</button>
  </div>

  <div id="chatbox-messages"></div>
  <div class="chatbox-options"></div>
</div>

<link rel="stylesheet" href="/tienda_deportiva/public/css/chatbot.css">
<script src="/tienda_deportiva/public/js/chatbot.js"></script>
