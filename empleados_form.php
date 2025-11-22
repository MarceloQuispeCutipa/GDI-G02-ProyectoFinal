<?php
require_once "conexion.php";
require_once "header.php";

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

$empleado = [
    'DNI' => '',
    'Correo' => '',
    'Cargo' => '',
    'Nombre' => '',
    'RUC_Empresa' => ''
];

$modo = "nuevo";

if ($dni != '') {
    $stmt = $conexion->prepare("SELECT * FROM Empleado WHERE DNI = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $empleado = $resultado->fetch_assoc();
        $modo = "editar";
    }
    $stmt->close();
}
?>

<section class="contenedor">
    <h2><?php echo ($modo == "nuevo") ? "Nuevo Empleado" : "Editar Empleado"; ?></h2>

    <form action="empleados_guardar.php" method="post" class="formulario">
        <label for="dni">DNI:</label>
        <input type="text" name="DNI" id="dni" maxlength="8"
               value="<?php echo htmlspecialchars($empleado['DNI']); ?>"
               <?php echo ($modo == "editar") ? "readonly" : ""; ?> required>

        <label for="nombre">Nombre completo:</label>
        <input type="text" name="Nombre" id="nombre"
               value="<?php echo htmlspecialchars($empleado['Nombre']); ?>" required>

        <label for="cargo">Cargo:</label>
        <input type="text" name="Cargo" id="cargo"
               value="<?php echo htmlspecialchars($empleado['Cargo']); ?>" required>

        <label for="correo">Correo:</label>
        <input type="email" name="Correo" id="correo"
               value="<?php echo htmlspecialchars($empleado['Correo']); ?>" required>

        <label for="ruc_empresa">RUC Empresa:</label>
        <input type="text" name="RUC_Empresa" id="ruc_empresa" maxlength="11"
               value="<?php echo htmlspecialchars($empleado['RUC_Empresa']); ?>" required>

        <input type="hidden" name="modo" value="<?php echo $modo; ?>">

        <button type="submit" class="btn btn-guardar">Guardar</button>
        <a class="btn btn-cancelar" href="empleados_listar.php">Cancelar</a>
    </form>
</section>

<?php
require_once "footer.php";
?>
