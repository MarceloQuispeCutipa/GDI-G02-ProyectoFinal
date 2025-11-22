<?php
// consultas.php
session_start();
require_once "conexion.php";
require_once "header.php";

// -----------------------------
// Helpers
// -----------------------------
function escape_html($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function output_table_from_result($rows) {
    if (empty($rows)) {
        echo "<p>No se encontraron filas para este reporte.</p>";
        return;
    }
    echo "<div style='overflow:auto'><table class='table'>\n<thead><tr>";
    $first = $rows[0];
    foreach (array_keys($first) as $col) {
        echo "<th>" . escape_html($col) . "</th>";
    }
    echo "</tr></thead>\n<tbody>";
    foreach ($rows as $r) {
        echo "<tr>";
        foreach ($r as $cell) {
            echo "<td>" . (is_null($cell) ? '' : escape_html((string)$cell)) . "</td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table></div>";
}

function export_csv($rows, $filename = 'reporte.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    if (empty($rows)) { fclose($out); exit; }
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $r) {
        $line = array_map(function($v){ return is_null($v) ? '' : (string)$v; }, $r);
        fputcsv($out, $line);
    }
    fclose($out); exit;
}

// -----------------------------
// Reportes configurados
// -----------------------------
$reports = [];

/* 1) Pedidos con cliente y empleado */
$reports[1] = [
    'title' => 'Pedidos con datos del cliente y empleado',
    'params' => [
        ['key'=>'desde','label'=>'Desde','type'=>'date'],
        ['key'=>'hasta','label'=>'Hasta','type'=>'date'],
        ['key'=>'cliente','label'=>'RUC Cliente','type'=>'text'],
        ['key'=>'empleado','label'=>'DNI Empleado','type'=>'text'],
    ],
    'sql' => "
        SELECT
            p.Nombre_de_la_hoja AS Pedido,
            p.Numero_de_factura AS Factura,
            f.Fecha_de_emision,
            c.RUC_Cliente,
            cn.Nombre_Cliente,
            cd.Direccion_del_Cliente,
            e.DNI AS DNI_Empleado,
            e.Nombre AS Nombre_Empleado,
            e.Cargo AS Cargo_Empleado
        FROM Pedido p
        LEFT JOIN Factura f ON f.Numero_Factura = p.Numero_de_factura
        LEFT JOIN Cliente c ON c.RUC_Cliente = p.RUC_Cliente
        LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
        LEFT JOIN Cliente_Direccion cd ON cd.ID_DireccionCliente = c.ID_DireccionCliente
        LEFT JOIN Empleado e ON e.DNI = p.DNI_Empleado
        WHERE 1=1
    ",
    'map_params' => function(&$sql) {
        $types = '';
        $params = [];

        if (!empty($_GET['desde']) && !empty($_GET['hasta'])) {
            $sql .= " AND f.Fecha_de_emision BETWEEN ? AND ?";
            $types .= "ss";
            $params[] = $_GET['desde'];
            $params[] = $_GET['hasta'];
        }

        if (!empty($_GET['cliente'])) {
            $sql .= " AND c.RUC_Cliente = ?";
            $types .= "s";
            $params[] = $_GET['cliente'];
        }

        if (!empty($_GET['empleado'])) {
            $sql .= " AND e.DNI = ?";
            $types .= "s";
            $params[] = $_GET['empleado'];
        }

        return [$types, $params];
    }
];

/* 2) Productos por pedido */
$reports[2] = [
    'title'=>'Productos incluidos en cada pedido',
    'desc'=>'Lista productos que pertenecen a cada pedido.',
    'params'=>[
        ['key'=>'pedido','label'=>'Nombre de pedido','type'=>'text'],
        ['key'=>'producto','label'=>'ID Producto','type'=>'text'],
    ],
    'sql' => "
        SELECT
            pp.NombreDeLaHoja_Pedido AS Pedido,
            pp.ID_Pedido_Producto AS ItemID,
            pr.idProducto,
            pr.Descripcion_del_producto,
            pr.Unidad,
            pp.Cantidad,
            pr.Precio_Unitario,
            (pp.Cantidad * pr.Precio_Unitario) AS TotalItem
        FROM Pedido_Producto pp
        LEFT JOIN Producto pr ON pr.idProducto = pp.ID_Producto
        WHERE 1=1
    ",
    'map_params' => function(&$sql) {
        $types = '';
        $params = [];

        if (!empty($_GET['pedido'])) {
            $sql .= " AND pp.NombreDeLaHoja_Pedido = ?";
            $types .= 's';
            $params[] = $_GET['pedido'];
        }

        if (!empty($_GET['producto'])) {
            $sql .= " AND pp.ID_Producto = ?";
            $types .= 's';
            $params[] = $_GET['producto'];
        }

        return [$types,$params];
    }
];

/* 3) Precios por pedido */
$reports[3] = [
    'title'=>'Precios registrados por pedido',
    'desc'=>'Lista precios, subtotal, total, etc.',
    'params'=>[
        ['key'=>'pedido','label'=>'Nombre de pedido','type'=>'text'],
    ],
    'sql' => "
        SELECT
            ID_Precios,
            NombreDeLaHoja_Pedido,
            Costo_Total,
            Precio_sin_IGV,
            Precio_total
        FROM Precios_Pedido
        WHERE 1=1
    ",
    'map_params'=>function(&$sql){
        $types=''; $params=[];
        if (!empty($_GET['pedido'])) {
            $sql .= " AND NombreDeLaHoja_Pedido = ?";
            $types='s'; 
            $params[] = $_GET['pedido'];
        }
        return [$types,$params];
    }
];

/* 4) Facturas asociadas a pedidos */
$reports[4] = [
    'title'=>'Facturas asociadas a pedidos',
    'desc'=>'Lista facturas relacionadas con pedidos.',
    'params'=>[
        ['key'=>'desde','label'=>'Desde (fecha)','type'=>'date'],
        ['key'=>'hasta','label'=>'Hasta (fecha)','type'=>'date'],
        ['key'=>'factura','label'=>'Número de factura','type'=>'text']
    ],
    'sql' => "
        SELECT
            f.Numero_Factura,
            f.Fecha_de_emision,
            f.Tipo_de_moneda,
            f.Sub_total_ventas,
            f.IGV,
            f.Importe_total,
            p.Nombre_de_la_hoja AS Pedido,
            e.DNI AS DNI_Empleado,
            e.Nombre AS Empleado_Nombre
        FROM Factura f
        LEFT JOIN Pedido p ON p.Numero_de_factura = f.Numero_Factura
        LEFT JOIN Empleado e ON e.DNI = f.DNI_Empleado
        WHERE 1=1
    ",
    'map_params'=>function(&$sql){
        $types=''; $params=[];

        if (!empty($_GET['desde']) && !empty($_GET['hasta'])) {
            $sql .= " AND f.Fecha_de_emision BETWEEN ? AND ?";
            $types.='ss';
            $params[] = $_GET['desde'];
            $params[] = $_GET['hasta'];
        }

        if (!empty($_GET['factura'])) {
            $sql .= " AND f.Numero_Factura = ?";
            $types.='s';
            $params[] = $_GET['factura'];
        }
        return [$types,$params];
    }
];

/* 5) Clientes y cantidad de pedidos */
$reports[5] = [
    'title'=>'Clientes y cantidad de pedidos realizados',
    'desc'=>'Contar pedidos por cliente.',
    'params'=>[
        ['key'=>'top','label'=>'Top N (0 = todos)','type'=>'number']
    ],
    'sql' => "
        SELECT
            c.RUC_Cliente,
            cn.Nombre_Cliente,
            COUNT(p.Nombre_de_la_hoja) AS Cantidad_Pedidos
        FROM Cliente c
        LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
        LEFT JOIN Pedido p ON p.RUC_Cliente = c.RUC_Cliente
        GROUP BY c.RUC_Cliente, cn.Nombre_Cliente
        ORDER BY Cantidad_Pedidos DESC
    ",
    'map_params'=>function(&$sql){
        if (!empty($_GET['top']) && intval($_GET['top']) > 0) {
            $sql .= " LIMIT " . intval($_GET['top']);
        }
        return ['', []];
    }
];

/* 6) Empleados y cantidad de pedidos */
$reports[6] = [
    'title'=>'Empleados y cantidad de pedidos atendidos',
    'desc'=>'Contar pedidos por empleado.',
    'params'=>[
        ['key'=>'top','label'=>'Top N (0 = todos)','type'=>'number']
    ],
    'sql' => "
        SELECT
            e.DNI,
            e.Nombre AS Empleado_Nombre,
            COUNT(p.Nombre_de_la_hoja) AS Total_Pedidos
        FROM Empleado e
        LEFT JOIN Pedido p ON p.DNI_Empleado = e.DNI
        GROUP BY e.DNI, e.Nombre
        ORDER BY Total_Pedidos DESC
    ",
    'map_params'=>function(&$sql){
        if (!empty($_GET['top']) && intval($_GET['top']) > 0) {
            $sql .= " LIMIT " . intval($_GET['top']);
        }
        return ['', []];
    }
];

/* 7) Productos */
$reports[7] = [
    'title'=>'Listado de Productos',
    'desc'=>'Todos los productos.',
    'params'=>[],
    'sql' => "SELECT idProducto, Descripcion_del_producto, Unidad, Precio_Unitario FROM Producto ORDER BY idProducto",
    'map_params'=>function(&$sql){ return ['',[]]; }
];

/* 8) Pedidos base */
$reports[8] = [
    'title'=>'Listado completo de Pedido',
    'desc'=>'Tabla base de Pedido.',
    'params'=>[],
    'sql' => "SELECT Nombre_de_la_hoja, RUC_Cliente, DNI_Empleado, Numero_de_factura FROM Pedido ORDER BY Nombre_de_la_hoja",
    'map_params'=>function(&$sql){ return ['',[]]; }
];

/* 9) Filtro por cliente */
$reports[9] = [
    'title'=>'Filtrar pedidos por RUC Cliente',
    'desc'=>'Filtra pedidos de un cliente específico.',
    'params'=>[
        ['key'=>'cliente','label'=>'RUC Cliente','type'=>'text']
    ],
    'sql' => "SELECT Nombre_de_la_hoja, RUC_Cliente, DNI_Empleado, Numero_de_factura FROM Pedido WHERE RUC_Cliente = ?",
    'map_params'=>function(&$sql){
        if (empty($_GET['cliente'])) return ['',[]];
        return ['s', [$_GET['cliente']]];
    }
];

/* 10) Buscar pedidos */
$reports[10] = [
    'title'=>'Buscar pedidos por nombre de hoja (LIKE)',
    'desc'=>'Busca texto en el nombre de hoja.',
    'params'=>[
        ['key'=>'q','label'=>'Texto a buscar','type'=>'text']
    ],
    'sql' => "SELECT Nombre_de_la_hoja, RUC_Cliente, DNI_Empleado, Numero_de_factura FROM Pedido WHERE Nombre_de_la_hoja LIKE ?",
    'map_params'=>function(&$sql){
        if (empty($_GET['q'])) return ['',[]];
        return ['s', ["%{$_GET['q']}%"]];
    }
];

// -----------------------------
// Manejo general
// -----------------------------
$report_id = intval($_GET['r'] ?? 1);
if (!isset($reports[$report_id])) $report_id = 1;

$report = $reports[$report_id];

$sql = $report['sql'];
list($types, $params) = $report['map_params']($sql);

// ejecución
$rows = [];
$error = null;

if ($types === '' && !empty($report['params'])) {
    // reportes que requieren param oblig
    foreach ($report['params'] as $p) {
        if (!empty($p['key']) && !empty($p['type']) && $p['type'] !== 'number') {
            if (empty($_GET[$p['key']])) {
                $error = "Falta parámetro obligatorio: {$p['label']}";
            }
        }
    }
}

if ($error === null) {
    try {
        $stmt = $conexion->prepare($sql);
        if (!$stmt) throw new Exception($conexion->error);

        if ($types !== '') {
            $bind = [];
            $bind[] = $types;
            foreach ($params as $k => $v) $bind[] = &$params[$k];
            call_user_func_array([$stmt, 'bind_param'], $bind);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<div class="contenedor" >
    <h2 style="text-align:center;">Consultas y Reportes</h2>

    <form class="formulario" method="GET" id="formReport">
        <label>Reporte:</label>
        <select name="r" onchange="document.getElementById('formReport').submit();">
            <?php foreach ($reports as $id=>$rp): ?>
                <option value="<?= $id ?>" <?= $id==$report_id?'selected':'' ?>>
                    <?= escape_html($rp['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="margin-top:15px;">
            <?php foreach ($report['params'] as $p):
                $v = $_GET[$p['key']] ?? '';
            ?>
                <label class="tarjetas"><?= $p['label'] ?>:<br>
                    <?php if ($p['type']==='date'): ?>
                        <input type="date" name="<?= $p['key'] ?>" value="<?= escape_html($v) ?>">
                    <?php elseif ($p['type']==='number'): ?>
                        <input type="number" name="<?= $p['key'] ?>" value="<?= escape_html($v) ?>">
                    <?php else: ?>
                        <input type="text" name="<?= $p['key'] ?>" value="<?= escape_html($v) ?>">
                    <?php endif; ?>
                </label>
                &nbsp;&nbsp;
            <?php endforeach; ?>
        </div>

        <br>
        <button class="btn btn-crear" type="submit">Ejecutar</button>
    </form>

    <br>

    <?php if ($error): ?>
        <div style="color:red;"><strong>Error:</strong> <?= escape_html($error) ?></div>
    <?php else: ?>
        <?php output_table_from_result($rows); ?>
    <?php endif; ?>
</div>

<?php require_once "footer.php"; ?>
