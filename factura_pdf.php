<?php
// factura_pdf.php
require_once "conexion.php";
require_once "fpdf/fpdf.php";

$num = $_GET['num'] ?? null;
if (!$num) die("Factura no especificada.");

$conn = $conexion;

// recuperar factura
$stmt = $conn->prepare("SELECT * FROM Factura WHERE Número_Factura = ?");
$stmt->bind_param("s", $num);
$stmt->execute();
$fact = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fact) die("Factura no encontrada.");

// recuperar pedidos asociados y productos
$stmt = $conn->prepare("
    SELECT pp.ID_Producto, pp.Cantidad, pr.Descripcion_del_producto, pr.Precio_Unitario
    FROM Pedido p
    JOIN Pedido_Producto pp ON pp.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
    JOIN Producto pr ON pr.idProducto = pp.ID_Producto
    WHERE p.Número_de_factura = ?
");
$stmt->bind_param("s", $num);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($r = $res->fetch_assoc()) $items[] = $r;
$stmt->close();

// crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,8,"Factura Nro: $num",0,1,'C');
$pdf->Ln(4);

$pdf->SetFont('Arial','',11);
$pdf->Cell(100,6,"RUC Empresa: " . $fact['RUC_Empresa'],0,0);
$pdf->Cell(0,6,"DNI Empleado: " . $fact['DNI_Empleado'],0,1);
$pdf->Cell(0,6,"Importe total: S/ " . number_format($fact['Importe_total'],2),0,1);
$pdf->Ln(6);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(30,8,'ID',1);
$pdf->Cell(90,8,'Descripcion',1);
$pdf->Cell(20,8,'Cant.',1,0,'R');
$pdf->Cell(25,8,'P.Unit',1,0,'R');
$pdf->Cell(25,8,'Total',1,1,'R');

$pdf->SetFont('Arial','',10);
foreach ($items as $it) {
    $total_line = $it['Precio_Unitario'] * $it['Cantidad'];
    $pdf->Cell(30,7, $it['ID_Producto'],1);
    $pdf->Cell(90,7, substr($it['Descripcion_del_producto'],0,50),1);
    $pdf->Cell(20,7, $it['Cantidad'],1,0,'R');
    $pdf->Cell(25,7, number_format($it['Precio_Unitario'],2),1,0,'R');
    $pdf->Cell(25,7, number_format($total_line,2),1,1,'R');
}

$pdf->Ln(6);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,"Total Factura: S/ " . number_format($fact['Importe_total'],2),0,1,'R');

$pdf->Output('D', "factura_$num.pdf");
exit;
