<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';
?>

<section class="contenedor">
    <h2>Empleados</h2>

    <!-- BARRA SUPERIOR -->
    <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:25px;">

        <!-- ✅ RUTA CORRECTA -->
        <a class="btn btn-crear" href="/sistema_facturacion_v2/empleados/empleados_form.php">
            + Nuevo Empleado
        </a>

        <!-- BUSCADOR -->
        <div style="flex:1; max-width:350px;">
            <input
                type="text"
                id="buscadorEmpleados"
                placeholder="Buscar por DNI, nombre o teléfono..."
                style="
                    width:100%;
                    padding:10px 12px;
                    border-radius:8px;
                    border:1px solid #ccc;
                "
            >
        </div>

    </div>

    <table id="tablaEmpleados">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $sql = "
            SELECT e.DNI, e.Nombre, e.Correo, t.Numero_telefono
            FROM Empleado e
            LEFT JOIN Empleado_Telefono t ON t.DNI_Empleado = e.DNI
            ORDER BY e.DNI
        ";
        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['DNI']}</td>
                        <td>{$row['Nombre']}</td>
                        <td>{$row['Correo']}</td>
                        <td>{$row['Numero_telefono']}</td>
                        <td>
                            <a class='btn btn-editar'
                               href='/sistema_facturacion_v2/empleados/empleados_form.php?dni={$row['DNI']}'>
                               Editar
                            </a>
                            <a class='btn btn-eliminar'
                               href='/sistema_facturacion_v2/empleados/empleados_eliminar.php?dni={$row['DNI']}'
                               onclick=\"return confirm('¿Seguro de eliminar el empleado {$row['DNI']}?');\">
                               Eliminar
                            </a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td colspan='5' style='text-align:center;'>No hay empleados registrados</td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</section>

<!-- FILTRADO AUTOMÁTICO -->
<script>
document.getElementById("buscadorEmpleados").addEventListener("keyup", function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaEmpleados tbody tr");

    filas.forEach(fila => {
        const textoFila = fila.innerText.toLowerCase();
        fila.style.display = textoFila.includes(filtro) ? "" : "none";
    });
});
</script>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
