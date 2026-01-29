<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ============================================
// 1. Verificar sesión del cliente
// ============================================
if (!isset($_SESSION['idu']) || $_SESSION['idr'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}
$usuario_id = $_SESSION['idu'];

// ============================================
// 2. Validar sesión de pago
// ============================================
$pedido_id = $_SESSION['pedido_id_pago'] ?? null;
$paypal_order_id = $_SESSION['paypal_order_id'] ?? null;

if (!$pedido_id || !$paypal_order_id) {
    die("⚠️ No se pudo validar la información del pago.");
}

// ============================================
// 3. Configurar PayPal Sandbox
// ============================================
$clientId = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['PAYPAL_SECRET'] ?? '';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/tienda_deportiva';

if (empty($clientId) || empty($clientSecret)) {
    die("⚠️ Credenciales PayPal no configuradas.");
}

$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

// ============================================
// 4. Capturar el pago
// ============================================
$request = new OrdersCaptureRequest($paypal_order_id);
$request->prefer('return=representation');

try {
    $response = $client->execute($request);
    $estado = $response->result->status ?? '';

    if ($estado !== 'COMPLETED') {
        die("⚠️ El pago no fue completado. Estado: $estado");
    }

    // ============================================
    // 5. Obtener monto pagado
    // ============================================
    $monto = 0;
    if (isset($response->result->purchase_units[0]->payments->captures[0])) {
        $monto = floatval($response->result->purchase_units[0]->payments->captures[0]->amount->value);
    }

    // ============================================
    // 6. Registrar pago en DB
    // ============================================
    $sqlPago = "INSERT INTO pagos
        (pedido_id, metodo_pago, estado_pago, referencia, monto, respuesta, created_at)
        VALUES (:pedido_id, :metodo_pago, :estado_pago, :referencia, :monto, :respuesta, NOW())";
    $stmtPago = $pdo->prepare($sqlPago);
    $stmtPago->execute([
        ':pedido_id' => $pedido_id,
        ':metodo_pago' => 'PayPal',
        ':estado_pago' => 'Pagado',
        ':referencia' => $paypal_order_id,
        ':monto' => $monto,
        ':respuesta' => json_encode($response->result)
    ]);

    // ============================================
    // 7. Actualizar estado del pedido
    // ============================================
    $sqlPedido = "UPDATE pedidos SET estado_id = 2 WHERE id = :pedido_id AND usuario_id = :usuario_id";
    $stmtPedido = $pdo->prepare($sqlPedido);
    $stmtPedido->execute([
        ':pedido_id' => $pedido_id,
        ':usuario_id' => $usuario_id
    ]);

    // ============================================
    // 8. Obtener datos del cliente y del pedido
    // ============================================
    $sqlPedidoDatos = "
        SELECT p.id AS pedido_id, p.created_at, p.total, p.direccion, p.telefono,
               u.nombre, u.apellidos, u.correo
        FROM pedidos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = :pedido_id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sqlPedidoDatos);
    $stmt->execute([':pedido_id' => $pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        die("⚠️ Pedido no encontrado.");
    }

    // Detalles del pedido
    $sqlDetalles = "
        SELECT dp.cantidad, dp.precio, dp.descuento, pr.nombre AS producto
        FROM detalle_pedido dp
        INNER JOIN productos pr ON dp.producto_id = pr.id
        WHERE dp.pedido_id = :pedido_id
    ";
    $stmt = $pdo->prepare($sqlDetalles);
    $stmt->execute([':pedido_id' => $pedido_id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================
    // 9. Generar PDF de factura
    // ============================================
    $html = '
    <div style="font-family: Arial, sans-serif; margin: 0; padding: 0;">
        <h1 style="text-align:center;">Tienda Deportiva</h1>
        <h3 style="text-align:center;">Factura de Compra</h3>
        <hr>
        <p><strong>Cliente:</strong> ' . htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellidos']) . '<br>
           <strong>Correo:</strong> ' . htmlspecialchars($pedido['correo']) . '<br>
           <strong>Pedido #:</strong> ' . $pedido['pedido_id'] . '<br>
           <strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($pedido['created_at'])) . '<br>
           <strong>Dirección:</strong> ' . htmlspecialchars($pedido['direccion']) . '<br>
           <strong>Teléfono:</strong> ' . htmlspecialchars($pedido['telefono']) . '</p>

        <h3>Detalle de Productos</h3>
        <table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse;">
            <tr style="background-color: #f2f2f2;">
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio (Bs)</th>
                <th>Descuento (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>';

    $total = 0;
    foreach ($detalles as $d) {
        $subtotal = ($d['precio'] * $d['cantidad']) - $d['descuento'];
        $total += $subtotal;
        $html .= '<tr>
                    <td>' . htmlspecialchars($d['producto']) . '</td>
                    <td style="text-align:center;">' . $d['cantidad'] . '</td>
                    <td style="text-align:right;">' . number_format($d['precio'], 2) . '</td>
                    <td style="text-align:right;">' . number_format($d['descuento'], 2) . '</td>
                    <td style="text-align:right;">' . number_format($subtotal, 2) . '</td>
                  </tr>';
    }

    $html .= '<tr>
                <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
                <td style="text-align:right;"><strong>' . number_format($total, 2) . '</strong></td>
              </tr>
        </table>

        <h3>Información de Pago</h3>
        <p><strong>Método:</strong> PayPal<br>
           <strong>ID Transacción:</strong> ' . htmlspecialchars($paypal_order_id) . '<br>
           <strong>Monto Pagado (Bs):</strong> ' . number_format($monto, 2) . '<br>
           <strong>Estado:</strong> ' . htmlspecialchars($estado) . '</p>
        <hr>
        <p style="text-align:center; font-size:12px;">Gracias por su compra en Tienda Deportiva - Todos los derechos reservados © ' . date('Y') . '</p>
    </div>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfContenido = $dompdf->output();

    // ============================================
    // 10. Enviar correo con PDF
    // ============================================
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'];
        $mail->Port = $_ENV['SMTP_PORT'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], "Tienda Deportiva");
        $mail->addAddress($pedido['correo'], $pedido['nombre'] . ' ' . $pedido['apellidos']);
        $mail->isHTML(true);
        $mail->Subject = "Factura de Pedido #{$pedido['pedido_id']} - Tienda Deportiva";
        $mail->Body = "<p>Estimado(a) {$pedido['nombre']},</p>
                       <p>Gracias por su compra en Tienda Deportiva.</p>
                       <p>Adjuntamos su factura en formato PDF.</p>";
        $mail->addStringAttachment($pdfContenido, "Factura_Pedido_{$pedido['pedido_id']}.pdf", "base64", "application/pdf");
        $mail->send();
    } catch (Exception $e) {
        // Opcional: registrar el error de correo en logs
    }

    // ============================================
    // 11. Limpiar sesión y redirigir
    // ============================================
    unset($_SESSION['pedido_id_pago'], $_SESSION['paypal_order_id']);
    $_SESSION['pago_exitoso'] = true;
    header("Location: ../cliente/mis_pedidos/detalle_pedido.php?id=$pedido_id&pago=exitoso");
    exit;

} catch (\PayPalHttp\HttpException $ex) {
    echo "<h2>❌ Error al Capturar el Pago</h2>";
    echo "<p>".$ex->getMessage()."</p>";
    exit;
} catch (Exception $e) {
    echo "<h2>❌ Error General</h2>";
    echo "<p>".$e->getMessage()."</p>";
    exit;
}
?>
