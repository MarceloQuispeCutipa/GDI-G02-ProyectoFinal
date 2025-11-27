<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

$empleado = [
    'DNI' => '',
    'Correo' => '',
    'Nombre' => '',
    'Numero_telefono' => ''
];

$modo = "nuevo";

if ($dni != '') {
    $stmt = $conexion->prepare("
        SELECT 
            e.DNI,
            e.Correo,
            e.Nombre,
            t.Numero_telefono
        FROM Empleado e
        LEFT JOIN Empleado_Telefono t 
            ON t.DNI_Empleado = e.DNI
        WHERE e.DNI = ?
    ");
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

        <!-- DNI -->
        <label for="dni">DNI:</label>
        <input
            type="text"
            name="DNI"
            id="dni"
            maxlength="8"
            minlength="8"
            pattern="[0-9]{8}"
            inputmode="numeric"
            title="El DNI debe contener exactamente 8 dígitos numéricos"
            value="<?php echo htmlspecialchars($empleado['DNI']); ?>"
            <?php echo ($modo == "editar") ? "readonly" : ""; ?>
            required
        >

        <!-- NOMBRE -->
        <label for="nombre">Nombre completo:</label>
        <input
            type="text"
            name="Nombre"
            id="nombre"
            pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+"
            title="Solo se permiten letras y espacios"
            value="<?php echo htmlspecialchars($empleado['Nombre']); ?>"
            required
        >

        <!-- CORREO -->
        <label for="correo">Correo:</label>
        <input
            type="email"
            name="Correo"
            id="correo"
            value="<?php echo htmlspecialchars($empleado['Correo']); ?>"
            required
        >

        <!-- TELÉFONO -->
        <label for="telefono">Teléfono:</label>
        <input
            type="text"
            name="Telefono"
            id="telefono"
            maxlength="9"
            minlength="9"
            pattern="[0-9]{9}"
            inputmode="numeric"
            title="El teléfono debe contener exactamente 9 dígitos numéricos"
            value="<?php echo htmlspecialchars($empleado['Numero_telefono']); ?>"
            required
        >

        <input type="hidden" name="modo" value="<?php echo $modo; ?>">

        <button type="submit" class="btn btn-guardar">Guardar</button>
        <a class="btn btn-cancelar" href="empleados_listar.php">Cancelar</a>
    </form>
</section>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
