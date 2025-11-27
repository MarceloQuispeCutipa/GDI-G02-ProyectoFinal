<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';

$ruc = isset($_GET['ruc']) ? $_GET['ruc'] : '';

$cliente = [
    'RUC_Cliente' => '',
    'Nombre_Cliente' => '',
    'Direccion_del_Cliente' => '',
    'ID_NombreCliente' => '',
    'ID_DireccionCliente' => ''
];

$modo = "nuevo";

if ($ruc != '') {
    $stmt = $conexion->prepare("
        SELECT 
            c.RUC_Cliente,
            c.ID_NombreCliente,
            c.ID_DireccionCliente,
            cn.Nombre_Cliente,
            cd.Direccion_del_Cliente
        FROM Cliente c
        LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
        LEFT JOIN Cliente_Direccion cd ON cd.ID_DireccionCliente = c.ID_DireccionCliente
        WHERE c.RUC_Cliente = ?
    ");

    $stmt->bind_param("s", $ruc);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $cliente = $resultado->fetch_assoc();
        $modo = "editar";
    }

    $stmt->close();
}
?>

<section class="contenedor">
    <h2><?php echo ($modo == "nuevo") ? "Nuevo Cliente" : "Editar Cliente"; ?></h2>

    <form action="clientes_guardar.php" method="post" class="formulario">

        <!-- RUC -->
        <label for="ruc_cliente">RUC:</label>
        <input
            type="text"
            name="ruc_cliente"
            id="ruc_cliente"
            maxlength="11"
            minlength="11"
            pattern="[0-9]{11}"
            inputmode="numeric"
            title="El RUC debe contener exactamente 11 dígitos numéricos"
            value="<?php echo htmlspecialchars($cliente['RUC_Cliente']); ?>"
            <?php echo ($modo == "editar") ? "readonly" : ""; ?>
            required
        >

        <!-- NOMBRE / RAZÓN SOCIAL -->
        <label for="nombre_cliente">Nombre o Razón Social:</label>
        <input
            type="text"
            name="nombre_cliente"
            id="nombre_cliente"
            value="<?php echo htmlspecialchars($cliente['Nombre_Cliente']); ?>"
            required
        >

        <!-- DIRECCIÓN -->
        <label for="direccion_del_cliente">Dirección:</label>
        <input
            type="text"
            name="direccion_del_cliente"
            id="direccion_del_cliente"
            value="<?php echo htmlspecialchars($cliente['Direccion_del_Cliente']); ?>"
            required
        >

        <!-- CAMPOS INTERNOS -->
        <input type="hidden" name="id_nombre" value="<?php echo htmlspecialchars($cliente['ID_NombreCliente']); ?>">
        <input type="hidden" name="id_direccion" value="<?php echo htmlspecialchars($cliente['ID_DireccionCliente']); ?>">
        <input type="hidden" name="modo" value="<?php echo $modo; ?>">

        <button type="submit" class="btn btn-guardar">Guardar</button>
        <a class="btn btn-cancelar" href="clientes_listar.php">Cancelar</a>
    </form>
</section>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
