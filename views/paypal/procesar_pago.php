<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

/*
|--------------------------------------------------------------------------
| 1. Verificar sesión cliente
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['usuario']) || $_SESSION['idr'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}

$usuario_id = $_SESSION['idu'];

/*
|--------------------------------------------------------------------------
| 2. Credenciales PayPal (.env)
|--------------------------------------------------------------------------
*/
$clientId     = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['PAYPAL_SECRET'] ?? '';
$appUrl       = $_ENV['APP_URL'] ?? 'http://localhost/tienda_deportiva';

if (empty($clientId) || empty($clientSecret)) {
    die("⚠️ Error: Credenciales PayPal no configuradas.");
}

/*
|--------------------------------------------------------------------------
| 3. Configurar entorno Sandbox
|--------------------------------------------------------------------------
*/
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

/*
|--------------------------------------------------------------------------
| 4. Validar pedido (CORREGIDO)
|--------------------------------------------------------------------------
*/
$pedido_id = intval($_GET['id'] ?? 0);
if ($pedido_id <= 0) {
    die("⚠️ Pedido inválido.");
}

/*
|--------------------------------------------------------------------------
| 5. Obtener pedido (solo pendiente y del usuario)
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT 
        p.id,
        p.total
    FROM pedidos p
    WHERE p.id = :pedido_id
      AND p.usuario_id = :usuario_id
      AND p.estado_id = 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':pedido_id'  => $pedido_id,
    ':usuario_id' => $usuario_id
]);

$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("⚠️ El pedido no existe, no te pertenece o ya fue pagado.");
}

$total = round($pedido['total'], 2);

if ($total <= 0) {
    die("⚠️ El pedido no tiene un monto válido.");
}

/*
|--------------------------------------------------------------------------
| 6. Guardar pedido en sesión
|--------------------------------------------------------------------------
*/
$_SESSION['pedido_id_pago'] = $pedido_id;

/*
|--------------------------------------------------------------------------
| 7. Crear orden PayPal
|--------------------------------------------------------------------------
*/
$request = new OrdersCreateRequest();
$request->prefer('return=representation');

$request->body = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "reference_id" => "PEDIDO-$pedido_id",
        "description"  => "Pedido #$pedido_id - Tienda Deportiva",
        "amount" => [
            "currency_code" => "USD",
            "value" => number_format($total, 2, '.', '')
        ]
    ]],
    "application_context" => [
        "brand_name"  => "Tienda Deportiva",
        "landing_page"=> "LOGIN",
        "user_action" => "PAY_NOW",
        "return_url"  => $appUrl . "/views/paypal/verificar_pago.php",
        "cancel_url"  => $appUrl . "/views/paypal/cancelado.php"
    ]
];

/*
|--------------------------------------------------------------------------
| 8. Ejecutar orden y redirigir
|--------------------------------------------------------------------------
*/
try {
    $response = $client->execute($request);

    $_SESSION['paypal_order_id'] = $response->result->id;

    foreach ($response->result->links as $link) {
        if ($link->rel === 'approve') {
            header("Location: " . $link->href);
            exit;
        }
    }

    die("⚠️ No se pudo obtener la URL de aprobación de PayPal.");

} catch (Exception $e) {

    echo "<h2>Error al procesar el pago</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";

    echo "<hr><pre>";
    echo "Pedido ID: $pedido_id\n";
    echo "Usuario ID: $usuario_id\n";
    echo "Total USD: $total\n";
    echo "</pre>";
}
