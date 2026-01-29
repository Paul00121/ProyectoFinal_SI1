<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);

    if ($nombre == '') {
        $error = "El nombre de la categoría es obligatorio.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => $nombre]);
            $_SESSION['success'] = "Categoría creada correctamente.";
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Error al crear la categoría.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Categoría | Panel Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">

    <h2 class="mb-4">Agregar Nueva Categoría</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="form-producto">
        <div class="mb-3">
            <label class="form-label">Nombre de la Categoría</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Guardar Categoría</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

</div>
</body>
</html>
