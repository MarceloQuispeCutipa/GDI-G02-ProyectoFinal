<?php
// pedido_generar.php
require_once "conexion.php";
session_start();

// Recepción y validación básica
$numero_factura = $_POST['numero_factura'] ?? null;
$ruc_cliente = $_POST['ruc_cliente'] ?? null;
$dni_empleado = $_POST['dni_empleado'] ?? null;
$producto_ids = $_POST['producto_id'] ?? [];
$cantidades = $_POST['cantidad'] ?? [];

if (!$numero_factura || !$ruc_cliente || !$dni_empleado) {
    die("Faltan datos obligatorios.");
}
if (count($producto_ids) === 0) {
    die("Agrega al menos un producto.");
}

// validar existencia factura / cliente / empleado
$stmt = $conexion->prepare("SELECT 1 FROM Factura WHERE Numero_Factura = ? LIMIT 1");
$stmt->bind_param("s", $numero_factura);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) die("Factura no encontrada.");
$stmt->close();

$stmt = $conexion->prepare("SELECT 1 FROM Cliente WHERE RUC_Cliente = ? LIMIT 1");
$stmt->bind_param("s", $ruc_cliente);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) die("Cliente no encontrado.");
$stmt->close();

$stmt = $conexion->prepare("SELECT 1 FROM Empleado WHERE DNI = ? LIMIT 1");
$stmt->bind_param("s", $dni_empleado);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) die("Empleado no encontrado.");
$stmt->close();

// generar Nombre_de_la_hoja (P + random/seq)
// intentamos con P + Numero_Factura si único, sino agregar sufijo
$base = 'P' . $numero_factura;
$nombreHoja = $base;
$cnt = 0;
while (true) {
    $chk = $conexion->prepare("SELECT 1 FROM Pedido WHERE Nombre_de_la_hoja = ? LIMIT 1");
    $chk->bind_param("s", $nombreHoja);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        $chk->close();
        break;
    }
    $chk->close();
    $cnt++;
    $nombreHoja = $base . "_" . $cnt;
}

// Transacción
$conexion->begin_transaction();

try {
    // Insertar pedido
    $stmt = $conexion->prepare("INSERT INTO Pedido (Nombre_de_la_hoja, RUC_Cliente, DNI_Empleado, Numero_de_factura) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombreHoja, $ruc_cliente, $dni_empleado, $numero_factura);
    $stmt->execute();
    $stmt->close();

    // Insertar productos
    $stmt = $conexion->prepare("INSERT INTO Pedido_Producto (ID_Pedido_Producto, NombreDeLaHoja_Pedido, Cantidad, ID_Producto) VALUES (?, ?, ?, ?)");
    $contador = 1;
    $subtotal = 0.0;
    for ($i=0;$i<count($producto_ids);$i++) {
        $pid = $producto_ids[$i];
        $cant = max(1, intval($cantidades[$i]));
        // obtener precio unitario actual
        $res = $conexion->prepare("SELECT Precio_Unitario FROM Producto WHERE idProducto = ? LIMIT 1");
        $res->bind_param("s", $pid);
        $res->execute();
        $r = $res->get_result()->fetch_assoc();
        $res->close();
        $precio_unit = $r ? floatval($r['Precio_Unitario']) : 0.0;
        $subtotal += $precio_unit * $cant;

        $idPedidoProd = 'PP' . str_pad($contador, 2, "0", STR_PAD_LEFT);
        $stmt->bind_param("ssis", $idPedidoProd, $nombreHoja, $cant, $pid);
        $stmt->execute();
        $contador++;
    }
    $stmt->close();

    // Precios_Pedido insert
    $res = $conexion->query("SELECT COALESCE(MAX(ID_Precios),0)+1 AS next_id FROM Precios_Pedido");
    $row = $res->fetch_assoc();
    $nextPrecioId = intval($row['next_id']);

    $igv = round($subtotal * 0.18, 2);
    $total = round($subtotal + $igv, 2);

    $costo_total_str = number_format($subtotal,2,'.','');
    $precio_sin_igv_str = number_format($subtotal,2,'.','');
    $precio_total_str = number_format($total,2,'.','');

    $stmt = $conexion->prepare("INSERT INTO Precios_Pedido (ID_Precios, NombreDeLaHoja_Pedido, Costo_Total, Precio_sin_IGV, Precio_total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $nextPrecioId, $nombreHoja, $costo_total_str, $precio_sin_igv_str, $precio_total_str);
    $stmt->execute();
    $stmt->close();

    $conexion->commit();

    // redirigir al detalle
    header("Location: pedido_detalle.php?nombre=" . urlencode($nombreHoja));
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    die("Error al crear pedido: " . $e->getMessage());
}
