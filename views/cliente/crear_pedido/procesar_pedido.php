<?php
session_start();
require_once '../../../config/database.php';

// Verificar sesión de cliente
if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

$producto_id = intval($_POST['producto_id'] ?? 0);
$cantidad    = intval($_POST['cantidad'] ?? 1);
$telefono    = trim($_POST['telefono'] ?? '');
$direccion   = trim($_POST['direccion'] ?? '');

if ($producto_id <= 0 || $cantidad <= 0 || empty($telefono) || empty($direccion)) {
    header("Location: index.php?error=datos");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Buscar pedido pendiente
    $stmt = $pdo->prepare("
        SELECT id FROM pedidos 
        WHERE usuario_id = :uid AND estado_id = 1
        LIMIT 1
    ");
    $stmt->execute(['uid' => $usuario_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        // Crear pedido
        $stmt = $pdo->prepare("
            INSERT INTO pedidos (usuario_id, estado_id, total, telefono, direccion)
            VALUES (:uid, 1, 0, :telefono, :direccion)
            RETURNING id
        ");
        $stmt->execute([
            'uid' => $usuario_id,
            'telefono' => $telefono,
            'direccion' => $direccion
        ]);
        $pedido_id = $stmt->fetchColumn();
    } else {
        $pedido_id = $pedido['id'];
    }

    // 2. Obtener producto
    $stmt = $pdo->prepare("SELECT precio, descuento FROM productos WHERE id = :id");
    $stmt->execute(['id' => $producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        throw new Exception("Producto no válido");
    }

    $precio_final = max($producto['precio'] - $producto['descuento'], 0);

    // 3. Insertar detalle
    $stmt = $pdo->prepare("
        INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio, descuento)
        VALUES (:pedido, :producto, :cantidad, :precio, :descuento)
    ");
    $stmt->execute([
        'pedido'    => $pedido_id,
        'producto'  => $producto_id,
        'cantidad'  => $cantidad,
        'precio'    => $precio_final,
        'descuento' => $producto['descuento']
    ]);

    // 4. Actualizar total
    $stmt = $pdo->prepare("
        UPDATE pedidos
        SET total = (
            SELECT SUM(precio * cantidad)
            FROM detalle_pedido
            WHERE pedido_id = :pid
        )
        WHERE id = :pid
    ");
    $stmt->execute(['pid' => $pedido_id]);

    $pdo->commit();

    // Redirigir a Mis Pedidos
    header("Location: ../mis_pedidos/index.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al crear el pedido: " . $e->getMessage();
}
