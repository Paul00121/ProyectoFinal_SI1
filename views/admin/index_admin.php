<?php
session_start();
require_once __DIR__ . '/../../config/database.php'; // Ajusta si tu path es distinto

// Protección: sólo admin (idr == 1)
if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Nombre de usuario para el header (evita arrays)
$adminName = '';
if (isset($_SESSION['usuario'])) {
    if (is_array($_SESSION['usuario'])) {
        $adminName = trim($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos']);
    } else {
        $adminName = $_SESSION['usuario'];
    }
} else {
    $adminName = 'Admin';
}

try {
    // Totales
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();

    // Solo pedidos PAGADOS (estado_id = 2)
    $totalOrders = (int)$pdo->query("
        SELECT COUNT(*) 
        FROM pedidos
    ")->fetchColumn();

    // Ingresos totales de pedidos PAGADOS
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(d.precio * d.cantidad),0)
        FROM detalle_pedido d
        INNER JOIN pedidos p ON d.pedido_id = p.id
        WHERE p.estado_id = 2
    ");
    $totalRevenue = (float)$stmt->fetchColumn();

    // Últimos 6 pedidos PAGADOS
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS order_id,
            p.created_at AS order_date,
            u.nombre,
            u.apellidos,
            e.nombre AS estado
        FROM pedidos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN estados e ON p.estado_id = e.id
        WHERE p.estado_id = 2
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Productos bajo stock (LÓGICA CORREGIDA)
    $stmt = $pdo->prepare("
        SELECT 
            pr.nombre AS product_name,
            s.cantidad AS quantity
        FROM stock s
        INNER JOIN productos pr ON pr.id = s.producto_id
        ORDER BY s.cantidad ASC
        LIMIT 6
    ");
    $stmt->execute();
    $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ventas últimos 7 días (solo pedidos PAGADOS)
    $stmt = $pdo->prepare("
        SELECT 
            to_char(date_trunc('day', p.created_at), 'YYYY-MM-DD') AS day,
            COALESCE(SUM(d.precio * d.cantidad),0) AS total
        FROM pedidos p
        INNER JOIN detalle_pedido d ON d.pedido_id = p.id
        WHERE p.estado_id = 2
          AND p.created_at >= (CURRENT_DATE - INTERVAL '6 days')
        GROUP BY day
        ORDER BY day ASC
    ");
    $stmt->execute();
    $salesByDay = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $totalUsers = $totalProducts = $totalOrders = 0;
    $totalRevenue = 0;
    $recentOrders = [];
    $lowStock = [];
    $salesByDay = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin · Tienda Deportiva</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar bg-dark text-white">
    <div class="sidebar-brand p-3">
        <h4 class="mb-0">Tienda Deportiva</h4>
        <small class="text-muted">Back Office</small>
    </div>
    <nav class="nav flex-column p-2">
        <a href="index_admin.php" class="nav-link text-white"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="categoria/index.php" class="nav-link text-white"><i class="fa fa-list me-2"></i> Categorías</a>
        <a href="productos/index.php" class="nav-link text-white"><i class="fa fa-box-open me-2"></i> Productos</a>
        <a href="pedidos/index.php" class="nav-link text-white"><i class="fa fa-shopping-cart me-2"></i> Pedidos</a>
        <a href="usuarios/index.php" class="nav-link text-white"><i class="fa fa-users me-2"></i> Usuarios</a>
        <a href="reportes/index.php" class="nav-link text-white"><i class="fa fa-chart-line me-2"></i> Reportes</a>
        <div class="mt-3 ps-2">
            <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Cerrar sesión</a>
        </div>
    </nav>
</aside>

<!-- MAIN -->
<main class="main">
    <header class="topbar d-flex justify-content-between align-items-center px-4">
        <div>
            <h2 class="mb-0">Panel de administración</h2>
            <small class="text-muted">Bienvenido, <?= htmlspecialchars($adminName) ?></small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form class="d-flex" role="search">
                <input class="form-control form-control-sm" type="search" placeholder="Buscar..." aria-label="Buscar">
            </form>
            <div class="profile text-end">
                <small class="d-block">Administrador</small>
                <span class="badge bg-secondary"><?= date('Y-m-d') ?></span>
            </div>
        </div>
    </header>

    <section class="container-fluid p-4">
        <!-- STAT CARDS -->
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Usuarios</h6>
                            <h3><?= number_format($totalUsers) ?></h3>
                        </div>
                        <div class="icon bg-primary text-white rounded-circle">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                    <small class="text-muted">Totales registrados</small>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Productos</h6>
                            <h3><?= number_format($totalProducts) ?></h3>
                        </div>
                        <div class="icon bg-success text-white rounded-circle">
                            <i class="fa fa-box-open"></i>
                        </div>
                    </div>
                    <small class="text-muted">En catálogo</small>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Pedidos</h6>
                            <h3><?= number_format($totalOrders) ?></h3>
                        </div>
                        <div class="icon bg-warning text-white rounded-circle">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                    </div>
                    <small class="text-muted">Pedidos</small>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Ingresos</h6>
                            <h3>Bs <?= number_format($totalRevenue, 2) ?></h3>
                        </div>
                        <div class="icon bg-info text-white rounded-circle">
                            <i class="fa fa-coins"></i>
                        </div>
                    </div>
                    <small class="text-muted">Últimos registros</small>
                </div>
            </div>
        </div>

        <!-- Gráficas y tablas -->
        <div class="row mt-4 g-4">
            <div class="col-12 col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Ventas (últimos 7 días)</strong>
                        <small class="text-muted">Visualización</small>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="120"></canvas>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <strong>Últimos pedidos</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pedido ID</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentOrders) === 0): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No hay pedidos pagados</td></tr>
                                <?php else: ?>
                                    <?php foreach($recentOrders as $i => $r): ?>
                                    <tr>
                                        <td><?= $i+1 ?></td>
                                        <td><?= htmlspecialchars($r['order_id']) ?></td>
                                        <td><?= htmlspecialchars(trim($r['nombre'].' '.$r['apellidos'])) ?></td>
                                        <td><?= htmlspecialchars($r['order_date']) ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= htmlspecialchars($r['estado'] ?? 'Pagado') ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Productos con bajo stock</strong></div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php if (count($lowStock) === 0): ?>
                                <li class="list-group-item text-center text-muted">Sin alertas</li>
                            <?php else: ?>
                                <?php foreach($lowStock as $p): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($p['product_name']) ?>
                                        <span class="badge bg-danger"><?= (int)$p['quantity'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><strong>Accesos rápidos</strong></div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="productos/create.php" class="btn btn-outline-primary btn-sm">Nuevo producto</a>
                            <a href="pedidos/index.php" class="btn btn-outline-secondary btn-sm">Gestionar pedidos</a>
                            <a href="reportes/index.php" class="btn btn-outline-success btn-sm">Exportar reportes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <footer class="text-center py-3 small text-muted">
        © <?= date('Y') ?> Tienda Deportiva · Panel administrativo
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script src="assets/js/admin.js"></script>

<script>
const salesDays = <?= json_encode(array_column($salesByDay,'day')) ?>;
const salesTotals = <?= json_encode(array_map(fn($r)=> (float)$r['total'],$salesByDay)) ?>;

let labels = salesDays.length ? salesDays : [];
let data = salesTotals.length ? salesTotals : [];

if (!labels.length) {
    for (let i=6; i>=0; i--) {
        const d = new Date();
        d.setDate(d.getDate()-i);
        labels.push(d.toISOString().slice(0,10));
        data.push(0);
    }
}

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Ventas',
            data: data,
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(54, 162, 235, 0.12)',
            borderColor: 'rgba(54, 162, 235, 1)',
            pointRadius: 3
        }]
    },
    options: { plugins: { legend: { display:false } }, scales: { y:{ beginAtZero:true } } }
});
</script>

</body>
</html>
