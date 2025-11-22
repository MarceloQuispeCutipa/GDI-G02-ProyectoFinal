<?php
// pedidos_detalle.php
session_start();
require_once "conexion.php";
require_once "header.php";

$nombre = $_GET['nombre'] ?? null;
if (!$nombre) {
    die("Nombre de pedido no indicado.");
}

// Cabecera del pedido: pedido + cliente + empleado + factura + precios (Precios_Pedido)
$sqlHead = "
SELECT p.Nombre_de_la_hoja, p.RUC_Cliente,
       cn.Nombre_Cliente, cd.Direccion_del_Cliente,
       p.DNI_Empleado, e.Nombre AS Empleado_Nombre,
       p.Numero_de_factura, f.Fecha_de_emision,
       pp.Costo_Total, pp.Precio_sin_IGV, pp.Precio_total
FROM Pedido p
LEFT JOIN Cliente c ON c.RUC_Cliente = p.RUC_Cliente
LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
LEFT JOIN Cliente_Direccion cd ON cd.ID_DireccionCliente = c.ID_DireccionCliente
LEFT JOIN Empleado e ON e.DNI = p.DNI_Empleado
LEFT JOIN Factura f ON f.Numero_Factura = p.Numero_de_factura
LEFT JOIN Precios_Pedido pp ON pp.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
WHERE p.Nombre_de_la_hoja = ?
LIMIT 1
";
$stmt = $conexion->prepare($sqlHead);
$stmt->bind_param("s", $nombre);
$stmt->execute();
$head = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$head) {
    die("Pedido no encontrado.");
}

// Productos del pedido
$sqlItems = "
SELECT pd.ID_Pedido_Producto, pd.Cantidad, pr.idProducto, pr.Descripcion_del_producto, pr.Unidad, pr.Precio_Unitario
FROM Pedido_Producto pd
LEFT JOIN Producto pr ON pr.idProducto = pd.ID_Producto
WHERE pd.NombreDeLaHoja_Pedido = ?
ORDER BY pd.ID_Pedido_Producto
";
$stmt2 = $conexion->prepare($sqlItems);
$stmt2->bind_param("s", $nombre);
$stmt2->execute();
$items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// calcular subtotal (si Precios_Pedido no existe usamos suma de items)
$subtotal_calc = 0.0;
foreach ($items as $it) {
    $subtotal_calc += floatval($it['Precio_Unitario']) * intval($it['Cantidad']);
}
$subtotal_calc = round($subtotal_calc, 2);

// Valores guardados en Precios_Pedido (si existen)
$costo_reg = $head['Costo_Total'] ?? null;
$precio_sin_igv_reg = $head['Precio_sin_IGV'] ?? null;
$precio_total_reg = $head['Precio_total'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Detalle Pedido <?= htmlspecialchars($nombre) ?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="contenedor">
    <h2>Pedido <?= htmlspecialchars($nombre) ?></h2>

    <div class="formulario">
        <strong>Cliente:</strong> <?= htmlspecialchars($head['Nombre_Cliente'] ?? '') ?> (<?= htmlspecialchars($head['RUC_Cliente']) ?>)<br>
        <strong>Dirección:</strong> <?= htmlspecialchars($head['Direccion_del_Cliente'] ?? '-') ?><br>
        <strong>Empleado:</strong> <?= htmlspecialchars($head['Empleado_Nombre'] ?? $head['DNI_Empleado']) ?> (<?= htmlspecialchars($head['DNI_Empleado']) ?>)<br>
        <strong>Factura vinculada:</strong> <?= htmlspecialchars($head['Numero_de_factura']) ?> | Fecha: <?= htmlspecialchars($head['Fecha_de_emision']) ?><br>
    </div>

    <h3>Productos</h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Descripción</th><th>Unidad</th><th>Cantidad</th><th>PU</th><th>Total</th></tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6">No hay productos en este pedido.</td></tr>
            <?php else: foreach ($items as $it): 
                $line = floatval($it['Precio_Unitario']) * intval($it['Cantidad']);
            ?>
                <tr>
                    <td><?= htmlspecialchars($it['idProducto']) ?></td>
                    <td><?= htmlspecialchars($it['Descripcion_del_producto']) ?></td>
                    <td><?= htmlspecialchars($it['Unidad']) ?></td>
                    <td><?= htmlspecialchars($it['Cantidad']) ?></td>
                    <td>S/ <?= number_format($it['Precio_Unitario'],2) ?></td>
                    <td>S/ <?= number_format($line,2) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="totales">
        <div><strong>Subtotal calculado:</strong> S/ <?= number_format($subtotal_calc,2) ?></div>
        <div><strong>Valores registrados (Precios_Pedido):</strong></div>
        <div>Costo_Total: <?= $costo_reg !== null ? 'S/ ' . number_format((float)$costo_reg,2) : '-' ?></div>
        <div>Precio_sin_IGV: <?= $precio_sin_igv_reg !== null ? 'S/ ' . number_format((float)$precio_sin_igv_reg,2) : '-' ?></div>
        <div>Precio_total: <?= $precio_total_reg !== null ? 'S/ ' . number_format((float)$precio_total_reg,2) : '-' ?></div>
    </div>

    <p style="margin-top:12px;"><a class="btn" href="pedidos.php">Volver al listado</a></p>
</div>

<?php require_once 'footer.php'; ?>
</body>
</html>
