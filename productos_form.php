<?php
require_once "conexion.php";
require_once "header.php";

$id = isset($_GET['id']) ? $_GET['id'] : '';
$producto = [
    'idproducto' => '',
    'descripcion_del_producto' => '',
    'unidad' => '',
    'precio_unitario' => ''
];

$modo = "nuevo";

if ($id != '') {
    $stmt = $conexion->prepare("SELECT * FROM producto WHERE idproducto = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
        $modo = "editar";
    }
    $stmt->close();
}
?>

<section class="contenedor">
    <h2><?php echo ($modo == "nuevo") ? "Nuevo Producto" : "Editar Producto"; ?></h2>

    <form action="productos_guardar.php" method="post" class="formulario">
        <label for="idproducto">ID Producto:</label>
        <input type="text" name="idproducto" id="idproducto"
               value="<?php echo htmlspecialchars($producto['idproducto']); ?>"
               <?php echo ($modo == "editar") ? "readonly" : ""; ?> required>

        <label for="descripcion_del_producto">Descripción:</label>
        <textarea name="descripcion_del_producto" id="descripcion_del_producto" required><?php
            echo htmlspecialchars($producto['descripcion_del_producto']);
        ?></textarea>

        <label for="unidad">Unidad:</label>
        <input type="text" name="unidad" id="unidad"
               value="<?php echo htmlspecialchars($producto['unidad']); ?>" required>

        <label for="precio_unitario">Precio Unitario (S/.):</label>
        <input type="number" step="0.01" name="precio_unitario" id="precio_unitario"
               value="<?php echo htmlspecialchars($producto['precio_unitario']); ?>" required>

        <input type="hidden" name="modo" value="<?php echo $modo; ?>">

        <button type="submit" class="btn btn-guardar">Guardar</button>
        <a class="btn btn-cancelar" href="productos_listar.php">Cancelar</a>
    </form>
</section>

<?php
require_once "footer.php";
?>
