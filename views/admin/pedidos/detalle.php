<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

// Protección: solo admin (idr == 1)
if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener ID del pedido
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pedido_id <= 0) {
    header("Location: index.php");
    exit();
}

// Obtener info del pedido y fecha de pago
$stmt = $pdo->prepare("
    SELECT 
        p.id AS pedido_id,
        p.created_at AS fecha_pedido,
        pg.created_at AS fecha_pago,
        p.total,
        u.nombre,
        u.apellidos,
        e.nombre AS estado,
        pg.metodo_pago
    FROM pedidos p
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN estados e ON p.estado_id = e.id
    LEFT JOIN pagos pg ON pg.pedido_id = p.id
    WHERE p.id=:pid
");
$stmt->execute(['pid'=>$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<h3>Pedido no encontrado.</h3>";
    exit();
}

// Obtener detalles del pedido
$stmt = $pdo->prepare("
    SELECT dp.cantidad, dp.precio, pr.nombre
    FROM detalle_pedido dp
    JOIN productos pr ON dp.producto_id = pr.id
    WHERE dp.pedido_id=:pid
");
$stmt->execute(['pid'=>$pedido_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatoFecha($fecha) {
    if (empty($fecha)) return '—';
    return date('d/m/Y H:i', strtotime($fecha));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle Pedido #<?= $pedido['pedido_id'] ?> | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">

    <h3>Detalle del Pedido #<?= $pedido['pedido_id'] ?></h3>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellidos']) ?></p>
    <p><strong>Fecha Pedido:</strong> <?= formatoFecha($pedido['fecha_pedido']) ?></p>
    <p><strong>Fecha Pago:</strong> <?= formatoFecha($pedido['fecha_pago']) ?></p>
    <p><strong>Método de Pago:</strong> <?= htmlspecialchars($pedido['metodo_pago'] ?? '—') ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalCalculado = 0;
            foreach($detalles as $d): 
                $subtotal = $d['precio'] * $d['cantidad'];
                $totalCalculado += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><?= $d['cantidad'] ?></td>
                <td><?= number_format($d['precio'],2) ?></td>
                <td><?= number_format($subtotal,2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total (Bs)</th>
                <th><?= number_format($totalCalculado,2) ?></th>
            </tr>
        </tfoot>
    </table>

    <a href="index.php" class="btn btn-secondary mt-3">← Volver a pedidos</a>
</div>
</body>
</html>
