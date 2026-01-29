<?php
session_start();
require_once '../../../config/database.php';

// Verificar rol admin
if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

// Filtros por fecha
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

// CONSULTA CORREGIDA (JOIN con pagos)
$sql = "
SELECT 
    p.id,
    u.nombre,
    u.apellidos,
    u.correo,
    p.direccion,
    p.telefono,
    pg.metodo_pago,
    pg.estado_pago,
    p.total,
    e.nombre AS estado,
    p.created_at
FROM pedidos p
JOIN usuarios u ON p.usuario_id = u.id
JOIN estados e ON p.estado_id = e.id
LEFT JOIN pagos pg ON pg.pedido_id = p.id
WHERE 1=1
";

$params = [];

if (!empty($fecha_inicio)) {
    $sql .= " AND p.created_at >= :fecha_inicio ";
    $params['fecha_inicio'] = $fecha_inicio . ' 00:00:00';
}

if (!empty($fecha_fin)) {
    $sql .= " AND p.created_at <= :fecha_fin ";
    $params['fecha_fin'] = $fecha_fin . ' 23:59:59';
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pedidos | Panel Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Pedidos</h2>
        <a href="../index_admin.php" class="btn btn-secondary">← Volver</a>
    </div>

    <!-- Filtro por fecha -->
    <form method="get" class="row g-2 mb-4 align-items-end">
        <div class="col-auto">
            <label class="form-label">Desde</label>
            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div class="col-auto">
            <label class="form-label">Hasta</label>
            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filtrar</button>
            <a href="index.php" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>

    <?php if (count($pedidos) > 0): ?>
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Método Pago</th>
                <th>Estado Pago</th>
                <th>Total</th>
                <th>Estado Pedido</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pedidos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></td>
                <td><?= htmlspecialchars($p['correo']) ?></td>
                <td><?= htmlspecialchars($p['direccion'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['metodo_pago'] ?? 'Sin pago') ?></td>
                <td><?= htmlspecialchars($p['estado_pago'] ?? 'Pendiente') ?></td>
                <td>Bs <?= number_format($p['total'], 2) ?></td>
                <td><?= htmlspecialchars($p['estado']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                <td>
                    <a href="detalle.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">
                        Ver detalle
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning">No hay pedidos registrados.</div>
    <?php endif; ?>

</div>

</body>
</html>
