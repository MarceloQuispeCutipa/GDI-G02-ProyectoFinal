<?php
require_once "conexion.php";
require_once "header.php";

$ruc = isset($_GET['ruc']) ? $_GET['ruc'] : '';

$cliente = [
    'ruc_cliente' => '',
    'nombre_cliente' => '',
    'direccion_del_cliente' => '',
    'id_nombrecliente' => '',
    'id_direccioncliente' => ''
];

$modo = "nuevo";

if ($ruc != '') {
    $stmt = $conexion->prepare("
        SELECT 
            c.ruc_cliente,
            c.id_nombrecliente,
            c.id_direccioncliente,
            cn.nombre_cliente,
            cd.direccion_del_cliente
        FROM cliente c
        JOIN cliente_nombre cn ON cn.id_nombre_cliente = c.id_nombrecliente
        JOIN cliente_direccion cd ON cd.id_direccioncliente = c.id_direccioncliente
        WHERE c.ruc_cliente = ?
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

        <label for="ruc_cliente">RUC:</label>
        <input type="text" name="ruc_cliente" id="ruc_cliente" maxlength="11"
               value="<?php echo htmlspecialchars($cliente['ruc_cliente']); ?>"
               <?php echo ($modo == "editar") ? "readonly" : ""; ?>
               required>

        <label for="nombre_cliente">Nombre o Razón Social:</label>
        <input type="text" name="nombre_cliente" id="nombre_cliente"
               value="<?php echo htmlspecialchars($cliente['nombre_cliente']); ?>" required>

        <label for="direccion_del_cliente">Dirección:</label>
        <input type="text" name="direccion_del_cliente" id="direccion_del_cliente"
               value="<?php echo htmlspecialchars($cliente['direccion_del_cliente']); ?>" required>

        <input type="hidden" name="id_nombre" value="<?php echo $cliente['id_nombrecliente']; ?>">
        <input type="hidden" name="id_direccion" value="<?php echo $cliente['id_direccioncliente']; ?>">
        <input type="hidden" name="modo" value="<?php echo $modo; ?>">

        <button type="submit" class="btn btn-guardar">Guardar</button>
        <a class="btn btn-cancelar" href="clientes_listar.php">Cancelar</a>
    </form>
</section>

<?php require_once "footer.php"; ?>
