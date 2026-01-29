<?php
session_start();
require_once '../../../config/database.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// Verificar sesión y rol admin
if (!isset($_SESSION['idr']) || $_SESSION['idr'] != 1) {
    header("Location: ../../index.php");
    exit;
}

// Filtros por fecha
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$sql = "SELECT 
            p.id,
            p.total,
            p.direccion,
            p.telefono,
            pg.created_at AS fecha_pago,
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

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Pedidos Pagados');

// Encabezados
$headers = ['ID', 'Cliente', 'Correo', 'Teléfono', 'Dirección', 'Total', 'Método Pago', 'Estado', 'Fecha Pago'];
$sheet->fromArray($headers, NULL, 'A1');

// Estilo encabezados
$sheet->getStyle('A1:I1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
    'alignment' => ['horizontal' => 'center'],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

// Datos
$row = 2;
foreach ($pedidos as $p) {
    $sheet->setCellValue("A$row", $p['id']);
    $sheet->setCellValue("B$row", $p['nombre'].' '.$p['apellidos']);
    $sheet->setCellValue("C$row", $p['correo']);
    $sheet->setCellValue("D$row", $p['telefono']);
    $sheet->setCellValue("E$row", $p['direccion']);
    
    // Total como número y moneda
    $sheet->setCellValue("F$row", $p['total']);
    $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode('"BS "#,##0.00');

    $sheet->setCellValue("G$row", $p['metodo_pago'] ?? '—');
    $sheet->setCellValue("H$row", $p['estado']);
    
    // Fecha pago
    if (!empty($p['fecha_pago'])) {
        $fechaExcel = Date::PHPToExcel(strtotime($p['fecha_pago']));
        $sheet->setCellValue("I$row", $fechaExcel);
        $sheet->getStyle("I$row")->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
    } else {
        $sheet->setCellValue("I$row", '—');
    }

    // Bordes de todas las celdas
    $sheet->getStyle("A$row:I$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $row++;
}

// Autoajustar columnas
foreach (range('A','I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Activar filtros automáticos
$sheet->setAutoFilter("A1:I1");

// Descargar Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reportes_pedidos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
