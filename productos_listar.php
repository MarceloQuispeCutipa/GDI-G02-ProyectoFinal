<?php
require_once "conexion.php";
require_once "header.php";
?>

<section class="contenedor">
    <h2>Listado de Productos</h2>
    <a class="btn btn-crear" href="productos_form.php">+ Nuevo Producto</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Unidad</th>
            <th>Precio Unitario (S/.)</th>
            <th>Acciones</th>
        </tr>
        <?php
        $sql = "SELECT idproducto, descripcion_del_producto, unidad, precio_unitario FROM producto";
        $resultado = $conexion->query($sql);
        while ($row = $resultado->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['idproducto']}</td>
                    <td>{$row['descripcion_del_producto']}</td>
                    <td>{$row['unidad']}</td>
                    <td>{$row['precio_unitario']}</td>
                    <td>
                        <a class='btn btn-editar' href='productos_form.php?id={$row['idproducto']}'>Editar</a>
                        <a class='btn btn-eliminar' href='productos_eliminar.php?id={$row['idproducto']}' onclick=\"return confirm('¿Seguro de eliminar el producto {$row['idproducto']}?');\">Eliminar</a>
                    </td>
                </tr>";
        }
        ?>
    </table>
</section>

<?php
require_once "footer.php";
?>
