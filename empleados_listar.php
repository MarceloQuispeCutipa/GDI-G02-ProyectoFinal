<?php
require_once "conexion.php";
require_once "header.php";
?>

<section class="contenedor">
    <h2>Listado de Empleados</h2>
    <a class="btn btn-crear" href="empleados_form.php">+ Nuevo Empleado</a>

    <table>
        <tr>
            <th>DNI</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Correo</th>
            <th>RUC Empresa</th>
            <th>Acciones</th>
        </tr>

        <?php
        $sql = "SELECT * FROM Empleado;";
        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['DNI']}</td>
                        <td>{$row['Nombre']}</td>
                        <td>{$row['Cargo']}</td>
                        <td>{$row['Correo']}</td>
                        <td>{$row['RUC_Empresa']}</td>
                        <td>
                            <a class='btn btn-editar' href='empleados_form.php?dni={$row['DNI']}'>Editar</a>
                            <a class='btn btn-eliminar' 
                               href='empleados_eliminar.php?dni={$row['DNI']}'
                               onclick=\"return confirm('¿Seguro de eliminar el empleado {$row['DNI']}?');\">
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
require_once "footer.php";
?>
