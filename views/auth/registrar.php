<?php
session_start();
require_once '../../config/database.php';
require_once '../../funciones/enviar_correo.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre     = trim($_POST['nombre']);
    $apellidos  = trim($_POST['apellidos']);
    $correo     = trim($_POST['correo']);
    $password   = trim($_POST['password']);
    $rol_id     = 2; // Cliente por defecto

    if ($nombre && $apellidos && $correo && $password) {

        // Validar solo letras y espacios
        if (!preg_match("/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/u", $nombre)) {
            $error = "El nombre solo puede contener letras y espacios.";
        } elseif (!preg_match("/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/u", $apellidos)) {
            $error = "Los apellidos solo pueden contener letras y espacios.";
        } 
        // Validar contraseña con máximo 8 caracteres y al menos 1 carácter especial
        elseif (strlen($password) > 8) {
            $error = "La contraseña no puede tener más de 8 caracteres.";
        } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
            $error = "La contraseña debe contener al menos un carácter especial (!@#$%^&* etc.).";
        } else {

            // Capitalizar primera letra de cada palabra
            $nombre = mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8");
            $apellidos = mb_convert_case($apellidos, MB_CASE_TITLE, "UTF-8");

            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
            $stmt->execute(['correo' => $correo]);

            if ($stmt->fetch()) {
                $error = "El correo ya está registrado.";
            } else {

                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                // Insertar usuario
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, apellidos, correo, password, rol_id, verificado)
                    VALUES (:nombre, :apellidos, :correo, :password, :rol_id, false)
                    RETURNING id
                ");

                if ($stmt->execute([
                    'nombre'    => $nombre,
                    'apellidos' => $apellidos,
                    'correo'    => $correo,
                    'password'  => $passwordHash,
                    'rol_id'    => $rol_id
                ])) {

                    $userId = $stmt->fetchColumn();

                    // Token de verificación
                    $token = bin2hex(random_bytes(32));
                    $expiracion = date('Y-m-d H:i:s', strtotime('+1 day'));

                    $stmt = $pdo->prepare("
                        INSERT INTO verificacion_email (usuario_id, token, expiracion)
                        VALUES (:uid, :token, :exp)
                    ");

                    if ($stmt->execute([
                        'uid'   => $userId,
                        'token' => $token,
                        'exp'   => $expiracion
                    ])) {

                        enviarCorreoVerificacion($correo, $nombre, $token);
                        $_SESSION['registro_exitoso'] = "Revisa tu correo para activar tu cuenta";
                        header("Location: login.php");
                        exit;

                    } else {
                        $error = "Error al generar verificación.";
                    }

                } else {
                    $error = "Error al registrar usuario.";
                }

            }

        }

    } else {
        $error = "Completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro | Tienda Deportiva</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../../public/css/registrar.css">
</head>
<body>

<div class="register-card">
    <div class="text-center mb-4">
        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
        <h4 class="fw-bold">Crea tu cuenta</h4>
        <p class="text-muted">Regístrate para comenzar a comprar</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="register-form">
        <div class="mb-3">
            <input class="form-control" id="nombre" name="nombre" placeholder="Nombre" required>
        </div>
        <div class="mb-3">
            <input class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos" required>
        </div>
        <div class="mb-3">
            <input class="form-control" type="email" name="correo" placeholder="Correo electrónico" required>
        </div>
        <div class="mb-3">
            <input class="form-control" type="password" id="password" name="password" placeholder="Contraseña (máx 8 caracteres, 1 especial)" maxlength="8" required>
        </div>
        <button class="btn btn-register w-100 py-2" type="submit"><i class="fas fa-check me-2"></i>Registrarse</button>
    </form>

    <hr class="my-4">

    <p class="text-center mb-0">
        ¿Ya tienes cuenta? <a href="login.php" class="fw-bold">Inicia sesión</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/registrar.js"></script>
</body>
</html>
