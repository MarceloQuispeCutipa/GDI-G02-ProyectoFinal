<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';

/* ----------------------
   CARGAR CLIENTES
---------------------- */
$sql = "
    SELECT 
        c.RUC_Cliente AS ruc_cliente,
        cn.Nombre_Cliente AS nombre_cliente,
        cd.Direccion_del_Cliente AS direccion_del_cliente
    FROM Cliente c
    LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
    LEFT JOIN Cliente_Direccion cd ON cd.ID_DireccionCliente = c.ID_DireccionCliente
    ORDER BY c.RUC_Cliente
";
$resultado = $conexion->query($sql);
?>

<section class="contenedor">
    <h2>Clientes</h2>

    <!-- BARRA SUPERIOR -->
    <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:25px;">

        <a class="btn btn-crear" href="clientes_form.php">+ Nuevo Cliente</a>

        <!-- BUSCADOR -->
        <div style="flex:1; max-width:350px;">
            <input
                type="text"
                id="buscadorClientes"
                placeholder="Buscar por RUC o nombre..."
                style="
                    width:100%;
                    padding:10px 12px;
                    border-radius:8px;
                    border:1px solid #ccc;
                "
            >
        </div>

    </div>

    <table id="tablaClientes">
        <thead>
            <tr>
                <th>RUC</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['ruc_cliente']}</td>
                        <td>{$row['nombre_cliente']}</td>
                        <td>{$row['direccion_del_cliente']}</td>
                        <td>
                            <a class='btn btn-editar' href='clientes_form.php?ruc={$row['ruc_cliente']}'>Editar</a>
                            <a class='btn btn-eliminar'
                               href='clientes_eliminar.php?ruc={$row['ruc_cliente']}'
                               onclick=\"return confirm('¿Seguro de eliminar al cliente {$row['ruc_cliente']}?');\">
                                Eliminar
                            </a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td colspan='4' style='text-align:center;'>No hay clientes registrados</td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</section>

<!-- FILTRADO EN TIEMPO REAL -->
<script>
document.getElementById("buscadorClientes").addEventListener("keyup", function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaClientes tbody tr");

    filas.forEach(fila => {
        const textoFila = fila.innerText.toLowerCase();
        fila.style.display = textoFila.includes(filtro) ? "" : "none";
    });
});
</script>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
