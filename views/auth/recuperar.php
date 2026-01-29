<?php
session_start();
require_once '../../config/database.php';
require_once '../../funciones/enviar_correo.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);

    // Verificar que el usuario exista
    $stmt = $pdo->prepare("
        SELECT id, nombre 
        FROM usuarios 
        WHERE correo = :correo
        LIMIT 1
    ");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $error = "❌ No existe una cuenta con ese correo electrónico.";
    } else {

        // Generar código y token
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $token = bin2hex(random_bytes(32));

        // Guardar código en BD
        $stmt = $pdo->prepare("
            INSERT INTO recuperacion_password (correo, codigo, expiracion, token)
            VALUES (:correo, :codigo, :expiracion, :token)
        ");

        $insertado = $stmt->execute([
            'correo'      => $correo,
            'codigo'      => $codigo,
            'expiracion'  => $expiracion,
            'token'       => $token
        ]);

        if ($insertado) {

            // Enviar correo
            $resultadoCorreo = enviarCodigoRecuperacion($correo, $codigo);

            if ($resultadoCorreo['success']) {

                // Guardar sesión
                $_SESSION['correo_recuperacion'] = $correo;
                $_SESSION['token_recuperacion']  = $token;
                unset($_SESSION['codigo_inicio']); // reset contador

                // REDIRECCIÓN CLAVE
                header("Location: verificar_codigo.php");
                exit();

            } else {
                $error = "❌ Error al enviar el correo: " . $resultadoCorreo['message'];
            }

        } else {
            $error = "❌ Error al generar el código de recuperación.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña</title>

<link rel="stylesheet" href="../../public/css/recuperar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<div class="recovery-card">
    <div class="text-center mb-4">
        <i class="fas fa-key fa-3x text-primary mb-3"></i>
        <h4 class="fw-bold">Recuperar Contraseña</h4>
        <p class="text-muted">
            Ingresa tu correo para recibir un código de recuperación
        </p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input
            type="email"
            name="correo"
            class="form-control"
            placeholder="Introduce tu correo"
            required
        >

        <button class="btn-recovery w-100 my-3">
            <i class="fas fa-paper-plane me-2"></i>Enviar Código
        </button>
    </form>

    <hr class="my-4">

    <div class="text-center">
        <a href="login.php">
            <i class="fas fa-arrow-left me-2"></i>Volver al inicio de sesión
        </a>
    </div>
</div>

</body>
</html>
