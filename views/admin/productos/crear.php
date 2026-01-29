<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['idr'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['idr'] != 1) {
    header('Location: ../../index.php');
    exit;
}

$error = '';

// Obtener categorías
$stmt = $pdo->prepare("SELECT * FROM categorias ORDER BY nombre ASC");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre']);
    $categoria_id = $_POST['categoria_id'];
    $precio = $_POST['precio'];
    $descuento = $_POST['descuento'] ?: 0;
    $stock_inicial = $_POST['stock'] ?? 0;

    if ($nombre == '' || $precio <= 0 || $categoria_id == '') {
        $error = "Completa todos los campos obligatorios.";
    } else {

        // Imagen
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $imagen = uniqid() . "." . $ext;
            $upload_dir = "../assets/img/productos/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_dir . $imagen);
        }

        try {
            $pdo->beginTransaction();

            // Insertar producto
            $stmt = $pdo->prepare("
                INSERT INTO productos (nombre, categoria_id, precio, descuento, imagen)
                VALUES (:nombre, :categoria_id, :precio, :descuento, :imagen)
            ");
            $stmt->execute([
                'nombre' => $nombre,
                'categoria_id' => $categoria_id,
                'precio' => $precio,
                'descuento' => $descuento,
                'imagen' => $imagen
            ]);

            // Obtener ID del producto recién creado
            $producto_id = $pdo->lastInsertId();

            // Insertar stock
            $stmt = $pdo->prepare("
                INSERT INTO stock (producto_id, cantidad)
                VALUES (:producto_id, :cantidad)
            ");
            $stmt->execute([
                'producto_id' => $producto_id,
                'cantidad' => $stock_inicial
            ]);

            $pdo->commit();

            $_SESSION['success'] = "Producto creado correctamente.";
            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al guardar el producto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Producto | Panel Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/productos.css">
</head>

<body>

<div class="container mt-4">

    <h2 class="mb-4">Agregar Nuevo Producto</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-producto">

        <div class="mb-3">
            <label class="form-label">Nombre del Producto</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-select" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php foreach($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio (Bs)</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descuento (%)</label>
            <input type="number" step="0.01" name="descuento" class="form-control" value="0">
        </div>

        <div class="mb-3">
            <label class="form-label">Stock inicial</label>
            <input type="number" name="stock" class="form-control" value="0" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" class="form-control">
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Guardar Producto</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>

</div>

</body>
</html>
