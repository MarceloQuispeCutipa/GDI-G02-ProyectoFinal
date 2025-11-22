<?php
session_start();
require_once "fpdf/fpdf.php";
require_once "conexion.php";
require_once "header.php";

$carrito = $_SESSION['carrito'];
$ruc = $_SESSION['ruc_cliente'];
$dni_empleado = $_SESSION['dni_empleado'];

$subtotal = array_sum(array_column($carrito, 'subtotal'));
$igv = $subtotal * 0.18;
$total = $subtotal + $igv;

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'FACTURA ELECTRONICA',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(190,10,'Cliente (RUC): '.$ruc,0,1);
$pdf->Cell(190,10,'Vendedor (DNI): '.$dni_empleado,0,1);
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,'Producto',1);
$pdf->Cell(30,10,'Cant.',1);
$pdf->Cell(40,10,'Precio',1);
$pdf->Cell(40,10,'Subtotal',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
foreach ($carrito as $i) {
    $pdf->Cell(80,10,$i['nombre'],1);
    $pdf->Cell(30,10,$i['cantidad'],1);
    $pdf->Cell(40,10,'S/ '.$i['precio'],1);
    $pdf->Cell(40,10,'S/ '.$i['subtotal'],1);
    $pdf->Ln();
}

$pdf->Ln(5);

$pdf->Cell(190,10,'Subtotal: S/ '.number_format($subtotal,2),0,1);
$pdf->Cell(190,10,'IGV (18%): S/ '.number_format($igv,2),0,1);
$pdf->Cell(190,10,'TOTAL: S/ '.number_format($total,2),0,1);

$pdf->Output('D', 'factura.pdf');
exit;
