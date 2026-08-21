<?php
require_once '../conexion.php';
require '../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = conectarDB();

$mes = filter_var($_GET['mes'] ?? date('m'), FILTER_VALIDATE_INT);
$anio = filter_var($_GET['anio'] ?? date('Y'), FILTER_VALIDATE_INT);
$mes = ($mes !== false && $mes >= 1 && $mes <= 12) ? $mes : (int)date('m');
$anio = ($anio !== false && $anio >= 2000 && $anio <= 2100) ? $anio : (int)date('Y');

// ========= INGRESOS =========
$stmtPagos = $conn->prepare("
    SELECT p.monto, p.fecha_pago, p.metodo_pago, f.id AS factura_id, pa.nombre AS cliente
    FROM pagos p
    JOIN facturas f ON p.factura_id = f.id
    JOIN pacientes pa ON f.paciente_id = pa.id

    WHERE MONTH(p.fecha_pago) = ? AND YEAR(p.fecha_pago) = ?
");
$stmtPagos->bind_param('ii', $mes, $anio);
$stmtPagos->execute();
$pagos = $stmtPagos->get_result();

$stmtFacturas = $conn->prepare("
    SELECT f.fecha, f.total, pa.nombre AS cliente
    FROM facturas f
    JOIN pacientes pa ON f.paciente_id = pa.id
    WHERE f.estado_pago = 'pagado' AND f.deuda = 0
    AND f.id NOT IN (SELECT factura_id FROM pagos)
    AND MONTH(f.fecha) = ? AND YEAR(f.fecha) = ?
");
$stmtFacturas->bind_param('ii', $mes, $anio);
$stmtFacturas->execute();
$facturas_directas = $stmtFacturas->get_result();

$total_pagos = 0;
$detalle_pagos = [];
while ($row = $pagos->fetch_assoc()) {
    $detalle_pagos[] = $row;
    $total_pagos += $row['monto'];
}

$total_directas = 0;
$detalle_facturas = [];
while ($row = $facturas_directas->fetch_assoc()) {
    $detalle_facturas[] = $row;
    $total_directas += $row['total'];
}

$ingresos = $total_pagos + $total_directas;

// ========= EGRESOS =========
$stmtEgresos = $conn->prepare("
    SELECT SUM(monto) AS total
    FROM movimientos_caja
    WHERE tipo = 'Egreso' AND id_gasto IS NOT NULL
    AND MONTH(fecha) = ? AND YEAR(fecha) = ?
");
$stmtEgresos->bind_param('ii', $mes, $anio);
$stmtEgresos->execute();
$egresos = $stmtEgresos->get_result()->fetch_assoc()['total'] ?? 0;

// ========= COMPRAS DETALLADAS =========
$stmtCompras = $conn->prepare("
    SELECT c.fecha, pr.nombre AS proveedor, dc.producto, dc.cantidad, dc.costo_unitario
    FROM compras c
    JOIN proveedores pr ON c.id_proveedor = pr.id_proveedor
    JOIN detalle_compra dc ON dc.id_compra = c.id_compra
    WHERE MONTH(c.fecha) = ? AND YEAR(c.fecha) = ?
");
$stmtCompras->bind_param('ii', $mes, $anio);
$stmtCompras->execute();
$compras = $stmtCompras->get_result();



$total_compras = 0;
$detalle_compras = [];
while ($row = $compras->fetch_assoc()) {
    $row['subtotal'] = $row['cantidad'] * $row['costo_unitario'];
    $detalle_compras[] = $row;
    $total_compras += $row['subtotal'];
}

// ========= BALANCE =========
$balance = $ingresos - ($egresos + $total_compras);

// ========= GENERAR EXCEL =========
$spreadsheet = new Spreadsheet();

// === Hoja 1: Resumen ===
$resumen = $spreadsheet->getActiveSheet();
$resumen->setTitle('Resumen Contable');
$resumen->fromArray([
    ['Categoría', 'Monto (C$)'],
    ['👥 Ingresos por clientes', $ingresos],
    ['🧾 Egresos operativos', $egresos],
    ['🛒 Compras de productos', $total_compras],
    ['📌 Balance Final', $balance]
]);

// === Hoja 2: Pagos ===
$pagosSheet = $spreadsheet->createSheet();
$pagosSheet->setTitle('Detalle de Pagos');
$pagosSheet->fromArray(['Cliente', 'Fecha de Pago', 'Monto', 'Método de Pago', 'Factura ID'], NULL, 'A1');
$row = 2;
foreach ($detalle_pagos as $p) {
    $pagosSheet->fromArray([
        $p['cliente'],
        $p['fecha_pago'],
        $p['monto'],
        $p['metodo_pago'],
        $p['factura_id']
    ], NULL, "A$row");
    $row++;
}

// === Hoja 3: Facturas Completas ===
$facturasSheet = $spreadsheet->createSheet();
$facturasSheet->setTitle('Facturas Completas');
$facturasSheet->fromArray(['Cliente', 'Fecha', 'Total'], NULL, 'A1');
$row = 2;
foreach ($detalle_facturas as $f) {
    $facturasSheet->fromArray([
        $f['cliente'],
        $f['fecha'],
        $f['total']
    ], NULL, "A$row");
    $row++;
}

// === Hoja 4: Compras Detalladas ===
$comprasSheet = $spreadsheet->createSheet();
$comprasSheet->setTitle('Detalle de Compras');
$comprasSheet->fromArray(['Fecha', 'Proveedor', 'Producto', 'Cantidad', 'Costo Unitario', 'Subtotal'], NULL, 'A1');
$row = 2;
foreach ($detalle_compras as $c) {
    $comprasSheet->fromArray([
        $c['fecha'],
        $c['proveedor'],
        $c['producto'],
        $c['cantidad'],
        $c['costo_unitario'],
        $c['subtotal']
    ], NULL, "A$row");
    $row++;
}

// === Autoajustar columnas
foreach ($spreadsheet->getAllSheets() as $sheet) {
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// === Descargar
$filename = "Balance_Resumen_{$mes}_{$anio}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
