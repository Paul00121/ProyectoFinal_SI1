<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header('Location: /tienda_deportiva/views/auth/login.php');
    exit;
}

$nombre = $_SESSION['usuario'];
?>

<header class="header">
    <div class="logo">
        <h2>Tienda Deportiva</h2>
    </div>
    <nav>
        <a href="/tienda_deportiva/views/cliente/index_cliente.php">Inicio</a>
        <a href="/tienda_deportiva/views/cliente/producto/index.php">Productos</a>
        <a href="/tienda_deportiva/views/cliente/crear_pedido/index.php">Crear pedido</a>
        <a href="/tienda_deportiva/views/cliente/mis_pedidos/index.php">Mis pedidos</a>
        <a href="/tienda_deportiva/views/auth/logout.php">Cerrar sesión</a>
    </nav>
</header>
