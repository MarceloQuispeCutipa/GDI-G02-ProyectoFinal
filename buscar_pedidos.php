<?php
require_once "conexion.php";

$codigo = $_GET['codigo'] ?? "";
$fecha_inicio = $_GET['fecha_inicio'] ?? "";
$fecha_fin = $_GET['fecha_fin'] ?? "";

$query = "
SELECT 
    p.Nombre_de_la_hoja,
    p.Fecha_de_emision,
    
    c.RUC_Cliente,
    cn.Nombre_Cliente,
    cd.Direccion_del_Cliente,

    e.Nombre AS Nombre_Empleado,
    e.Cargo AS Cargo_Empleado,

    f.Número_Factura,
    f.Importe_total,
    f.Tipo_de_moneda,
    f.IGV,
    f.Sub_total_ventas,

    pr.Costo_Total,
    pr.Precio_sin_IGV,
    pr.Precio_total

FROM Pedido p
INNER JOIN Cliente c ON p.RUC_Cliente = c.RUC_Cliente
LEFT JOIN Cliente_Nombre cn ON c.ID_NombreCliente = cn.ID_Nombre_Cliente
LEFT JOIN Cliente_Direccion cd ON c.ID_DireccionCliente = cd.ID_DireccionCliente
INNER JOIN Empleado e ON p.DNI_Empleado = e.DNI
INNER JOIN Factura f ON p.Número_de_factura = f.Número_Factura
LEFT JOIN Precios_Pedido pr ON pr.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
WHERE 1=1
";

$types = "";
$params = [];

if (!empty($codigo)) {
    $query .= " AND p.Nombre_de_la_hoja = ? ";
    $types .= "s";
    $params[] = $codigo;
}

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $query .= " AND p.Fecha_de_emision BETWEEN ? AND ? ";
    $types .= "ss";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
}

$stmt = $conexion->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda de Pedidos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <h1>Buscar Pedidos</h1>
    <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="clientes.php">Clientes</a></li>
            <li><a href="productos.php">Productos</a></li>
            <li><a href="buscar_pedidos.php">Pedidos</a></li>
        </ul>
    </nav>
</header>

<main class="contenedor">

    <h2>Buscar Pedido por Código o Fecha</h2>

    <form method="GET" class="formulario">

        <label>Código (Nombre de la hoja):</label>
        <input type="text" name="codigo" value="<?= htmlspecialchars($codigo) ?>">

        <label>Fecha inicio:</label>
        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">

        <label>Fecha fin:</label>
        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">

        <button class="btn btn-crear" type="submit">Buscar</button>
    </form>

    <hr><br>

<?php if ($resultado->num_rows > 0): ?>
    <?php while ($row = $resultado->fetch_assoc()): ?>

        <div class="tarjeta" style="max-width:900px; margin:auto; text-align:left;">
            <h3 style="text-align:center;">Pedido: <?= $row['Nombre_de_la_hoja'] ?></h3>

            <p><b>Fecha de emisión:</b> <?= $row['Fecha_de_emision'] ?></p>

            <h4>Cliente</h4>
            <p><b>RUC:</b> <?= $row['RUC_Cliente'] ?></p>
            <p><b>Nombre:</b> <?= $row['Nombre_Cliente'] ?></p>
            <p><b>Dirección:</b> <?= $row['Direccion_del_Cliente'] ?></p>

            <h4>Empleado</h4>
            <p><b>Nombre:</b> <?= $row['Nombre_Empleado'] ?></p>
            <p><b>Cargo:</b> <?= $row['Cargo_Empleado'] ?></p>

            <h4>Factura</h4>
            <p><b>Número:</b> <?= $row['Número_Factura'] ?></p>
            <p><b>Importe total:</b> <?= $row['Importe_total'] ?></p>
            <p><b>IGV:</b> <?= $row['IGV'] ?></p>
            <p><b>Subtotal ventas:</b> <?= $row['Sub_total_ventas'] ?></p>

            <h4>Precios del Pedido</h4>
            <p><b>Costo Total:</b> <?= $row['Costo_Total'] ?></p>
            <p><b>Precio sin IGV:</b> <?= $row['Precio_sin_IGV'] ?></p>
            <p><b>Precio total:</b> <?= $row['Precio_total'] ?></p>

            <h4>Productos</h4>

            <?php
            $stmt2 = $conexion->prepare("
                SELECT 
                    pp.Cantidad,
                    prod.Descripcion_del_producto,
                    prod.Unidad,
                    prod.Precio_Unitario
                FROM Pedido_Producto pp
                INNER JOIN Producto prod ON pp.ID_Producto = prod.idProducto
                WHERE pp.NombreDeLaHoja_Pedido = ?
            ");
            $stmt2->bind_param("s", $row['Nombre_de_la_hoja']);
            $stmt2->execute();
            $productos = $stmt2->get_result();
            ?>

            <table>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Precio Unitario</th>
                </tr>
                <?php while ($p = $productos->fetch_assoc()): ?>
                    <tr>
                        <td><?= $p['Descripcion_del_producto'] ?></td>
                        <td><?= $p['Cantidad'] ?></td>
                        <td><?= $p['Unidad'] ?></td>
                        <td><?= $p['Precio_Unitario'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <br><hr><br>

    <?php endwhile; ?>

<?php else: ?>
    <p style="text-align:center;">No se encontraron pedidos.</p>
<?php endif; ?>

</main>

<footer>
    <p>©2025 - Sistema DARISA</p>
</footer>

</body>
</html>
