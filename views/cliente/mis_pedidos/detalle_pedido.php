<?php
require_once '../../../config/database.php';

// Verificar sesión de cliente
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header("Location: /tienda_deportiva/views/auth/login.php");
    exit;
}

$usuario_id = $_SESSION['idu'];

// Validar ID del pedido
$pedido_id = intval($_GET['id'] ?? 0);
if ($pedido_id <= 0) {
    die("⚠️ Pedido inválido.");
}

// Obtener datos del pedido
$sql = "SELECT p.id, p.total, p.created_at, p.direccion, p.telefono, e.nombre AS estado
        FROM pedidos p
        INNER JOIN estados e ON p.estado_id = e.id
        WHERE p.id = :pedido_id AND p.usuario_id = :usuario_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':pedido_id' => $pedido_id,
    ':usuario_id' => $usuario_id
]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("⚠️ El pedido no existe o no pertenece al usuario.");
}

// Obtener detalles
$sql_detalles = "SELECT dp.cantidad, dp.precio, dp.descuento, pr.nombre AS producto
                 FROM detalle_pedido dp
                 INNER JOIN productos pr ON dp.producto_id = pr.id
                 WHERE dp.pedido_id = :pedido_id";
$stmt = $pdo->prepare($sql_detalles);
$stmt->execute([':pedido_id' => $pedido_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pago exitoso
$pago_exitoso = isset($_GET['pago']) && $_GET['pago'] === 'exitoso';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle Pedido #<?= $pedido['id'] ?> | Tienda Deportiva</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/cliente.css?v=3">
<link rel="stylesheet" href="/tienda_deportiva/views/cliente/assets/css/detalle_pedido.css?v=3">
</head>
<body>

<?php require_once '../header.php'; ?>

<!-- 🔽 ESPACIO PARA HEADER FIXED -->
<div class="container " style="margin-top: 90px;">

    <h3>Detalle del Pedido #<?= $pedido['id'] ?></h3>

    <?php if($pago_exitoso): ?>
        <div class="alert alert-success">
            ✅ Pago realizado con éxito. Gracias por tu compra.
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['telefono']) ?></p>
        </div>
    </div>

    <h5>Productos del Pedido</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio (Bs)</th>
                <th>Descuento (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_calculado = 0;
            foreach($detalles as $d):
                $subtotal = ($d['cantidad'] * $d['precio']) - $d['descuento'];
                $total_calculado += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($d['producto']) ?></td>
                <td class="text-center"><?= $d['cantidad'] ?></td>
                <td class="text-end"><?= number_format($d['precio'],2) ?></td>
                <td class="text-end"><?= number_format($d['descuento'],2) ?></td>
                <td class="text-end"><?= number_format($subtotal,2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td class="text-end"><strong><?= number_format($total_calculado,2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <a href="index.php" class="btn btn-primary mt-3">← Volver a Mis Pedidos</a>
</div>

<?php require_once '../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
