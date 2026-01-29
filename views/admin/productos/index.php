<?php
session_start();
require_once '../../../config/database.php'; // $pdo ya definido aquí

// Verificar sesión y rol admin
if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 1) {
    header("Location: ../../auth/login.php");
    exit;
}

$rol = $_SESSION['idr'];

// Filtros
$id_categoria = isset($_GET['idc']) ? intval($_GET['idc']) : 0;
$busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';

// Obtener categorías
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos con stock y categoría
$sql = "SELECT p.id, p.nombre, p.precio, COALESCE(p.descuento,0) as descuento, p.imagen,
               c.nombre AS categoria, COALESCE(s.cantidad,0) AS stock
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN stock s ON p.id = s.producto_id
        WHERE 1=1 ";

$params = [];
if($id_categoria > 0) { 
    $sql .= " AND p.categoria_id = :idc "; 
    $params['idc'] = $id_categoria; 
}
if(!empty($busqueda)) { 
    $sql .= " AND LOWER(p.nombre) LIKE LOWER(:busqueda) "; // PostgreSQL es sensible a mayúsculas
    $params['busqueda'] = "%$busqueda%"; 
}

$sql .= " ORDER BY c.nombre, p.nombre ";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos | Back Office</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- CSS propio -->
<link rel="stylesheet" href="../assets/css/productos.css?v=3">
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Productos</h2>
        <div>
            <a href="../index_admin.php" class="btn btn-secondary">← Volver</a>
            <a href="crear.php" class="btn btn-primary">+ Crear Producto</a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-4">
        <form method="get" class="d-flex gap-2 flex-wrap">
            <input type="text" name="search" class="form-control search-box" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>" style="max-width:300px;">
            <select name="idc" class="form-select" style="max-width:200px;" onchange="this.form.submit()">
                <option value="0">Todas las categorías</option>
                <?php foreach($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($id_categoria == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="index.php" class="btn btn-secondary">Limpiar</a>
        </form>
    </div>

    <!-- Productos Grid -->
    <div class="products-grid">
        <?php if(count($productos) > 0): ?>
            <?php foreach($productos as $prod): 
                $precio = (float)$prod['precio'];
                $descuento = (float)$prod['descuento'];
                $precio_final = max($precio - $descuento, 0);
                $stock = (int)$prod['stock'];
                $stockClass = $stock==0?'bg-danger':($stock<=5?'bg-warning text-dark':'bg-success');
                $stockText = $stock==0?'Agotado':($stock<=5?'Bajo stock':'Disponible');
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if($prod['imagen'] && file_exists("../assets/img/productos/".$prod['imagen'])): ?>
                        <img src="../assets/img/productos/<?= $prod['imagen'] ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                    <?php else: ?>
                        <div class="no-image">Sin imagen</div>
                    <?php endif; ?>
                    <?php if($descuento>0): ?>
                        <span class="position-absolute badge bg-danger" style="top:10px; left:10px;">-<?= round(($descuento/$precio)*100) ?>%</span>
                    <?php endif; ?>
                    <span class="position-absolute badge <?= $stockClass ?>" style="top:10px; right:10px;"><?= $stockText ?></span>
                </div>
                <div class="product-info">
                    <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                    <div class="category"><?= htmlspecialchars($prod['categoria']) ?></div>
                    <div class="price">
                        <?php if($descuento>0): ?>
                            <span class="text-decoration-line-through text-muted">Bs<?= number_format($precio,2) ?></span>
                            <span class="fw-bold">Bs <?= number_format($precio_final,2) ?></span>
                        <?php else: ?>
                            <span class="fw-bold">Bs <?= number_format($precio,2) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="editar.php?id=<?= $prod['id'] ?>" class="btn-editar w-50 text-center">Editar</a>
                        <a href="eliminar.php?id=<?= $prod['id'] ?>" class="btn-eliminar w-50 text-center" onclick="return confirm('¿Seguro deseas eliminar este producto?');">Eliminar</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">No se encontraron productos</div>
        <?php endif; ?>
    </div>

</div>

<!-- JS propio -->
<script src="../assets/js/productos.js"></script>
</body>
</html>
