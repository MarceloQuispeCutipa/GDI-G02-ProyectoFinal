<?php
require_once "conexion.php";
require_once "header.php";
?>

<section class="contenedor">
    <h2>Listado de Clientes</h2>
    <a class="btn btn-crear" href="clientes_form.php">+ Nuevo Cliente</a>

    <table>
        <tr>
            <th>RUC</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Acciones</th>
        </tr>

        <?php
        $sql = "
            SELECT 
                c.RUC_Cliente AS ruc_cliente,
                cn.nombre_cliente,
                cd.direccion_del_cliente
            FROM Cliente c
            LEFT JOIN Cliente_Nombre cn ON cn.id_nombre_cliente = c.ID_NombreCliente
            LEFT JOIN Cliente_Direccion cd ON cd.id_direccioncliente = c.ID_DireccionCliente
            ORDER BY c.RUC_Cliente
        ";

        $resultado = $conexion->query($sql);

        while ($row = $resultado->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['ruc_cliente']}</td>
                    <td>{$row['nombre_cliente']}</td>
                    <td>{$row['direccion_del_cliente']}</td>
                    <td>
                        <a class='btn btn-editar' href='clientes_form.php?ruc={$row['ruc_cliente']}'>Editar</a>
                        <a class='btn btn-eliminar' href='clientes_eliminar.php?ruc={$row['ruc_cliente']}'
                           onclick=\"return confirm('¿Seguro de eliminar al cliente {$row['ruc_cliente']}?');\">
                           Eliminar
                        </a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</section>

<?php require_once "footer.php"; ?>
