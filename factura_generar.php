<?php
// factura_crear.php
session_start();
require_once "conexion.php";

// Mostrar errores en desarrollo (comenta en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (isset($_POST['vaciar_carrito'])) {
    unset($_SESSION['carrito']);
    // Redirigir de vuelta a la página previa si existe
    $back = $_SERVER['HTTP_REFERER'] ?? 'factura_crear.php';
    header("Location: " . $back);
    exit;
}

/* Validar carrito */
if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    die("Error: El carrito está vacío. Agrega productos antes de generar la factura.");
}

/* MAPEO DE CAMPOS POST (acepta varias variantes) */
$ruc_empresa = $_POST['ruc_empresa'] ?? $_POST['empresa'] ?? null;
// empleado: aceptamos 'dni_empleado' o 'empleado_factura'
$dni_empleado = $_POST['dni_empleado'] ?? $_POST['empleado_factura'] ?? null;
// cliente: aceptamos 'ruc_cliente' o 'cliente' (tu BD usa RUC_Cliente)
$ruc_cliente = $_POST['ruc_cliente'] ?? $_POST['cliente'] ?? null;
// tipo de moneda opcional
$tipo_moneda = $_POST['tipo_moneda'] ?? 'PEN';

if (!$ruc_empresa || !$dni_empleado || !$ruc_cliente) {
    die("Faltan datos obligatorios (empresa, empleado o cliente).");
}

/* VALIDACIONES BÁSICAS: existencia de empleado y cliente en BD */
$stmt = $conexion->prepare("SELECT RUC_Empresa FROM Empleado WHERE DNI = ? LIMIT 1");
$stmt->bind_param("s", $dni_empleado);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    die("Error: Empleado no válido (DNI).");
}
$row = $res->fetch_assoc();
$empresa_del_empleado = $row['RUC_Empresa'];
$stmt->close();

// Opcional: podríamos validar que $empresa_del_empleado == $ruc_empresa, pero lo dejamos flexible.

$stmt = $conexion->prepare("SELECT RUC_Cliente FROM Cliente WHERE RUC_Cliente = ? LIMIT 1");
$stmt->bind_param("s", $ruc_cliente);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    die("Error: Cliente no encontrado (RUC).");
}
$stmt->close();

/* CALCULAR TOTALES desde carrito */
$subtotal = 0.0;
foreach ($_SESSION['carrito'] as $item) {
    $precio = floatval($item['precio']);
    $cantidad = intval($item['cantidad']);
    $subtotal += $precio * $cantidad;
}
$subtotal = round($subtotal, 2);
$igv = round($subtotal * 0.18, 2);
$total = round($subtotal + $igv, 2);

/* GENERAR Numero_Factura único (formato FNNN) */
do {
    $num = random_int(1, 999);
    $numero_factura = 'F' . str_pad($num, 3, "0", STR_PAD_LEFT); // F001..F999
    $chk = $conexion->prepare("SELECT 1 FROM Factura WHERE Numero_Factura = ? LIMIT 1");
    $chk->bind_param("s", $numero_factura);
    $chk->execute();
    $r = $chk->get_result();
    $exists = ($r->num_rows > 0);
    $chk->close();
} while ($exists);

/* Nombre de la hoja del pedido: seguimos tu convención P + número */
$nombreHoja = 'P' . substr($numero_factura, 1); // si F001 -> P001

/* Iniciamos transacción */
$conexion->begin_transaction();

try {
    // Insertar Factura
    $stmt = $conexion->prepare("
        INSERT INTO Factura
        (Numero_Factura, Tipo_de_moneda, Observaciones, Importe_total, IGV, Valor_venta, Sub_total_ventas, Fecha_de_emision, RUC_Empresa, DNI_Empleado)
        VALUES (?, ?, '', ?, ?, ?, ?, CURDATE(), ?, ?)
    ");
    // Tu esquema usa INT para montos; convertimos a enteros (si quieres centavos, cambia a DECIMAL en BD)
    $importe_int = (int) round($total);
    $igv_int = (int) round($igv);
    $valor_venta_int = (int) round($subtotal);
    $sub_total_int = (int) round($subtotal);

   
    $stmt->bind_param("siiiisss",
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


    $stmt = $conexion->prepare("
        INSERT INTO Pedido_Producto (ID_Pedido_Producto, NombreDeLaHoja_Pedido, Cantidad, ID_Producto)
        VALUES (?, ?, ?, ?)
    ");
    $contador = 1;
    foreach ($_SESSION['carrito'] as $item) {
        $idPedidoProd = 'PP' . str_pad($contador, 2, "0", STR_PAD_LEFT); 
        $cantidad = intval($item['cantidad']);
        $idProducto = $item['id'];
        $stmt->bind_param("ssis", $idPedidoProd, $nombreHoja, $cantidad, $idProducto);
        $stmt->execute();
        $contador++;
    }
    $stmt->close();


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

    // Commit
    $conexion->commit();

    // Guardar respaldo para mostrar en factura
    $_SESSION['carrito_backup'] = $_SESSION['carrito'];
    $_SESSION['numero_factura'] = $numero_factura;
    $_SESSION['cliente_factura'] = $ruc_cliente;
    $_SESSION['empleado_factura'] = $dni_empleado;
    $_SESSION['subtotal_factura'] = $subtotal;
    $_SESSION['igv_factura'] = $igv;
    $_SESSION['total_factura'] = $total;

    // Vaciar carrito actual
    unset($_SESSION['carrito']);

    // Redirigir a la vista de la factura
    header("Location: factura.php?numero_factura=" . urlencode($numero_factura));
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    // Para depuración:
    die("Error al generar la factura: " . $e->getMessage());
}
