<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['detalle_factura']) || count($_SESSION['detalle_factura']) === 0) {
    die("No hay detalle de productos para emitir la factura.");
}

$ruc_cliente = $_SESSION['ruc_cliente'] ?? null;
$dni_empleado = $_POST['dni_empleado'] ?? null;
$tipo_moneda = $_POST['tipo_moneda'] ?? 'PEN';

if (!$ruc_cliente || !$dni_empleado) {
    die("Faltan datos obligatorios de cliente o empleado.");
}

// Validar cliente
$stmt = $conexion->prepare("SELECT RUC_Cliente FROM Cliente WHERE RUC_Cliente = ? LIMIT 1");
$stmt->bind_param("s", $ruc_cliente);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    die("Cliente no encontrado.");
}
$stmt->close();

// Validar empleado y obtener empresa
$stmt = $conexion->prepare("SELECT RUC_Empresa FROM Empleado WHERE DNI = ? LIMIT 1");
$stmt->bind_param("s", $dni_empleado);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    die("Empleado no válido.");
}
$row = $res->fetch_assoc();
$ruc_empresa = $row['RUC_Empresa'];
$stmt->close();

// Calcular totales
$subtotal = 0.0;
foreach ($_SESSION['detalle_factura'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$subtotal = round($subtotal, 2);
$igv = round($subtotal * 0.18, 2);
$total = round($subtotal + $igv, 2);

// Generar número de factura FNNN
do {
    $num = random_int(1, 999);
    $numero_factura = 'F' . str_pad($num, 3, "0", STR_PAD_LEFT);
    $chk = $conexion->prepare("SELECT 1 FROM Factura WHERE Numero_Factura = ? LIMIT 1");
    $chk->bind_param("s", $numero_factura);
    $chk->execute();
    $r = $chk->get_result();
    $exists = ($r->num_rows > 0);
    $chk->close();
} while ($exists);

$nombreHoja = 'P' . substr($numero_factura, 1); // P001, P002...

$conexion->begin_transaction();

try {
    // Insertar Factura
    $stmt = $conexion->prepare("
        INSERT INTO Factura
        (Numero_Factura, Tipo_de_moneda, Observaciones, Importe_total, IGV, Valor_venta, Sub_total_ventas, Fecha_de_emision, RUC_Empresa, DNI_Empleado)
        VALUES (?, ?, '', ?, ?, ?, ?, CURDATE(), ?, ?)
    ");

    $importe_int = (int) round($total);
    $igv_int = (int) round($igv);
    $valor_venta_int = (int) round($subtotal);
    $sub_total_int = (int) round($subtotal);

    $stmt->bind_param(
        "ssiiiiss",
        $numero_factura,
        $tipo_moneda,
        $importe_int,
        $igv_int,
        $valor_venta_int,
        $sub_total_int,
        $ruc_empresa,
        $dni_empleado
    );
    $stmt->execute();
    $stmt->close();

    // Insertar Pedido
    $stmt = $conexion->prepare("
        INSERT INTO Pedido (Nombre_de_la_hoja, RUC_Cliente, DNI_Empleado, Numero_de_factura)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $nombreHoja, $ruc_cliente, $dni_empleado, $numero_factura);
    $stmt->execute();
    $stmt->close();

    // Insertar detalle y actualizar stock
    $stmtDetalle = $conexion->prepare("
        INSERT INTO Pedido_Producto (ID_Pedido_Producto, NombreDeLaHoja_Pedido, Cantidad, ID_Producto)
        VALUES (?, ?, ?, ?)
    ");
    $stmtStock = $conexion->prepare("
        UPDATE Producto SET Stock = Stock - ? WHERE idProducto = ?
    ");

    $contador = 1;
    foreach ($_SESSION['detalle_factura'] as $item) {
        $idPedidoProd = 'PP' . str_pad($contador, 2, "0", STR_PAD_LEFT);
        $cantidad = intval($item['cantidad']);
        $idProducto = $item['id'];

        // Insertar detalle
        $stmtDetalle->bind_param("ssis", $idPedidoProd, $nombreHoja, $cantidad, $idProducto);
        $stmtDetalle->execute();

        // Actualizar stock
        $stmtStock->bind_param("is", $cantidad, $idProducto);
        $stmtStock->execute();

        $contador++;
    }
    $stmtDetalle->close();
    $stmtStock->close();

    // Registrar precios del pedido
    $res = $conexion->query("SELECT COALESCE(MAX(ID_Precios),0)+1 AS next_id FROM Precios_Pedido");
    $row = $res->fetch_assoc();
    $nextPrecioId = intval($row['next_id']);

    $stmt = $conexion->prepare("
        INSERT INTO Precios_Pedido (ID_Precios, NombreDeLaHoja_Pedido, Costo_Total, Precio_sin_IGV, Precio_total)
        VALUES (?, ?, ?, ?, ?)
    ");

    $costo_total_str = number_format($subtotal, 2, '.', '');
    $precio_sin_igv_str = number_format($subtotal, 2, '.', '');
    $precio_total_str = number_format($total, 2, '.', '');

    $stmt->bind_param("issss", $nextPrecioId, $nombreHoja, $costo_total_str, $precio_sin_igv_str, $precio_total_str);
    $stmt->execute();
    $stmt->close();

    $conexion->commit();

    $_SESSION['detalle_backup'] = $_SESSION['detalle_factura'];
    $_SESSION['numero_factura'] = $numero_factura;
    $_SESSION['ruc_cliente_factura'] = $ruc_cliente;

    unset($_SESSION['detalle_factura']);

    header("Location: factura.php?numero_factura=" . urlencode($numero_factura));
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    die("Error al emitir la factura: " . $e->getMessage());
}
?>
