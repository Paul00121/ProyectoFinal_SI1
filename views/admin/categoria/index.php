<?php
session_start();
require_once '../../../config/database.php';

// Verificar sesión y rol admin
if (!isset($_SESSION['usuario']) || !isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener categorías
$stmt = $pdo->prepare("SELECT * FROM categorias ORDER BY nombre ASC");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Categorías | Panel Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/categoria.css?v=1">
</head>
<body>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Categorías</h2>
        <div>
            <a href="../index_admin.php" class="btn btn-secondary me-2">← Volver</a>
            <a href="crear.php" class="btn btn-primary">+ Crear Categoría</a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if(count($categorias) > 0): ?>
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categorias as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                <td>
                    <a href="editar.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="eliminar.php?id=<?= $cat['id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Seguro deseas eliminar esta categoría?');">
                       Eliminar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No hay categorías registradas.</p>
    <?php endif; ?>

</div>
</body>
</html>
