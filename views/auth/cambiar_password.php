<?php
session_start();
require_once '../../config/database.php';

$error = '';
$success = '';

// Seguridad: solo entra si pasó por verificación
if (
    !isset($_SESSION['correo_recuperacion']) ||
    !isset($_SESSION['codigo_verificado'])
) {
    header("Location: recuperar.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);
    $correo   = $_SESSION['correo_recuperacion'];

    if ($password !== $confirm) {
        $error = "❌ Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "❌ La contraseña debe tener al menos 6 caracteres.";
    } else {

        // Encriptar contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar contraseña (columna correcta: password)
        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET password = :password
            WHERE correo = :correo
        ");

        if ($stmt->execute([
            'password' => $hash,
            'correo'   => $correo
        ])) {

            // Marcar recuperación como usada
            $stmt = $pdo->prepare("
                UPDATE recuperacion_password
                SET usado = TRUE
                WHERE correo = :correo
            ");
            $stmt->execute(['correo' => $correo]);

            // Limpiar sesión
            unset($_SESSION['correo_recuperacion']);
            unset($_SESSION['codigo_verificado']);
            unset($_SESSION['token_recuperacion']);

            $success = "✅ Tu contraseña fue actualizada correctamente.";
        } else {
            $error = "❌ Error al actualizar la contraseña.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar Contraseña</title>

<link rel="stylesheet" href="../../public/css/recuperar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<div class="recovery-card">
    <h4 class="text-center mb-3">
        <i class="fas fa-lock me-2"></i>Restablecer Contraseña
    </h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <a href="login.php" class="btn btn-recovery w-100 mt-3">
            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
        </a>
    <?php else: ?>

        <form method="POST">
            <input
                type="password"
                name="password"
                class="form-control mb-2"
                placeholder="Nueva Contraseña"
                required
            >

            <input
                type="password"
                name="confirm_password"
                class="form-control mb-2"
                placeholder="Confirmar Contraseña"
                required
            >

            <button class="btn btn-recovery w-100 mt-3">
                <i class="fas fa-sync-alt me-2"></i>Cambiar Contraseña
            </button>
        </form>

        <hr>

        <div class="text-center">
            <a href="login.php">
                <i class="fas fa-arrow-left me-2"></i>Volver al inicio de sesión
            </a>
        </div>

    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
