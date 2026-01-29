<?php
include 'header.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: auth/login.php");
    exit;
}

$nombre = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'];
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda Deportiva</title>
<link rel="stylesheet" href="assets/css/cliente.css"> <!-- Ruta correcta -->
</head>
<body>

<div class="main">
    <div class="welcome-card">
        <h3>Bienvenido, <?= htmlspecialchars($nombre) ?> 👋</h3>
        <p>En tu panel puedes ver tus pedidos, explorar productos y categorías.</p>
    </div>

    <div class="dashboard-cards">
        <div class="card">
            <h4>Productos</h4>
            <p>Explora todos nuestros productos disponibles.</p>
            <a href="producto/index.php">Ver productos</a>
        </div>>
        <div class="card">
            <h4>Mis Pedidos</h4>
            <p>Revisa el estado de tus pedidos y detalles.</p>
            <a href="mis_pedidos/index.php">Ver pedidos</a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../partials/chatbot.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>
