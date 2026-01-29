<?php
session_start();
require_once '../../config/database.php';

$mensaje = '';
$tipo = 'danger'; // success | danger | warning | info
$token_valido = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {

    $token = trim($_GET['token']);

    $stmt = $pdo->prepare("
        SELECT 
            v.id AS verif_id,
            v.usuario_id,
            v.usado,
            v.expiracion,
            u.correo,
            u.nombre,
            u.verificado AS ya_verificado
        FROM verificacion_email v
        INNER JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.token = :token
    ");
    $stmt->execute(['token' => $token]);
    $datos = $stmt->fetch();

    if ($datos) {

        if ($datos['ya_verificado']) {
            $mensaje = "Tu cuenta ya está verificada. Puedes iniciar sesión.";
            $tipo = 'info';
            $token_valido = true;

        } elseif ($datos['usado']) {
            $mensaje = "Este enlace de verificación ya fue utilizado. Tu cuenta debería estar verificada.";
            $tipo = 'warning';

        } elseif (date('Y-m-d H:i:s') > $datos['expiracion']) {
            $mensaje = "El enlace de verificación ha expirado. Por favor, solicita uno nuevo.";
            $tipo = 'warning';

        } else {

            try {
                $pdo->beginTransaction();

                // Verificar usuario
                $stmt = $pdo->prepare("UPDATE usuarios SET verificado = true WHERE id = :id");
                $stmt->execute(['id' => $datos['usuario_id']]);

                // Marcar token como usado
                $stmt = $pdo->prepare("UPDATE verificacion_email SET usado = true WHERE id = :id");
                $stmt->execute(['id' => $datos['verif_id']]);

                $pdo->commit();

                $_SESSION['verificacion_exitosa'] = true;
                $mensaje = "¡Cuenta verificada exitosamente! Ya puedes iniciar sesión con tu correo y contraseña.";
                $tipo = 'success';
                $token_valido = true;

            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = "Error al verificar la cuenta. Inténtalo nuevamente.";
                $tipo = 'danger';
            }
        }

    } else {
        $mensaje = "Token de verificación inválido. Verifica que el enlace sea correcto.";
        $tipo = 'danger';
    }

} else {
    $mensaje = "No se proporcionó un token de verificación válido.";
    $tipo = 'danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Verificación de Cuenta | Tienda Deportiva</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
    min-height: 100vh;
}
.card {
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(0,0,0,.15);
}
.icon {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.icon.success { background: #d4edda; color: #155724; }
.icon.warning { background: #fff3cd; color: #856404; }
.icon.danger  { background: #f8d7da; color: #721c24; }
.icon.info    { background: #d1ecf1; color: #0c5460; }

.btn-main {
    background: #0b132b;
    border: none;
}
.btn-main:hover {
    background: #1c2541;
}
</style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="col-md-7 col-lg-6">
    <div class="card p-5 text-center">

        <?php if ($tipo === 'success'): ?>
            <div class="icon success"><i class="fas fa-check fa-3x"></i></div>
            <h3 class="text-success fw-bold">¡Verificación Exitosa!</h3>
        <?php elseif ($tipo === 'warning'): ?>
            <div class="icon warning"><i class="fas fa-exclamation-triangle fa-3x"></i></div>
            <h3 class="text-warning fw-bold">Atención</h3>
        <?php elseif ($tipo === 'info'): ?>
            <div class="icon info"><i class="fas fa-info-circle fa-3x"></i></div>
            <h3 class="text-info fw-bold">Información</h3>
        <?php else: ?>
            <div class="icon danger"><i class="fas fa-times fa-3x"></i></div>
            <h3 class="text-danger fw-bold">Error</h3>
        <?php endif; ?>

        <div class="alert alert-<?= $tipo ?> mt-4 text-start">
            <?= $mensaje ?>
        </div>

        <?php if ($token_valido || $tipo === 'info'): ?>
            <p class="text-muted mt-3">Tu cuenta está lista para usar.</p>
            <a href="login.php" class="btn btn-main btn-lg px-5 mt-2">
                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
            </a>
        <?php else: ?>
            <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                <a href="registrar.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus me-2"></i>Registrarse
                </a>
                <a href="login.php" class="btn btn-outline-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
