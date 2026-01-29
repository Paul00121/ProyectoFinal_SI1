<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

/**
 * Función base para enviar correos con PHPMailer
 */
function enviarCorreo($destinatario, $asunto, $mensaje, $esHTML = true) {

    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'];
        $mail->Port       = $_ENV['SMTP_PORT'];
        $mail->CharSet    = 'UTF-8';

        // Remitente
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);

        // Destinatario
        $mail->addAddress($destinatario);

        // Contenido
        $mail->isHTML($esHTML);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;

        if (!$esHTML) {
            $mail->AltBody = strip_tags($mensaje);
        }

        $mail->send();

        return [
            'success' => true,
            'message' => 'Correo enviado correctamente'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al enviar correo: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Enviar correo de verificación de cuenta
 */
function enviarCorreoVerificacion($correo, $nombre, $token) {

    $urlVerificacion = $_ENV['APP_URL'] . "/views/auth/verificar_email.php?token=" . $token;

    $asunto = "Verifica tu cuenta | Tienda Deportiva";

    $mensaje = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
            }
            .header {
                background: #0b132b;
                color: #ffffff;
                padding: 30px;
                text-align: center;
            }
            .content {
                padding: 35px;
                background-color: #f9f9f9;
            }
            .button {
                display: inline-block;
                padding: 14px 35px;
                background-color: #1c2541;
                color: #ffffff !important;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                margin: 20px 0;
            }
            .warning {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 12px;
                margin: 20px 0;
                font-size: 14px;
            }
            .footer {
                background-color: #eeeeee;
                text-align: center;
                padding: 20px;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🏀 Tienda Deportiva</h1>
                <p>Verificación de Cuenta</p>
            </div>

            <div class='content'>
                <h2>Hola, {$nombre} 👋</h2>
                <p>Gracias por registrarte en <strong>Tienda Deportiva</strong>.</p>
                <p>Para activar tu cuenta y poder iniciar sesión, confirma tu correo electrónico haciendo clic en el botón:</p>

                <div style='text-align:center'>
                    <a href='{$urlVerificacion}' class='button'>✔ Verificar mi cuenta</a>
                </div>

                <div class='warning'>
                    ⏰ Este enlace es válido por <strong>24 horas</strong>.
                </div>

                <p style='font-size:13px;color:#666'>
                    Si no realizaste este registro, puedes ignorar este correo.
                </p>
            </div>

            <div class='footer'>
                © " . date('Y') . " Tienda Deportiva · Proyecto Académico<br>
                No responder este correo
            </div>
        </div>
    </body>
    </html>
    ";

    return enviarCorreo($correo, $asunto, $mensaje, true);
}

/**
 * Enviar código de recuperación de contraseña
 */
function enviarCodigoRecuperacion($correo, $codigo) {

    $asunto = "Código de recuperación | Tienda Deportiva";

    $mensaje = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
            }
            .header {
                background-color: #c1121f;
                color: white;
                padding: 30px;
                text-align: center;
            }
            .content {
                padding: 35px;
                background-color: #f9f9f9;
            }
            .codigo {
                font-size: 36px;
                letter-spacing: 8px;
                font-weight: bold;
                color: #c1121f;
                background-color: #ffffff;
                border: 3px dashed #c1121f;
                padding: 20px;
                text-align: center;
                margin: 25px 0;
                border-radius: 8px;
            }
            .warning {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 12px;
                margin: 20px 0;
                font-size: 14px;
            }
            .footer {
                background-color: #eeeeee;
                text-align: center;
                padding: 20px;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Recuperación de Contraseña</h1>
            </div>

            <div class='content'>
                <p>Has solicitado restablecer tu contraseña en <strong>Tienda Deportiva</strong>.</p>
                <p>Ingresa el siguiente código:</p>

                <div class='codigo'>{$codigo}</div>

                <div class='warning'>
                    ⏰ Este código expirará en <strong>15 minutos</strong>.
                </div>

                <p style='font-size:13px;color:#666'>
                    Si no solicitaste este cambio, ignora este correo.
                </p>
            </div>

            <div class='footer'>
                © " . date('Y') . " Tienda Deportiva · Proyecto Académico
            </div>
        </div>
    </body>
    </html>
    ";

    return enviarCorreo($correo, $asunto, $mensaje, true);
}
