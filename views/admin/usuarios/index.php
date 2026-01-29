<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

// Obtener usuarios
$stmt = $pdo->query("SELECT u.id, u.nombre, u.apellidos, u.correo, r.nombre as rol, u.verificado
                     FROM usuarios u
                     JOIN roles r ON u.rol_id = r.id
                     ORDER BY u.id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios | Panel Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/usuarios.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Usuarios</h2>
        <div>
            <a href="../index_admin.php" class="btn btn-secondary me-2">← Volver</a>
            <a href="crear.php" class="btn btn-primary">+ Agregar Usuario</a>
        </div>
    </div>

    <div class="users-grid">
        <?php foreach($usuarios as $user): ?>
        <div class="user-card">
            <h3><?= htmlspecialchars($user['nombre']." ".$user['apellidos']) ?></h3>
            <p>Correo: <?= htmlspecialchars($user['correo']) ?></p>
            <p>Rol: <?= htmlspecialchars($user['rol']) ?></p>
            <p class="status">Verificado: <?= $user['verificado'] ? 'Sí' : 'No' ?></p>
            <div class="actions">
                <a href="editar.php?id=<?= $user['id'] ?>" class="btn btn-editar">Editar</a>
                <a href="eliminar.php?id=<?= $user['id'] ?>" class="btn btn-eliminar" onclick="return confirm('¿Eliminar usuario?');">Eliminar</a>
                <?php if(!$user['verificado']): ?>
                <a href="verificar.php?id=<?= $user['id'] ?>" class="btn btn-verificar">Verificar</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(count($usuarios) == 0): ?>
            <p>No hay usuarios registrados.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
