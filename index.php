<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';
session_start();


?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tienda Deportiva | Equipamiento y Accesorios</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

body {
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* HEADER */
header {
    background: #0b132b;
    color: white;
    padding: 15px 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h1 {
    font-size: 22px;
}

nav a {
    color: white;
    margin-left: 20px;
    text-decoration: none;
    font-weight: bold;
}

nav a:hover {
    text-decoration: underline;
}

/* SLIDER */
.slider {
    position: relative;
    width: 100%;
    max-height: 300px; /* slider más pequeño */
    overflow: hidden;
    margin-bottom: 40px;
}

.slides {
    display: flex;
    width: 300%; /* 3 imágenes */
    transition: margin-left 1s ease-in-out;
}

.slides img {
    width: auto;
    max-width: 100%;
    max-height: 300px;
    margin: 0 auto;
    object-fit: contain; /* mantiene la proporción original */
    display: block;
}

/* CATEGORIES */
.categories {
    padding: 40px 50px;
    background: white;
    text-align: center;
}

.categories h3 {
    font-size: 28px;
    margin-bottom: 30px;
}

.category-box {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
}

.category {
    background: #f1f1f1;
    width: 280px;
    padding: 25px;
    margin: 10px;
    border-radius: 10px;
}

.category h4 {
    margin-bottom: 10px;
}

/* FOOTER */
footer {
    background: #0b132b;
    color: #aaa;
    text-align: center;
    padding: 20px;
    margin-top: auto;
}

/* RESPONSIVE */
@media screen and (max-width: 768px) {
    .slider {
        max-height: 200px;
    }
    .category {
        width: 90%;
    }
}
</style>
</head>

<body>

<header>
    <h1> Tienda Deportiva</h1>
    <nav>
                <!-- Siempre público -->
        <a href="views/auth/login.php">Iniciar sesión</a>
        <a href="views/auth/registrar.php">Registrarse</a>
    </nav>
</header>

<!-- SLIDER -->
<section class="slider">
    <div class="slides" id="slides">
        <img src="public/img/zapatillas.jpg" alt="Calzado Deportivo">
        <img src="public/img/polera.jpg" alt="Ropa Deportiva">
        <img src="public/img/tomatodo.jpg" alt="Accesorios">
    </div>
</section>

<!-- CATEGORIES -->
<section class="categories">
    <h3>Nuestras Categorías</h3>
    <div class="category-box">
        <div class="category">
            <h4>Calzado Deportivo</h4>
            <p>Zapatillas para todo tipo de deporte.</p>
        </div>
        <div class="category">
            <h4>Ropa Deportiva</h4>
            <p>Entrena cómodo y con estilo.</p>
        </div>
        <div class="category">
            <h4>Accesorios</h4>
            <p>Todo lo que necesitas para rendir mejor.</p>
        </div>
    </div>
</section>

<footer>
    © <?= date('Y') ?> Tienda Deportiva · Proyecto Académico
</footer>

<!-- JS CARRUSEL AUTOMÁTICO -->
<script>
let index = 0;
const slides = document.getElementById('slides');
const total = slides.children.length;

function showNextSlide() {
    index++;
    if(index >= total) index = 0;
    slides.style.marginLeft = '-' + (index * 100) + '%';
}

setInterval(showNextSlide, 4000); // Cambia cada 4 segundos
</script>
<?php include __DIR__ . '/views/partials/chatbot.php'; ?>
</body>
</html>
