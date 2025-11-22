<?php
// factura.php
require_once "conexion.php";

$numero_factura = $_GET['numero_factura'] ?? null;
if (!$numero_factura) {
    die("Número de factura no proporcionado.");
}

// Query (igual a la que tú mostraste)
$sql = "
SELECT
    f.`Numero_Factura`,
    f.`Tipo_de_moneda`,
    f.`Sub_total_ventas`,
    f.`IGV`,
    f.`Importe_total`,

    e1.`Razon_Social` AS Empresa_Emisora,
    e1.`ID_Direccion` AS Direccion_Empresa,

    emp.`Nombre` AS Empleado_Registro,
    emp.`Correo` AS Empleado_Correo,
    emp.`Cargo` AS Empleado_Cargo,

    e2.`Razon_Social` AS Empresa_Empleado,
    e2.`ID_Direccion` AS Direccion_Empresa_Empleado,

    c.`RUC_Cliente`,
    cn.`Nombre_Cliente`,
    cd.`Direccion_del_Cliente`,

    p.`Nombre_de_la_hoja` AS Nombre_Pedido,
    f.`Fecha_de_emision` AS Fecha_Pedido,

    pprice.`Costo_Total` AS Pedido_Costo_Total,

    pprod.`ID_Pedido_Producto`,
    pprod.`Cantidad`,
    pr.`idProducto`,
    pr.`Descripcion_del_producto`,
    pr.`Precio_Unitario`,
    (pprod.`Cantidad` * pr.`Precio_Unitario`) AS Total_por_producto

FROM Factura f
JOIN Empleado emp ON emp.`DNI` = f.`DNI_Empleado`
JOIN Empresa e1 ON e1.`RUC_Empresa` = f.`RUC_Empresa`
JOIN Empresa e2 ON e2.`RUC_Empresa` = emp.`RUC_Empresa`
JOIN Pedido p ON p.`Numero_de_factura` = f.`Numero_Factura`
JOIN Cliente c ON c.`RUC_Cliente` = p.`RUC_Cliente`
LEFT JOIN Cliente_Nombre cn ON cn.`ID_Nombre_Cliente` = c.`ID_NombreCliente`
LEFT JOIN Cliente_Direccion cd ON cd.`ID_DireccionCliente` = c.`ID_DireccionCliente`
LEFT JOIN Precios_Pedido pprice ON pprice.`NombreDeLaHoja_Pedido` = p.`Nombre_de_la_hoja`
LEFT JOIN Pedido_Producto pprod ON pprod.`NombreDeLaHoja_Pedido` = p.`Nombre_de_la_hoja`
LEFT JOIN Producto pr ON pr.`idProducto` = pprod.`ID_Producto`
WHERE f.`Numero_Factura` = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $numero_factura);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("No se encontró la factura: " . htmlspecialchars($numero_factura));
}
$filas = $res->fetch_all(MYSQLI_ASSOC);
$g = $filas[0];

// Obtener texto de dirección de la empresa (Empresa_Direccion sólo tiene ID)
$direccion_empresa_text = '';
if (!empty($g['Direccion_Empresa'])) {
    $stmt2 = $conexion->prepare("SELECT Direccion FROM Empresa_Direccion WHERE ID_Direccion = ?");
    $stmt2->bind_param("s", $g['Direccion_Empresa']);
    $stmt2->execute();
    $r2 = $stmt2->get_result();
    if ($r2->num_rows > 0) {
        $drow = $r2->fetch_assoc();
        $direccion_empresa_text = $drow['Direccion'];
    }
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Factura <?= htmlspecialchars($g['Numero_Factura']) ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 24px; color:#222; }
.header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #333; padding-bottom:12px; }
.info { max-width:60%; }
.h-ruc { text-align:right; }
.section { margin-top:16px; }
.table { width:100%; border-collapse:collapse; margin-top:12px; }
.table th, .table td { border:1px solid #ddd; padding:8px; text-align:left; }
.table th { background:#f0f0f0; }
.totales { width:320px; margin-left:auto; margin-top:12px; }
.totales div { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #ddd; }
.totales .total { font-weight:bold; font-size:1.1em; border-top:2px solid #000; padding-top:8px; }
.btn { display:inline-block; padding:10px 14px; background:#1a73e8; color:#fff; text-decoration:none; border-radius:6px; margin-top:14px; }
</style>
</head>
<body>

<div class="header">
    <div class="info">
        <h2><?= htmlspecialchars($g['Empresa_Emisora']) ?></h2>
        <div><?= nl2br(htmlspecialchars($direccion_empresa_text ?: $g['Direccion_Empresa'])) ?></div>
    </div>
    <div class="h-ruc">
        <h3>FACTURA</h3>
        <div><strong>RUC Cliente:</strong> <?= htmlspecialchars($g['RUC_Cliente']) ?></div>
        <div><strong>Nº <?= htmlspecialchars($g['Numero_Factura']) ?></strong></div>
    </div>
</div>

<div class="section">
    <strong>Emitido por:</strong> <?= htmlspecialchars($g['Empleado_Registro']) ?> (<?= htmlspecialchars($g['Empleado_Correo']) ?>) - <?= htmlspecialchars($g['Empleado_Cargo']) ?><br>
    <strong>Cliente:</strong> <?= htmlspecialchars($g['Nombre_Cliente'] ?? '') ?> - RUC: <?= htmlspecialchars($g['RUC_Cliente']) ?><br>
    <strong>Dirección cliente:</strong> <?= htmlspecialchars($g['Direccion_del_Cliente'] ?? '') ?><br>
    <strong>Pedido:</strong> <?= htmlspecialchars($g['Nombre_Pedido']) ?> - Fecha: <?= htmlspecialchars($g['Fecha_Pedido']) ?>
</div>

<div class="section">
    <table class="table">
        <thead>
            <tr>
                <th>Producto (ID)</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_calculado = 0;
            foreach ($filas as $row) {
                $total_producto = floatval($row['Total_por_producto'] ?? ($row['Cantidad'] * $row['Precio_Unitario']));
                $total_calculado += $total_producto;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['idProducto']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Descripcion_del_producto']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Cantidad']) . "</td>";
                echo "<td>S/ " . number_format($row['Precio_Unitario'], 2) . "</td>";
                echo "<td>S/ " . number_format($total_producto, 2) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="totales">
    <div><span>Subtotal:</span><span>S/ <?= number_format($g['Sub_total_ventas'], 2) ?></span></div>
    <div><span>IGV:</span><span>S/ <?= number_format($g['IGV'], 2) ?></span></div>
    <div class="total"><span>Total:</span><span>S/ <?= number_format($g['Importe_total'], 2) ?></span></div>
</div>

<div style="clear:both"></div>
<a class="btn" href="javascript:window.print()">Guardar PDF</a>
<a class="btn btn-editar" href="index.php">Volver al inicio</a>
<a class="btn btn-guardar" href="factura_crear.php">Realizar otra Factura</a>

<?php
require_once "footer.php";
?>

