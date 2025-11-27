<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';

function output_table_from_result($resultado) {
    if (!$resultado || $resultado->num_rows == 0) {
        echo "<p>No hay datos para mostrar.</p>";
        return;
    }

    echo "<table>";

    // Encabezados
    echo "<tr>";
    while ($campo = $resultado->fetch_field()) {
        echo "<th>" . htmlspecialchars($campo->name) . "</th>";
    }
    echo "</tr>";

    // Filas
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        foreach ($fila as $valor) {
            echo "<td>" . htmlspecialchars($valor) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

$tipo_consulta = isset($_GET['tipo_consulta']) ? $_GET['tipo_consulta'] : '';
?>

<section class="contenedor">
    <h2>Consultas y Reportes</h2>

    <form method="get" class="formulario">
        <label for="tipo_consulta">Seleccione el reporte:</label>
        <select name="tipo_consulta" id="tipo_consulta">
            <option value="">-- Seleccione --</option>
            <option value="facturas_resumen" <?php if($tipo_consulta=='facturas_resumen') echo 'selected'; ?>>
                Listado de facturas (tipo SUNAT)
            </option>
            <option value="pedidos_detalle" <?php if($tipo_consulta=='pedidos_detalle') echo 'selected'; ?>>
                Pedidos con detalle de productos
            </option>
            <option value="empleados_audit" <?php if($tipo_consulta=='empleados_audit') echo 'selected'; ?>>
                Auditoría de empleados
            </option>
            <option value="clientes_audit" <?php if($tipo_consulta=='clientes_audit') echo 'selected'; ?>>
                Auditoría de clientes
            </option>
        </select>
        <button type="submit" class="btn btn-crear">Ver reporte</button>
    </form>

    <?php
    if ($tipo_consulta != '') {
        echo "<h3>Resultado:</h3>";

        switch ($tipo_consulta) {
            case 'facturas_resumen':
                $sql = "
                    SELECT
                        f.Numero_Factura AS Numero,
                        f.Fecha_de_emision AS Fecha,
                        f.Tipo_de_moneda AS Moneda,
                        c.RUC_Cliente AS RUC_Cliente,
                        cn.Nombre_Cliente AS Cliente,
                        f.Sub_total_ventas AS Subtotal_sin_IGV,
                        f.IGV AS IGV,
                        f.Importe_total AS Importe_total
                    FROM Factura f
                    INNER JOIN Pedido p ON p.Numero_de_factura = f.Numero_Factura
                    INNER JOIN Cliente c ON c.RUC_Cliente = p.RUC_Cliente
                    LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
                    ORDER BY f.Fecha_de_emision DESC, f.Numero_Factura DESC
                ";
                $res = $conexion->query($sql);
                output_table_from_result($res);
                break;

            case 'pedidos_detalle':
                $sql = "
                    SELECT
                        p.Nombre_de_la_hoja AS Pedido,
                        p.Numero_de_factura AS Numero_Factura,
                        f.Fecha_de_emision,
                        c.RUC_Cliente,
                        cn.Nombre_Cliente,
                        pr.idProducto,
                        pr.Descripcion_del_producto AS Producto,
                        pp.Cantidad,
                        pr.Precio_Unitario,
                        (pp.Cantidad * pr.Precio_Unitario) AS Importe
                    FROM Pedido p
                    INNER JOIN Factura f ON f.Numero_Factura = p.Numero_de_factura
                    INNER JOIN Cliente c ON c.RUC_Cliente = p.RUC_Cliente
                    LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
                    INNER JOIN Pedido_Producto pp ON pp.NombreDeLaHoja_Pedido = p.Nombre_de_la_hoja
                    INNER JOIN Producto pr ON pr.idProducto = pp.ID_Producto
                    ORDER BY f.Fecha_de_emision DESC, p.Nombre_de_la_hoja, pr.idProducto
                ";
                $res = $conexion->query($sql);
                output_table_from_result($res);
                break;

            case 'empleados_audit':
                $sql = "SELECT * FROM empleado_audit ORDER BY fecha_cambio DESC, hora_cambio DESC";
                $res = $conexion->query($sql);
                output_table_from_result($res);
                break;

            case 'clientes_audit':
                $sql = "SELECT * FROM cliente_audit ORDER BY fecha_cambio DESC, hora_cambio DESC";
                $res = $conexion->query($sql);
                output_table_from_result($res);
                break;
        }
    }
    ?>

</section>

<?php
require_once __DIR__ . '/../views/footer.php';
?>
