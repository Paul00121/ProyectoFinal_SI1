<?php
require_once '../../../config/database.php';
require_once '../header.php';

// Verificar sesión de cliente
if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}

$usuario_id = $_SESSION['idu'];

// =====================
// Quitar producto de pedido pendiente
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quitar_detalle_id'], $_POST['pedido_id'])) {
    $detalle_id = intval($_POST['quitar_detalle_id']);
    $pedido_id = intval($_POST['pedido_id']);

    // Verificar que el pedido pertenece al usuario y está pendiente
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id=:pid AND usuario_id=:uid AND estado_id=1");
    $stmt->execute(['pid'=>$pedido_id,'uid'=>$usuario_id]);
    if ($stmt->fetch()) {
        // Obtener producto y cantidad
        $stmt = $pdo->prepare("SELECT producto_id, cantidad FROM detalle_pedido WHERE id=:did");
        $stmt->execute(['did'=>$detalle_id]);
        $det = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($det) {
            // Devolver stock en tabla stock
            $stmt_update = $pdo->prepare("UPDATE stock SET cantidad = cantidad + :cant WHERE producto_id = :pid");
            $stmt_update->bindValue(':cant', $det['cantidad'], PDO::PARAM_INT);
            $stmt_update->bindValue(':pid', $det['producto_id'], PDO::PARAM_INT);
            $stmt_update->execute();

            // Borrar detalle
            $pdo->prepare("DELETE FROM detalle_pedido WHERE id=:did")->execute(['did'=>$detalle_id]);

            // Actualizar total del pedido
            $stmt_total = $pdo->prepare("UPDATE pedidos 
                           SET total = COALESCE((SELECT SUM(precio*cantidad) FROM detalle_pedido WHERE pedido_id=:pid),0) 
                           WHERE id=:pid");
            $stmt_total->execute(['pid'=>$pedido_id]);

            header("Location: index.php");
            exit;
        }
    }
}

// =====================
// Cancelar pedido pendiente
// =====================
if(isset($_GET['cancelar']) && intval($_GET['cancelar'])) {
    $pedido_id = intval($_GET['cancelar']);

    // Solo se puede cancelar pedidos pendientes
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id=:pid AND usuario_id=:uid AND estado_id=1");
    $stmt->execute(['pid'=>$pedido_id,'uid'=>$usuario_id]);
    if ($stmt->fetch()) {
        // Devolver stock de cada detalle
        $stmt = $pdo->prepare("SELECT producto_id, cantidad FROM detalle_pedido WHERE pedido_id=:pid");
        $stmt->execute(['pid'=>$pedido_id]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($detalles as $d){
            $stmt_update = $pdo->prepare("UPDATE stock SET cantidad = cantidad + :cant WHERE producto_id = :pid");
            $stmt_update->bindValue(':cant', $d['cantidad'], PDO::PARAM_INT);
            $stmt_update->bindValue(':pid', $d['producto_id'], PDO::PARAM_INT);
            $stmt_update->execute();
        }
        // Borrar detalles y pedido
        $pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id=:pid")->execute(['pid'=>$pedido_id]);
        $pdo->prepare("DELETE FROM pedidos WHERE id=:pid")->execute(['pid'=>$pedido_id]);

        header("Location: index.php");
        exit;
    }
}

// =====================
// Obtener pedidos del usuario
// =====================
$sql = "SELECT p.id, p.total, p.created_at, e.nombre AS estado 
        FROM pedidos p
        JOIN estados e ON p.estado_id=e.id
        WHERE p.usuario_id=:uid
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['uid'=>$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Pedidos | Tienda Deportiva</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/cliente.css?v=3">
<link rel="stylesheet" href="../assets/css/mis_pedidos.css?v=3">
</head>
<body>

<div class="main container">
    <h3 class="mb-4">Mis Pedidos</h3>

    <?php if($pedidos): ?>
        <div class="row g-3">
            <?php foreach($pedidos as $p):
                // Obtener detalles del pedido
                $stmt = $pdo->prepare("SELECT dp.id AS detalle_id, dp.cantidad, dp.precio, pr.nombre 
                                       FROM detalle_pedido dp
                                       JOIN productos pr ON dp.producto_id=pr.id
                                       WHERE dp.pedido_id=:pid");
                $stmt->execute(['pid'=>$p['id']]);
                $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Badge según estado
                switch($p['estado']){
                    case 'Pendiente': $estadoClass='bg-warning text-dark'; break;
                    case 'Pagado': $estadoClass='bg-success'; break;
                    case 'Cancelado': $estadoClass='bg-danger'; break;
                    default: $estadoClass='bg-secondary'; break;
                }
            ?>
            <div class="col-md-6">
                <div class="card pedido-card shadow-sm h-100">
                    <div class="card-body">
                        <h5>Pedido #<?= $p['id'] ?></h5>
                        <p><small>Fecha: <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></small></p>
                        <p>Total: <strong>Bs <?= number_format($p['total'],2) ?></strong></p>
                        <span class="badge <?= $estadoClass ?> p-2"><?= $p['estado'] ?></span>

                        <table class="table mt-2">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <?php if($p['estado']=='Pendiente'): ?><th>Acción</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($detalles as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['nombre']) ?></td>
                                    <td><?= $d['cantidad'] ?></td>
                                    <td>Bs <?= number_format($d['precio'],2) ?></td>
                                    <?php if($p['estado']=='Pendiente'): ?>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="quitar_detalle_id" value="<?= $d['detalle_id'] ?>">
                                            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">✖ Quitar</button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="mt-2 d-flex flex-column gap-2">
                            <?php if($p['estado']=='Pendiente'): ?>
                                <a href="index.php?cancelar=<?= $p['id'] ?>" class="btn btn-danger w-100">✖ Cancelar pedido</a>
                                <a href="../crear_pedido/index.php" class="btn btn-primary w-100">➕ Añadir producto</a>
                                <a href="../../paypal/procesar_pago.php?id=<?= $p['id'] ?>" class="btn btn-success w-100">🛒 Pagar</a>
                            <?php elseif($p['estado']=='Pagado'): ?>
                                <button class="btn btn-success w-100" disabled>✔ Pagado</button>
                                <a href="detalle_pedido.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary w-100">
                                    🔍 Ver detalle del pedido
                                </a>
                            <?php elseif($p['estado']=='Cancelado'): ?>
                                <button class="btn btn-danger w-100" disabled>✖ Cancelado</button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No tienes pedidos aún.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include __DIR__ . '/../../partials/chatbot.php'; ?>
<?php require_once '../footer.php'; ?>
