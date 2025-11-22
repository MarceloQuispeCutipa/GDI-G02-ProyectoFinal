<?php
session_start();
require_once "conexion.php";

/* Normalizar variable de conexión */
if (isset($conexion) && $conexion instanceof mysqli) {
    $conn = $conexion;
} else {
    die("Error: No se pudo establecer conexión con la base de datos.");
}

/* Verificar que haya carrito */
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    die("Error: El carrito está vacío.");
}

/* Recibir datos del formulario */
if (!isset($_POST['dni_empleado'], $_POST['ruc_cliente'])) {
    die("Error: faltan datos obligatorios (DNI empleado y RUC cliente).");
}

$dni_empleado = trim($_POST['dni_empleado']);
$ruc_cliente = trim($_POST['ruc_cliente']);

/* Verificar que el empleado exista y obtener su RUC_Empresa */
$sqlEmp = "SELECT RUC_Empresa FROM Empleado WHERE DNI = ?";
$stmt = $conn->prepare($sqlEmp);
$stmt->bind_param("s", $dni_empleado);
$stmt->execute();
$resEmp = $stmt->get_result();

if ($resEmp->num_rows === 0) {
    die("Error: No se encontró empleado válido para el DNI: $dni_empleado");
}

$empleado = $resEmp->fetch_assoc();
$ruc_empresa = $empleado['RUC_Empresa'];
$stmt->close();

/* Verificar que el cliente exista */
$sqlCli = "
    SELECT c.RUC_Cliente, cn.Nombre_Cliente, cd.Direccion_del_Cliente
    FROM Cliente c
    LEFT JOIN Cliente_Nombre cn ON c.ID_NombreCliente = cn.ID_Nombre_Cliente
    LEFT JOIN Cliente_Direccion cd ON c.ID_DireccionCliente = cd.ID_DireccionCliente
    WHERE c.RUC_Cliente = ?
";
$stmt = $conn->prepare($sqlCli);
$stmt->bind_param("s", $ruc_cliente);
$stmt->execute();
$resCli = $stmt->get_result();

if ($resCli->num_rows === 0) {
    die("Error: No se encontró cliente válido para el RUC: $ruc_cliente");
}
$cliente = $resCli->fetch_assoc();
$stmt->close();

/* Generar número de factura único */
$numeroFactura = rand(1000, 9999);

/* Calcular totales */
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$igv = $subtotal * 0.18;
$total = $subtotal + $igv;

/* INSERTAR FACTURA */
$sqlFactura = "INSERT INTO Factura 
(Número_Factura, RUC_Empresa, DNI_Empleado, Sub_total_ventas, IGV, Valor_venta, Importe_total)
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sqlFactura);
$stmt->bind_param("sssdddd", $numeroFactura, $ruc_empresa, $dni_empleado, $subtotal, $igv, $subtotal, $total);

if (!$stmt->execute()) {
    die("Error al insertar factura: " . $conn->error);
}
$stmt->close();

/* INSERTAR PEDIDO */
$nombreHoja = "Pedido_" . $numeroFactura;
$sqlPedido = "INSERT INTO Pedido (Nombre_de_la_hoja, Fecha_de_emision, RUC_Cliente, DNI_Empleado, Número_de_factura)
              VALUES (?, CURDATE(), ?, ?, ?)";
$stmt = $conn->prepare($sqlPedido);
$stmt->bind_param("ssss", $nombreHoja, $ruc_cliente, $dni_empleado, $numeroFactura);
$stmt->execute();
$stmt->close();

/* INSERTAR PRODUCTOS EN PEDIDO_PRODUCTO */
$sqlProducto = "INSERT INTO Pedido_Producto (ID_Pedido_Producto, NombreDeLaHoja_Pedido, Cantidad, ID_Producto)
                VALUES (?, ?, ?, ?)";
$stmtProd = $conn->prepare($sqlProducto);

$contadorProd = 1;
foreach ($_SESSION['carrito'] as $item) {
    $idPedidoProd = str_pad($contadorProd, 4, "0", STR_PAD_LEFT);
    $stmtProd->bind_param("ssis", $idPedidoProd, $nombreHoja, $item['cantidad'], $item['id']);
    $stmtProd->execute();
    $contadorProd++;
}
$stmtProd->close();

/* INSERTAR PRECIOS_PEDIDO */
$sqlPrecios = "INSERT INTO Precios_Pedido 
(ID_Precios, NombreDeLaHoja_Pedido, Costo_Total, Precio_sin_IGV, Precio_total)
VALUES (?, ?, ?, ?, ?)";
$stmtPrecio = $conn->prepare($sqlPrecios);

$idPrecio = 1;
$precio_sin_igv = $subtotal;
$costo_total = $subtotal;
$precio_total = $total;
$stmtPrecio->bind_param("issss", $idPrecio, $nombreHoja, $costo_total, $precio_sin_igv, $precio_total);
$stmtPrecio->execute();
$stmtPrecio->close();

/* Guardar carrito temporal para mostrar */
$_SESSION['carrito_backup'] = $_SESSION['carrito'];
unset($_SESSION['carrito']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Generada</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <h1>Factura Emitida</h1>
</header>

<main class="contenedor">

    <div class="tarjeta">
        <h2>Factura Nº <?= $numeroFactura ?></h2>

        <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente['Nombre_Cliente']) ?></p>
        <p><strong>RUC:</strong> <?= htmlspecialchars($cliente['RUC_Cliente']) ?></p>
        <p><strong>Dirección:</strong> <?= htmlspecialchars($cliente['Direccion_del_Cliente']) ?></p>

        <h3>Detalle de productos</h3>

        <table>
            <thead>
                <tr>
                    <th>ID Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['carrito_backup'] as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= $item['cantidad'] ?></td>
                        <td><?= number_format($item['precio'], 2) ?></td>
                        <td><?= number_format($item['precio'] * $item['cantidad'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Totales</h3>
        <p><strong>Subtotal:</strong> S/ <?= number_format($subtotal, 2) ?></p>
        <p><strong>IGV (18%):</strong> S/ <?= number_format($igv, 2) ?></p>
        <p><strong>Total:</strong> S/ <?= number_format($total, 2) ?></p>

        <a class="btn btn-crear" href="factura_pdf.php?factura=<?= $numeroFactura ?>">Descargar PDF</a>
        <br><br>
        <a class="btn" href="index.php">Volver al Menú</a>
    </div>

</main>
</body>
</html>
