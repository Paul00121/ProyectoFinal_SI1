<?php
require_once '../../../config/database.php';
require_once '../header.php'; // Header ya inicia la sesión

// Verificar sesión de cliente
if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}

// ID y nombre del cliente desde sesión
$usuario_id = $_SESSION['usuario']['id'] ?? 0;
$usuario_nombre = trim($_SESSION['usuario']['nombre'] ?? 'Cliente');

// Producto seleccionado desde GET (si viene desde productos/index.php)
$producto_seleccionado = intval($_GET['producto_id'] ?? 0);

// Obtener productos disponibles (nombre y precio)
$sql = "SELECT id, nombre, precio FROM productos ORDER BY nombre";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Pedido | Tienda Deportiva</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<!-- CSS cliente general -->
<link rel="stylesheet" href="../assets/css/cliente.css?v=2">
<!-- CSS específico de crear pedido -->
<link rel="stylesheet" href="../assets/css/crear_pedido.css?v=2">
</head>
<body>

<div class="main container">
    <h3 class="mb-4 text-center">🛒 Crear Pedido</h3>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card form-card p-4">
                <form method="post" action="procesar_pedido.php">
                    
                    <!-- Nombre del cliente (solo lectura) -->
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($usuario_nombre) ?>" disabled>
                    </div>

                    <!-- Producto -->
                    <div class="mb-3">
                        <label for="producto_id" class="form-label">Producto</label>
                        <select name="producto_id" id="producto_id" class="form-select" required>
                            <option value="">-- Selecciona un producto --</option>
                            <?php foreach($productos as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($p['id'] == $producto_seleccionado) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?> - Bs <?= number_format($p['precio'],2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Cantidad -->
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" required>
                    </div>

                    <!-- Teléfono -->
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" required>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 submit-btn">🛒 Crear pedido</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../partials/chatbot.php'; ?>
<?php require_once '../footer.php'; ?>

<!-- JS propio -->
<script src="../assets/js/crear_pedido.js"></script>
</body>
</html>
