<?php
session_start();
require_once '../../../config/database.php';

// Verificar sesión y rol admin
if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

// Filtros por fecha
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

// Consulta de pedidos PAGADOS con su pago
$sql = "SELECT 
            p.id,
            p.total,
            p.direccion,
            p.telefono,
            pg.created_at AS fecha_pago,   -- FECHA DEL PAGO
            u.nombre,
            u.apellidos,
            u.correo,
            e.nombre AS estado,
            pg.metodo_pago
        FROM pedidos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN estados e ON p.estado_id = e.id
        LEFT JOIN pagos pg ON pg.pedido_id = p.id
        WHERE p.estado_id = 2";

$params = [];

if (!empty($fecha_inicio)) {
    $sql .= " AND pg.created_at >= :fecha_inicio ";
    $params['fecha_inicio'] = $fecha_inicio . ' 00:00:00';
}

if (!empty($fecha_fin)) {
    $sql .= " AND pg.created_at <= :fecha_fin ";
    $params['fecha_fin'] = $fecha_fin . ' 23:59:59';
}

$sql .= " ORDER BY pg.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes | Panel Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/reportes.css">
</head>
<body>

<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reportes de Pedidos Pagados</h2>
        <a href="../index_admin.php" class="btn btn-secondary">← Volver</a>
    </div>

    <!-- Filtro por fecha -->
    <form method="get" class="row g-2 align-items-end mb-4">
        <div class="col-md-3">
            <label class="form-label">Fecha inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha fin</label>
            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-3">
            <a href="index.php" class="btn btn-secondary w-100">Limpiar</a>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <a 
                    href="exportar_excel.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
                    class="btn btn-success w-100"
                >
                    📊 Exportar a Excel
                </a>
            </div>
        </div>
    </form>

    <!-- Tabla -->
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Total</th>
                <th>Método Pago</th>
                <th>Estado</th>
                <th>Fecha Pago</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($pedidos) > 0): ?>
            <?php foreach ($pedidos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre'].' '.$p['apellidos']) ?></td>
                <td><?= htmlspecialchars($p['correo']) ?></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= htmlspecialchars($p['direccion']) ?></td>
                <td>Bs <?= number_format($p['total'], 2) ?></td>
                <td><?= htmlspecialchars($p['metodo_pago'] ?? '—') ?></td>
                <td><?= htmlspecialchars($p['estado']) ?></td>
                <td><?= isset($p['fecha_pago']) ? date('d/m/Y H:i', strtotime($p['fecha_pago'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="text-center">No se encontraron pedidos pagados</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
