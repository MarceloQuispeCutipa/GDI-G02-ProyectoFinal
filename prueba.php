<?php
session_start();
if (!isset($_SESSION['carrito_backup'])) {
    die("No hay datos de factura para mostrar.");
}

$cliente = $_SESSION['cliente_factura'];
$empresa = $_SESSION['empresa_factura'];

$numeroFactura = $_SESSION['numeroFactura'];
$fecha = date("d/m/Y");
$items = $_SESSION['carrito_backup'];

$subtotal = $_SESSION['subtotal_factura'];
$igv = $_SESSION['igv_factura'];
$total = $_SESSION['total_factura'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura <?= $numeroFactura ?></title>

<style>
body{
    font-family: Arial, sans-serif;
    margin: 0;
    background: #fff;
}

.contenedor{
    width: 900px;
    margin: auto;
    padding: 20px;
    border: 1px solid #000;
}

.header{
    display: flex;
    justify-content: space-between;
    border-bottom: 2px solid #000;
    padding-bottom: 10px;
}

.empresa{
    width: 60%;
    font-size: 13px;
}

.cuadro-ruc{
    width: 35%;
    border: 2px solid #000;
    text-align: center;
    font-size: 14px;
}

.cuadro-ruc h2{
    margin: 5px 0;
}

.section{
    margin-top: 15px;
    font-size: 13px;
}

.table-productos{
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.table-productos th,
.table-productos td{
    border: 1px solid #000;
    padding: 6px;
    font-size: 12px;
}

.totales-box{
    width: 40%;
    float: right;
    margin-top: 10px;
}

.totales-box table{
    width: 100%;
    border-collapse: collapse;
}

.totales-box td{
    border: 1px solid #000;
    padding: 5px;
    font-size: 12px;
}

.pie{
    text-align: center;
    font-size: 12px;
    margin-top: 40px;
    font-style: italic;
}
</style>

</head>

<body>

<div class="contenedor">

    <!-- HEADER -->
    <div class="header">
        <div class="empresa">
            <strong><?= $empresa['Razon_Social'] ?></strong><br>
            <?= $empresa['Direccion'] ?><br>
            <?= $empresa['Departamento'] ?> - <?= $empresa['Provincia'] ?> - <?= $empresa['Distrito'] ?>
        </div>

        <div class="cuadro-ruc">
            <h2>FACTURA ELECTRÓNICA</h2>
            <strong>RUC: <?= $empresa['RUC_Empresa'] ?></strong><br>
            <strong>E001-<?= $numeroFactura ?></strong>
        </div>
    </div>

    <!-- DATOS -->
    <div class="section">
        <strong>Fecha de Emisión:</strong> <?= $fecha ?><br>
        <strong>Señor(es):</strong> <?= $cliente['Nombre_Cliente'] ?><br>
        <strong>RUC:</strong> <?= $cliente['RUC_Cliente'] ?><br>
        <strong>Dirección del Cliente:</strong> <?= $cliente['Direccion_del_Cliente'] ?><br>
        <strong>Tipo de Moneda:</strong> SOLES<br>
        <strong>Observación:</strong> <br>
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <table class="table-productos">
        <thead>
            <tr>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Descripción</th>
                <th>Valor Unitario</th>
                <th>ICBPER</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $item): ?>
            <tr>
                <td><?= $item['cantidad'] ?></td>
                <td><?= $item['unidad'] ?></td>
                <td><?= $item['descripcion'] ?></td>
                <td><?= number_format($item['precio'], 2) ?></td>
                <td>0.00</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TOTALES -->
    <div class="totales-box">
        <table>
            <tr>
                <td>Sub Total Ventas :</td>
                <td>S/ <?= number_format($subtotal, 2) ?></td>
            </tr>
            <tr>
                <td>IGV :</td>
                <td>S/ <?= number_format($igv, 2) ?></td>
            </tr>
            <tr>
                <td>Importe Total :</td>
                <td><strong>S/ <?= number_format($total, 2) ?></strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- PIE -->
    <div class="pie">
        Esta es una representación impresa de la factura electrónica generada en el Sistema de SUNAT.
    </div>

</div>

</body>
</html>
