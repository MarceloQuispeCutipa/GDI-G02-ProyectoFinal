<?php
session_start();
require_once "dompdf/autoload.inc.php";
use Dompdf\Dompdf;

if (!isset($_SESSION['carrito_backup'])) {
    die("Error: No existe factura para exportar.");
}

$carrito = $_SESSION['carrito_backup'];
$numeroFactura = $_SESSION['numero_factura'];
$cliente = $_SESSION['cliente_factura'];
$subtotal = $_SESSION['subtotal_factura'];
$igv = $_SESSION['igv_factura'];
$total = $_SESSION['total_factura'];

ob_start();
include "plantilla_pdf.php";
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();
$dompdf->stream("Factura_$numeroFactura.pdf", ["Attachment" => true]);
?>
