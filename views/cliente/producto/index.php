<?php
require_once '../../../config/database.php';
require_once '../header.php'; // Header inicia sesión y verifica cliente

// ❌ Quitar session_start(); porque ya se inicia en header.php

if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}

$id_categoria = isset($_GET['idc']) ? intval($_GET['idc']) : 0;
$busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';

// Traer categorías
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Traer productos con stock
$sql = "SELECT p.id, p.nombre, p.precio, p.descuento, c.nombre AS categoria,
        COALESCE(s.cantidad,0) AS stock, p.imagen
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN stock s ON s.producto_id = p.id
        WHERE 1=1";
$params = [];
if($id_categoria > 0){
    $sql .= " AND p.categoria_id = :idc";
    $params['idc'] = $id_categoria;
}
if(!empty($busqueda)){
    $sql .= " AND LOWER(p.nombre) LIKE LOWER(:busqueda)";
    $params['busqueda'] = "%$busqueda%";
}
$sql .= " ORDER BY c.nombre, p.nombre";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agregar al carrito
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])){
    $usuario_id = $_SESSION['idu'];
    $producto_id = intval($_POST['producto_id']);

    // Verificar stock
    $stmt = $pdo->prepare("SELECT cantidad FROM stock WHERE producto_id = :pid");
    $stmt->execute(['pid'=>$producto_id]);
    $stock = $stmt->fetchColumn();
    if($stock < 1){
        $_SESSION['error_carrito'] = "No hay stock disponible para este producto.";
        header("Location: index.php");
        exit;
    }

    // Verificar si hay pedido pendiente
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE usuario_id = :uid AND estado_id = 1");
    $stmt->execute(['uid' => $usuario_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$pedido){
        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, estado_id, total) VALUES (:uid, 1, 0) RETURNING id");
        $stmt->execute(['uid' => $usuario_id]);
        $pedido_id = $stmt->fetchColumn();
    } else {
        $pedido_id = $pedido['id'];
    }

    // Obtener precio y descuento del producto
    $stmt = $pdo->prepare("SELECT precio, descuento FROM productos WHERE id = :pid");
    $stmt->execute(['pid' => $producto_id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    $precio_final = max($prod['precio'] - $prod['descuento'],0);

    // Agregar detalle del pedido
    $stmt = $pdo->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio, descuento)
                           VALUES (:pedido_id, :producto_id, 1, :precio, :descuento)");
    $stmt->execute([
        'pedido_id' => $pedido_id,
        'producto_id' => $producto_id,
        'precio' => $precio_final,
        'descuento' => $prod['descuento']
    ]);

    // Actualizar total del pedido
    $stmt = $pdo->prepare("UPDATE pedidos 
                           SET total = COALESCE((SELECT SUM(precio * cantidad) FROM detalle_pedido WHERE pedido_id = :pid),0)
                           WHERE id = :pid");
    $stmt->execute(['pid' => $pedido_id]);

    // Descontar stock
    $stmt = $pdo->prepare("UPDATE stock SET cantidad = cantidad - 1 WHERE producto_id = :pid");
    $stmt->execute(['pid' => $producto_id]);

    header("Location: ../crear_pedido/index.php?producto_id=".$producto_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos | Tienda Deportiva</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/cliente.css">
<link rel="stylesheet" href="../assets/css/productos.css">
</head>
<body style="background: #eef2ff;">

<div class="container mt-5 pt-5">
    <h2 class="mb-4">Productos disponibles</h2>

    <!-- Filtros -->
    <form method="get" class="d-flex gap-2 flex-wrap mb-4 align-items-center">
        <input type="text" name="search" id="searchInput"class="search-box form-control" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>" style="max-width:250px;">
        <select name="idc" class="form-select" style="max-width:180px;" onchange="this.form.submit()">
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

    <!-- GRID PRODUCTOS -->
    <div class="products-grid">
        <?php if(count($productos) > 0): ?>
            <?php foreach($productos as $prod):
                $precio = (float)$prod['precio'];
                $descuento = (float)$prod['descuento'];
                $precio_final = max($precio - $descuento, 0);
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php 
                    $img_path = "../../../views/admin/assets/img/productos/".$prod['imagen'];
                    ?>
                    <?php if($prod['imagen'] && file_exists($img_path)): ?>
                        <img src="/tienda_deportiva/views/admin/assets/img/productos/<?= htmlspecialchars($prod['imagen']) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                    <?php else: ?>
                        <div class="no-image">Sin imagen</div>
                    <?php endif; ?>
                    <?php if($descuento>0): ?>
                        <span class="badge bg-danger position-absolute" style="top:10px; left:10px;">-<?= round(($descuento/$precio)*100) ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                    <div class="category"><?= htmlspecialchars($prod['categoria']) ?></div>
                    <div class="price">
                        <?php if($descuento>0): ?>
                            <span class="text-decoration-line-through text-muted">$<?= number_format($precio,2) ?></span>
                            <span>$<?= number_format($precio_final,2) ?></span>
                        <?php else: ?>
                            <span>$<?= number_format($precio,2) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="stock mb-2">
                        <?php if($prod['stock'] > 0): ?>
                            <span class="text-success">Disponible: <?= $prod['stock'] ?></span>
                        <?php else: ?>
                            <span class="text-danger">Agotado</span>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="index.php" class="product-actions">
                        <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>">
                        <button type="submit" class="btn-add" <?= ($prod['stock'] < 1) ? 'disabled' : '' ?>>🛒 Añadir al carrito</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">No se encontraron productos</div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/producto.js"></script>
</body>
</html>
<?php include __DIR__ . '/../../partials/chatbot.php'; ?>
<?php require_once '../footer.php'; ?>
