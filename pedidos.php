<?php
// pedidos.php
session_start();
require_once "conexion.php";
require_once "header.php"; // si tienes cabecera común

// Opcional: logo local (imagen que subiste)
$logo_local = "/mnt/data/WhatsApp Image 2025-10-01 at 23.05.57_91a09c05.jpg";

// --- Obtener datos para selects ---
$clientes = $conexion->query("
    SELECT c.RUC_Cliente, cn.Nombre_Cliente
    FROM Cliente c
    LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
    ORDER BY cn.Nombre_Cliente
")->fetch_all(MYSQLI_ASSOC);

$empleados = $conexion->query("SELECT DNI, Nombre FROM Empleado ORDER BY Nombre")->fetch_all(MYSQLI_ASSOC);

$productos = $conexion->query("SELECT idProducto, Descripcion_del_producto FROM Producto ORDER BY Descripcion_del_producto")->fetch_all(MYSQLI_ASSOC);

// --- Recibir filtros (GET) ---
$fecha_exacta = $_GET['fecha'] ?? "";
$desde = $_GET['desde'] ?? "";
$hasta = $_GET['hasta'] ?? "";
$filter_cliente = $_GET['cliente'] ?? "";
$filter_empleado = $_GET['empleado'] ?? "";
$filter_producto = $_GET['producto'] ?? "";
$filter_pedido = $_GET['pedido'] ?? "";
$filter_factura = $_GET['factura'] ?? "";

// Paginación simple
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// --- Construir WHERE dinámico (usando Factura.Fecha_de_emision para fecha) ---
$where = [];
$params = [];
$types = "";

if ($fecha_exacta !== "") {
    $where[] = "f.Fecha_de_emision = ?";
    $params[] = $fecha_exacta;
    $types .= "s";
}
if ($desde !== "" && $hasta !== "") {
    $where[] = "f.Fecha_de_emision BETWEEN ? AND ?";
    $params[] = $desde;
    $params[] = $hasta;
    $types .= "ss";
}
if ($filter_cliente !== "") {
    $where[] = "p.RUC_Cliente = ?";
    $params[] = $filter_cliente;
    $types .= "s";
}
if ($filter_empleado !== "") {
    $where[] = "p.DNI_Empleado = ?";
    $params[] = $filter_empleado;
    $types .= "s";
}
if ($filter_producto !== "") {
    $where[] = "pp.ID_Producto = ?";
    $params[] = $filter_producto;
    $types .= "s";
}
if ($filter_pedido !== "") {
    $where[] = "p.Nombre_de_la_hoja = ?";
    $params[] = $filter_pedido;
    $types .= "s";
}
if ($filter_factura !== "") {
    $where[] = "p.Numero_de_factura = ?";
    $params[] = $filter_factura;
    $types .= "s";
}

$where_sql = "";
if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

// --- Contar (para paginación) ---
$sqlCount = "
SELECT COUNT(*) AS tot
FROM Pedido p
JOIN Factura f ON f.Numero_Factura = p.Numero_de_factura
JOIN Pedido_Producto pp ON pp.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
{$where_sql}
";
$stmtCount = $conexion->prepare($sqlCount);
if ($types !== "") {
    // bind params safely (no unpack mixing)
    $bind_params = array_merge([$types], $params);
    // call_user_func_array needs references
    $tmp = [];
    foreach ($bind_params as $k => $v) $tmp[$k] = &$bind_params[$k];
    call_user_func_array([$stmtCount, 'bind_param'], $tmp);
}
$stmtCount->execute();
$resCount = $stmtCount->get_result()->fetch_assoc();
$totalRows = intval($resCount['tot'] ?? 0);
$stmtCount->close();

$totalPages = max(1, ceil($totalRows / $perPage));

// --- Consulta principal: 1 fila por producto del pedido (no GROUP BY) ---
$sql = "
SELECT
    p.Nombre_de_la_hoja,
    p.RUC_Cliente,
    cn.Nombre_Cliente,
    c2.Direccion_del_Cliente,
    p.DNI_Empleado,
    e.Nombre AS Empleado_Nombre,
    p.Numero_de_factura,
    f.Fecha_de_emision,
    pp.ID_Pedido_Producto,
    pp.Cantidad,
    prod.idProducto,
    prod.Descripcion_del_producto,
    prod.Unidad,
    prod.Precio_Unitario,
    (pp.Cantidad * prod.Precio_Unitario) AS TotalProducto
FROM Pedido p
LEFT JOIN Cliente c ON c.RUC_Cliente = p.RUC_Cliente
LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
LEFT JOIN Cliente_Direccion c2 ON c2.ID_DireccionCliente = c.ID_DireccionCliente
LEFT JOIN Empleado e ON e.DNI = p.DNI_Empleado
LEFT JOIN Factura f ON f.Numero_Factura = p.Numero_de_factura
LEFT JOIN Pedido_Producto pp ON pp.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
LEFT JOIN Producto prod ON prod.idProducto = pp.ID_Producto
{$where_sql}
ORDER BY f.Fecha_de_emision DESC, p.Nombre_de_la_hoja, pp.ID_Pedido_Producto
LIMIT ? OFFSET ?
";

$stmt = $conexion->prepare($sql);

// bind parameters: first types + perPage + offset
if ($types !== "") {
    $final_types = $types . "ii";
    $final_params = array_merge($params, [$perPage, $offset]);
    // prepare call_user_func_array binding (must pass references)
    $bind_names = [];
    $bind_names[] = $final_types;
    foreach ($final_params as $i => $value) {
        $bind_names[] = &$final_params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Pedidos - Listado</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<?php if (file_exists($logo_local)): ?>
    <div style="text-align:right"><img src="<?= htmlspecialchars($logo_local) ?>" alt="logo" style="max-width:160px;"></div>
<?php endif; ?>

<div class="contenedor">
    <h2>Pedidos</h2>

    <form method="GET" class="formulario" style="display:flex;flex-wrap:wrap;gap:10px;">
        <label>Fecha exacta: <input type="date" name="fecha" value="<?= htmlspecialchars($fecha_exacta) ?>"></label>
        <label>Desde: <input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>"></label>
        <label>Hasta: <input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>"></label>

        <label>Cliente:
            <select name="cliente">
                <option value="">-- Todos --</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= htmlspecialchars($c['RUC_Cliente']) ?>" <?= $filter_cliente == $c['RUC_Cliente'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['Nombre_Cliente'] . " (" . $c['RUC_Cliente'] . ")") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Empleado:
            <select name="empleado">
                <option value="">-- Todos --</option>
                <?php foreach ($empleados as $e): ?>
                    <option value="<?= htmlspecialchars($e['DNI']) ?>" <?= $filter_empleado == $e['DNI'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Producto:
            <select name="producto">
                <option value="">-- Todos --</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= htmlspecialchars($p['idProducto']) ?>" <?= $filter_producto == $p['idProducto'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['Descripcion_del_producto']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Nombre de pedido: <input type="text" name="pedido" value="<?= htmlspecialchars($filter_pedido) ?>" placeholder="P001..."></label>
        <label>Nº factura: <input type="text" name="factura" value="<?= htmlspecialchars($filter_factura) ?>" placeholder="F001..."></label>

        <div style="flex-basis:100%;display:flex;gap:8px;">
            <button class="btn btn-crear" type="submit">Filtrar</button>
            <a class="btn btn-cancelar" href="pedidos.php">Limpiar</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Dir. Cliente</th>
                <th>Empleado</th>
                <th>Factura</th>
                <th>Producto (ID)</th>
                <th>Descripción</th>
                <th>Unidad</th>
                <th>Cantidad</th>
                <th>PU</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="13">No se encontraron pedidos con esos filtros.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['Nombre_de_la_hoja']) ?></td>
                    <td><?= htmlspecialchars($r['Fecha_de_emision']) ?></td>
                    <td><?= htmlspecialchars($r['Nombre_Cliente'] ?? '') ?> (<?= htmlspecialchars($r['RUC_Cliente']) ?>)</td>
                    <td><?= htmlspecialchars($r['Direccion_del_Cliente'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['Empleado_Nombre'] ?? $r['DNI_Empleado']) ?></td>
                    <td><?= htmlspecialchars($r['Numero_de_factura']) ?></td>
                    <td><?= htmlspecialchars($r['idProducto']) ?></td>
                    <td><?= htmlspecialchars($r['Descripcion_del_producto']) ?></td>
                    <td><?= htmlspecialchars($r['Unidad']) ?></td>
                    <td><?= htmlspecialchars($r['Cantidad']) ?></td>
                    <td>S/ <?= number_format($r['Precio_Unitario'],2) ?></td>
                    <td>S/ <?= number_format($r['TotalProducto'],2) ?></td>
                    <td><a class="btn" href="pedidos_detalle.php?nombre=<?= urlencode($r['Nombre_de_la_hoja']) ?>">Ver</a></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div style="margin-top:12px;">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a class="btn <?= $i==$page ? 'btn-crear' : 'btn-cancelar' ?>" href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>

</div>

<?php require_once 'footer.php'; ?>
</body>
</html>
