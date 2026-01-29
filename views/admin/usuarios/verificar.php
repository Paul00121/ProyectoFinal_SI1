<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

// Obtener id del usuario a verificar
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de usuario no válido.");
}

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT nombre, correo, verificado FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Solo enviar mensaje si no está verificado
if ($usuario['verificado']) {
    $mensaje = "El usuario <strong>{$usuario['nombre']}</strong> ya está verificado.";
} else {
    // Aquí se podría enviar el correo de verificación si quieres (opcional)
    // Por ahora solo mostramos el mensaje
    $mensaje = "El usuario <strong>{$usuario['nombre']}</strong> fue creado correctamente. 
                Debe verificar su correo: <em>{$usuario['correo']}</em>.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Verificar Usuario | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 600px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-align: center;
}
h2 {
    color: #0b132b;
    margin-bottom: 20px;
}
.alert-info {
    font-size: 16px;
}
.btn-volver {
    margin-top: 20px;
}
</style>
</head>
<body>

<div class="container">
    <h2>Verificación de Usuario</h2>
    <div class="alert alert-info">
        <?= $mensaje ?>
    </div>
    <a href="index.php" class="btn btn-primary btn-volver">Volver al listado</a>
</div>

</body>
</html>
