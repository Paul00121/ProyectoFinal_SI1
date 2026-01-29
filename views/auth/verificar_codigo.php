<?php
session_start();
require_once '../../config/database.php';

// Si no hay correo en sesión, volver a recuperar
if (!isset($_SESSION['correo_recuperacion'])) {
    header("Location: recuperar.php");
    exit();
}

$error = '';

// Inicializar contador de tiempo (60 segundos visuales)
if (!isset($_SESSION['codigo_inicio'])) {
    $_SESSION['codigo_inicio'] = time();
}

$tiempoRestante = 60 - (time() - $_SESSION['codigo_inicio']);
if ($tiempoRestante < 0) {
    $tiempoRestante = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $correo = $_SESSION['correo_recuperacion'];

    // Buscar código válido en la tabla correcta
    $stmt = $pdo->prepare("
        SELECT id 
        FROM recuperacion_password
        WHERE correo = :correo
          AND codigo = :codigo
          AND usado = false
          AND expiracion > NOW()
        LIMIT 1
    ");

    $stmt->execute([
        'correo' => $correo,
        'codigo' => $codigo
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Marcar código como usado
        $stmt = $pdo->prepare("
            UPDATE recuperacion_password 
            SET usado = true 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $row['id']]);

        // Marcar como verificado
        $_SESSION['codigo_verificado'] = true;
        unset($_SESSION['codigo_inicio']);

        header("Location: cambiar_password.php");
        exit();
    } else {
        $error = "❌ El código ingresado no es válido o ya expiró.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Verificar Código</title>

<link rel="stylesheet" href="../../public/css/verificar_codigo.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<div class="recovery-card">
    <h4 class="text-center mb-3">
        <i class="fas fa-shield-alt me-2"></i>Verificar Código
    </h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="codigo-form">
        <input
            type="text"
            name="codigo"
            maxlength="6"
            class="form-control mb-2 text-center fw-bold"
            placeholder="Ingresa el código"
            required
        >

        <small class="text-muted d-block mb-2">
            El código expira en 
            <span id="countdown"><?= $tiempoRestante ?></span> segundos
        </small>

        <button
            class="btn btn-recovery w-100 mt-3"
            type="submit"
            id="verify-btn"
        >
            Verificar Código
        </button>
    </form>

    <hr>

    <div class="text-center">
        <a href="recuperar.php">
            <i class="fas fa-arrow-left me-2"></i>Volver al envío de código
        </a>
    </div>
</div>

<script src="../../public/js/verificar_codigo.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js" defer></script>

</body>
</html>
