// chatbot
const preguntasIniciales = [
  {
    texto: "¿Cuáles son sus horarios?",
    respuesta: "Atendemos de lunes a sábado de 9:00 a 12:00 y de 15:00 a 19:30.",
    siguientes: [
      { texto: "¿Atienden en feriados?", respuesta: "Solo con citas previas." },
      { texto: "¿Hay atención fuera del horario?", respuesta: "Solo con citas previas." },
      { texto: "¿Cómo agendo una cita?", respuesta: "Mediante WhatsApp, <a href='https://wa.me/59174666992?text=¡Hola!%20Quiero%20agendar%20una%20cita%20de%20visita%20a%20su%20tienda.' target='_blank'>Click Aquí para escribirnos</a>." }
    ]
  },
  {
    texto: "¿Hacen envíos?",
    respuesta: "Sí, hacemos envíos a todo el país.",
    siguientes: [
      { texto: "¿Cuánto es el costo del delivery?", respuesta: "El costo del envío depende de la distancia entre la tienda y su domicilio. <a href='https://wa.me/59174545356?text=¡Hola!%20Quiero%20cotizar%20el%20costo%20del%20envío%20a%20mi%20domicilio.' target='_blank'>Click Aquí para consultar el costo</a>." },
      { texto: "¿Hacen envíos a todos los departamentos?", respuesta: "Sí, a los 9 departamentos." },
      { texto: "¿Cómo envían a otros departamentos?", respuesta: "Con el producto ya pagado, nosotros lo envíamos mediante la transportadora de preferencia del cliente y el costo del envío corre por parte del comprador." }
    ]
  },
  {
    texto: "¿Dónde están ubicados?",
    respuesta: "Nuestra sede central está en la Zona Norte, entre el 7mo anillo y 8vo anillo, Av. Beni, Calle H, nro 18.",
    siguientes: [
      { texto: "¿Tienen más sucursales?", respuesta: "Sí, nuestra segunda sucursal está en el 5to anillo, Radial 26 en el Condominio Los Mangales, Calle 4 Oeste, Casa 3." },
      {texto: "Ubicaciones en GPS", respuesta: "<a href='../../index.html#sede-central'>Click Aquí para la Sede Central</a><br><br> <a href='../../index.html#segunda-sucursal'>Click Aquí para la Segunda Sucursal</a>" }
    ]
  },
  {
    texto: "Soporte técnico",
    respuesta: "Podés contactarte con nuestro soporte técnico por WhatsApp:",
    siguientes: [
      {
        texto: "📞 Soporte 1",
        respuesta: "<a href='https://wa.me/59174545356' target='_blank'>Escribir al 74545356</a>"
      },
      {
        texto: "📞 Soporte 2",
        respuesta: "<a href='https://wa.me/59174666992' target='_blank'>Escribir al 74666992</a>"
      },
      {
        texto: "📞 Soporte 3",
        respuesta: "<a href='https://wa.me/5917809144' target='_blank'>Escribir al 7809144</a>"
      }
    ]
  }
];

let preguntasPendientes = [...preguntasIniciales];
let subPreguntasPendientes = [];
let estadoActual = null;

function toggleChatbox() {
  const chatbox = document.getElementById("chatbox");
  const messages = document.getElementById("chatbox-messages");
  const options = document.querySelector(".chatbox-options");

  const isOpen = chatbox.style.display === "flex";
  chatbox.style.display = isOpen ? "none" : "flex";

  if (!isOpen && messages.children.length === 0) {
    const usuario = localStorage.getItem("usuarioActual") || "usuario";
    const saludo = `¡Hola, ${usuario}! ¿En qué puedo ayudarte hoy?`;
    const saludoElem = document.createElement("div");
    saludoElem.className = "bot-message";
    saludoElem.innerText = saludo;
    messages.appendChild(saludoElem);
    mostrarOpciones();
  }
}

function mostrarOpciones() {
  const options = document.querySelector(".chatbox-options");
  options.innerHTML = "";

  if (subPreguntasPendientes.length > 0) {
    subPreguntasPendientes.forEach((pregunta, index) => {
      const btn = document.createElement("button");
      btn.innerText = pregunta.texto;
      btn.onclick = () => responder(pregunta, true);
      options.appendChild(btn);
    });
  } else if (preguntasPendientes.length > 0) {
    preguntasPendientes.forEach((pregunta, index) => {
      const btn = document.createElement("button");
      btn.innerText = pregunta.texto;
      btn.onclick = () => responder(pregunta);
      options.appendChild(btn);
    });
  } else {
    despedirUsuario();
  }
}

function responder(pregunta, esSubPregunta = false) {
  const mensajes = document.getElementById("chatbox-messages");

  const userMsg = document.createElement("div");
  userMsg.className = "user-message";
  userMsg.innerText = pregunta.texto;
  mensajes.appendChild(userMsg);

  const botMsg = document.createElement("div");
  botMsg.className = "bot-message";
  botMsg.innerHTML = pregunta.respuesta;
  mensajes.appendChild(botMsg);

  mensajes.scrollTop = mensajes.scrollHeight;

  if (!esSubPregunta) {
    const index = preguntasPendientes.findIndex(p => p.texto === pregunta.texto);
    if (index !== -1) {
      preguntasPendientes.splice(index, 1);
      subPreguntasPendientes = [...pregunta.siguientes];
    }
  } else {
    const index = subPreguntasPendientes.findIndex(p => p.texto === pregunta.texto);
    if (index !== -1) {
      subPreguntasPendientes.splice(index, 1);
    }
  }

  setTimeout(mostrarOpciones, 500);
}


function despedirUsuario() {
  const mensajes = document.getElementById("chatbox-messages");
  const botMsg = document.createElement("div");
  botMsg.className = "bot-message";
  botMsg.innerHTML = "¡Gracias por chatear con nosotros! Si tenés más preguntas, <a href='https://wa.me/59160883366' target='_blank'>clickea aquí para hablarnos por WhatsApp</a>. ¡Hasta luego!";
  mensajes.appendChild(botMsg);
  document.querySelector(".chatbox-options").innerHTML = "";
}
