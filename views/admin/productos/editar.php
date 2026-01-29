<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

/* ===============================
   OBTENER PRODUCTO
================================ */
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute(['id' => $id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: index.php");
    exit;
}

/* ===============================
   OBTENER STOCK
================================ */
$stmt = $pdo->prepare("SELECT cantidad FROM stock WHERE producto_id = :id");
$stmt->execute(['id' => $id]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);
$stock_actual = $stock ? $stock['cantidad'] : 0;

/* ===============================
   OBTENER CATEGORÍAS
================================ */
$categorias = $pdo->query("
    SELECT id, nombre 
    FROM categorias 
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

$error = '';

/* ===============================
   PROCESAR FORMULARIO
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre        = trim($_POST['nombre']);
    $categoria_id  = $_POST['categoria_id'];
    $precio        = $_POST['precio'];
    $descuento     = $_POST['descuento'] ?? 0;
    $nuevo_stock   = $_POST['stock'] ?? 0;

    $ruta_imagen = $producto['imagen'];

    /* Subir nueva imagen */
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nueva = uniqid() . "." . $ext;
        $upload_dir = "../assets/img/productos/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_dir . $imagen_nueva);
        $ruta_imagen = $imagen_nueva;
    }

    try {
        $pdo->beginTransaction();

        /* ===============================
           ACTUALIZAR PRODUCTO
        ================================ */
        $stmt = $pdo->prepare("
            UPDATE productos 
            SET nombre = :nombre,
                categoria_id = :categoria_id,
                precio = :precio,
                descuento = :descuento,
                imagen = :imagen
            WHERE id = :id
        ");

        $stmt->execute([
            'nombre'        => $nombre,
            'categoria_id'  => $categoria_id,
            'precio'        => $precio,
            'descuento'     => $descuento,
            'imagen'        => $ruta_imagen,
            'id'            => $id
        ]);

        /* ===============================
           ACTUALIZAR O INSERTAR STOCK
        ================================ */
        $stmt = $pdo->prepare("SELECT id FROM stock WHERE producto_id = :id");
        $stmt->execute(['id' => $id]);
        $existeStock = $stmt->fetch();

        if ($existeStock) {
            $stmt = $pdo->prepare("
                UPDATE stock 
                SET cantidad = :cantidad
                WHERE producto_id = :producto_id
            ");
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO stock (producto_id, cantidad)
                VALUES (:producto_id, :cantidad)
            ");
        }

        $stmt->execute([
            'producto_id' => $id,
            'cantidad'    => $nuevo_stock
        ]);

        $pdo->commit();

        $_SESSION['success'] = "Producto actualizado correctamente.";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al actualizar el producto.";
        // Para depurar:
        // $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Producto | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/productos.css">
</head>

<body>

<div class="container mt-4">

    <h2 class="mb-4">Editar Producto</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-producto">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($producto['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-select" required>
                <?php foreach($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= $c['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio (Bs)</label>
            <input type="number" step="0.01" name="precio"
                   class="form-control" value="<?= $producto['precio'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descuento (%)</label>
            <input type="number" step="0.01" name="descuento"
                   class="form-control" value="<?= $producto['descuento'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Stock disponible</label>
            <input type="number" name="stock" class="form-control"
                   value="<?= $stock_actual ?>" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Imagen actual</label><br>
            <?php if ($producto['imagen'] && file_exists("../assets/img/productos/".$producto['imagen'])): ?>
                <img src="../assets/img/productos/<?= $producto['imagen'] ?>" width="120" class="mb-2">
            <?php else: ?>
                <div class="no-image">Sin imagen</div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Cambiar imagen</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>

</div>

</body>
</html>
