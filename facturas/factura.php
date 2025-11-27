<?php
require_once __DIR__ . '/../config/conexion.php';

$numero_factura = $_GET['numero_factura'] ?? null;
if (!$numero_factura) {
    die("Número de factura no proporcionado.");
}

$sql = "
SELECT
    f.Numero_Factura,
    f.Tipo_de_moneda,
    f.Sub_total_ventas,
    f.IGV,
    f.Importe_total,
    f.Fecha_de_emision,

    e1.Razon_Social AS Empresa_Emisora,
    e1.ID_Direccion AS Direccion_Empresa,

    emp.Nombre AS Empleado_Registro,
    emp.Correo AS Empleado_Correo,

    c.RUC_Cliente,
    cn.Nombre_Cliente,
    cd.Direccion_del_Cliente,

    p.Nombre_de_la_hoja AS Nombre_Pedido,

    pprod.Cantidad,
    pr.idProducto,
    pr.Descripcion_del_producto,
    pr.Precio_Unitario,
    (pprod.Cantidad * pr.Precio_Unitario) AS Total_por_producto

FROM Factura f
JOIN Empleado emp ON emp.DNI = f.DNI_Empleado
JOIN Empresa e1 ON e1.RUC_Empresa = f.RUC_Empresa
JOIN Pedido p ON p.Numero_de_factura = f.Numero_Factura
JOIN Cliente c ON c.RUC_Cliente = p.RUC_Cliente
LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
LEFT JOIN Cliente_Direccion cd ON cd.ID_DireccionCliente = c.ID_DireccionCliente
LEFT JOIN Pedido_Producto pprod ON pprod.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
LEFT JOIN Producto pr ON pr.idProducto = pprod.ID_Producto
WHERE f.Numero_Factura = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $numero_factura);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("No se encontró la factura.");
}
$filas = $res->fetch_all(MYSQLI_ASSOC);
$g = $filas[0];

// Obtener texto de dirección de la empresa
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
<title>Factura <?php echo htmlspecialchars($g['Numero_Factura']); ?></title>
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
.nota { font-size:0.85em; margin-top:8px; color:#555; }
</style>
</head>
<body>

<div class="header">
    <div class="info">
        <h2><?php echo htmlspecialchars($g['Empresa_Emisora']); ?></h2>
        <div><?php echo nl2br(htmlspecialchars($direccion_empresa_text ?: $g['Direccion_Empresa'])); ?></div>
    </div>
    <div class="h-ruc">
        <h3>FACTURA</h3>
        <div><strong>RUC Cliente:</strong> <?php echo htmlspecialchars($g['RUC_Cliente']); ?></div>
        <div><strong>Nº <?php echo htmlspecialchars($g['Numero_Factura']); ?></strong></div>
        <div><strong>Fecha:</strong> <?php echo htmlspecialchars($g['Fecha_de_emision']); ?></div>
    </div>
</div>

<div class="section">
    <strong>Cliente:</strong> <?php echo htmlspecialchars($g['Nombre_Cliente'] ?? ''); ?> - RUC: <?php echo htmlspecialchars($g['RUC_Cliente']); ?><br>
    <strong>Dirección:</strong> <?php echo htmlspecialchars($g['Direccion_del_Cliente'] ?? ''); ?><br>
    <strong>Atendido por:</strong> <?php echo htmlspecialchars($g['Empleado_Registro']); ?> (<?php echo htmlspecialchars($g['Empleado_Correo']); ?>)<br>
    <strong>Pedido:</strong> <?php echo htmlspecialchars($g['Nombre_Pedido']); ?><br>
    <strong>Moneda:</strong> <?php echo htmlspecialchars($g['Tipo_de_moneda']); ?><br>
</div>

<div class="section">
    <h3>Detalle</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio unitario (sin IGV)</th>
                <th>Total sin IGV</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filas as $item): ?>
                <?php if (!empty($item['idProducto'])): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['idProducto']); ?></td>
                        <td><?php echo htmlspecialchars($item['Descripcion_del_producto']); ?></td>
                        <td><?php echo (int)$item['Cantidad']; ?></td>
                        <td><?php echo number_format($item['Precio_Unitario'], 2); ?></td>
                        <td><?php echo number_format($item['Total_por_producto'], 2); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="totales">
    <div><span>Subtotal (sin IGV):</span><span><?php echo number_format($g['Sub_total_ventas'], 2); ?></span></div>
    <div><span>IGV (18%):</span><span><?php echo number_format($g['IGV'], 2); ?></span></div>
    <div class="total"><span>Total con IGV:</span><span><?php echo number_format($g['Importe_total'], 2); ?></span></div>
</div>

<a class="btn" href="javascript:window.print();">Imprimir / Guardar como PDF</a>
<div class="nota">
    Para descargar esta factura en PDF, use la opción de impresión del navegador y elija "Guardar como PDF".
</div>

</body>
</html>
