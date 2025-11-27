<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';
?>

<section class="contenedor">
    <h2>Productos</h2>
    <a class="btn btn-crear" href="productos_form.php">+ Nuevo Producto</a>

    <table>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Unidad</th>
            <th>Precio unitario</th>
            <th>Stock disponible</th>
            <th>Acciones</th>
        </tr>

        <?php
        $sql = "SELECT * FROM Producto ORDER BY idProducto";
        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['idProducto']}</td>
                        <td>{$row['Descripcion_del_producto']}</td>
                        <td>{$row['Unidad']}</td>
                        <td>S/ " . number_format($row['Precio_Unitario'], 2) . "</td>
                        <td>{$row['Stock']}</td>
                        <td>
                            <a class='btn btn-editar' href='productos_form.php?id={$row['idProducto']}'>Editar</a>
                            <a class='btn btn-eliminar' href='productos_eliminar.php?id={$row['idProducto']}'
                               onclick=\"return confirm('¿Seguro de eliminar el producto {$row['idProducto']}?');\">
                               Eliminar
                            </a>
                        </td>
                      </tr>";
            }
        }
        ?>
    </table>
</section>

<?php
require_once __DIR__ . '/../views/footer.php';
?>
