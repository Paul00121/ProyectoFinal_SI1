<?php
session_start();
require_once '../../config/database.php';

$error = '';
$success_msg = '';

// Mensajes de registro o verificación
if (isset($_SESSION['registro_exitoso'])) {
    $success_msg = $_SESSION['registro_exitoso'];
    unset($_SESSION['registro_exitoso']);
}

if (isset($_SESSION['verificacion_exitosa'])) {
    $success_msg = "¡Tu cuenta ha sido verificada exitosamente! Ahora puedes iniciar sesión.";
    unset($_SESSION['verificacion_exitosa']);
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            if (password_verify($password, $usuario['password'])) { // Asegúrate que sea "password" no "passwd"
                if ($usuario['verificado']) {
                    // Crear sesión
                    $_SESSION['usuario'] = [
                        'id'        => $usuario['id'],
                        'nombre'    => $usuario['nombre'],
                        'apellidos' => $usuario['apellidos'],
                        'correo'    => $usuario['correo']
                    ];

                    $_SESSION['idu'] = $usuario['id']; // opcional, si ya usas usuario[id]
                    $_SESSION['idr'] = $usuario['rol_id'];


                    // Redirigir según rol
                    if ($_SESSION['idr'] == 1) {
                        header("Location: ../admin/index_admin.php");
                    } else {
                        header("Location: ../cliente/index_cliente.php");
                    }
                    exit;
                } else {
                    $error = "Debes verificar tu correo antes de iniciar sesión. Revisa tu bandeja de entrada o spam.";
                }
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión - Tienda Deportiva</title>
<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- CSS externo -->
<link rel="stylesheet" href="../../public/css/login.css">
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <i class="fas fa-running fa-3x text-primary mb-3"></i>
        <h4 class="fw-bold">Bienvenido a Tienda Deportiva</h4>
        <p class="text-muted">Inicia sesión para continuar</p>
    </div>

    <!-- ALERTA DE ÉXITO -->
    <?php if($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ALERTA DE ERROR: PERMANENTE -->
    <?php if($error): ?>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
            <input type="email" name="correo" class="form-control" placeholder="ejemplo@correo.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-lock me-2"></i>Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
        </div>
        <div class="mb-3 text-end">
            <a href="recuperar.php" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
        </div>
        <button class="btn btn-login btn-primary w-100 py-2">
            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
        </button>
    </form>

    <hr class="my-4">

    <p class="text-center mb-0">
        ¿No tienes cuenta? <a href="registrar.php" class="text-decoration-none fw-bold">Regístrate aquí</a>
    </p>
</div>

<!-- JS externos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/login.js"></script>
</body>
</html>
