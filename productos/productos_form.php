<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';

$nombre = $_POST['Descripcion_del_producto'] ?? '';

$producto_existente = false;
$producto_bd = [];

/* ----------------------
   VERIFICAR PRODUCTO
---------------------- */
if ($nombre !== '') {
    $stmt = $conexion->prepare("
        SELECT Descripcion_del_producto, Unidad, Precio_Unitario 
        FROM Producto
        WHERE Descripcion_del_producto = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $producto_existente = true;
        $producto_bd = $res->fetch_assoc();
    }
    $stmt->close();
}

// Unidades disponibles
$unidades = [
    "Unidad",
    "Paquete",
    "Kilogramo",
    "Gramo",
    "Litro",
    "Mililitro",
    "Caja",
    "Saco",
    "Bolsa"
];
?>

<section class="contenedor">
    <h2>Nuevo Producto</h2>

    <form method="post" class="formulario">

        <!-- PASO 1 -->
        <label>Nombre del producto:</label>
        <input
            type="text"
            name="Descripcion_del_producto"
            required
            value="<?php echo htmlspecialchars($nombre); ?>"
        >

        <button type="submit" class="btn btn-crear">
            Verificar producto
        </button>

        <?php if ($nombre !== ''): ?>

            <?php if ($producto_existente): ?>
                <!-- PRODUCTO EXISTENTE -->
                <p class="success">
                    El producto ya está registrado. Solo puede aumentar el stock.
                </p>

                <label>Cantidad a agregar:</label>
                <input
                    type="number"
                    name="Stock"
                    min="1"
                    step="1"
                    required
                >

                <input type="hidden" name="modo" value="existente">

            <?php else: ?>
                <!-- NUEVO PRODUCTO -->
                <p class="success">
                    Producto nuevo. Complete los datos.
                </p>

                <label>Unidad de medida:</label>
                <select name="Unidad" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($unidades as $u): ?>
                        <option value="<?php echo $u; ?>">
                            <?php echo $u; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Precio unitario (sin IGV):</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="Precio_Unitario"
                    required
                >

                <label>Stock inicial:</label>
                <input
                    type="number"
                    name="Stock"
                    min="1"
                    step="1"
                    required
                >

                <input type="hidden" name="modo" value="nuevo">

            <?php endif; ?>

            <button
                type="submit"
                formaction="productos_guardar.php"
                class="btn btn-guardar"
                style="margin-top:15px;"
            >
                Guardar
            </button>

        <?php endif; ?>

        <a class="btn btn-cancelar" href="productos_listar.php">Cancelar</a>
    </form>
</section>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
