<?php
session_start();
require_once "conexion.php";
require_once "header.php";

if (isset($_POST['vaciar_carrito'])) {
    unset($_SESSION['carrito']);
}

/* Inicializar carrito */
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

/* Obtener productos */
$sqlProductos = "SELECT idProducto, Descripcion_del_producto, Precio_Unitario FROM Producto";
$resultado = $conexion->query($sqlProductos);
$productos = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<section class="contenedor">

<h2>Crear Factura</h2>

<!-- FORMULARIO PARA AGREGAR PRODUCTOS -->
<form action="factura_agregar_producto.php" method="POST" class="formulario">

    <h3>Agregar productos al carrito</h3>

    <label>Producto:</label>
    <select name="id_producto" required>
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['idProducto'] ?>">
                <?= $p['Descripcion_del_producto'] ?> - S/ <?= number_format($p['Precio_Unitario'],2) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Cantidad:</label>
    <input type="number" name="cantidad" value="1" min="1" required>

    <button type="submit" class="btn btn-crear">Agregar al Carrito</button>

</form>

<h3>Carrito actual:</h3>
<div class="formulario">

    <?php if (empty($_SESSION['carrito'])): ?>
        <p>No hay productos en el carrito.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($_SESSION['carrito'] as $item): ?>
                <li><?= htmlspecialchars($item['descripcion']) ?> - 
                    S/ <?= number_format($item['precio'],2) ?> x <?= $item['cantidad'] ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="factura_crear.php" style="margin-bottom: auto;">
        <button type="submit" name="vaciar_carrito" class="btn btn-eliminar">
            Vaciar carrito
        </button>
    </form>

</div>

<!-- FORMULARIO PARA GENERAR FACTURA -->
<form action="factura_generar.php" method="POST" class="formulario">

    <label>Tipo de Moneda:</label>
    <select name="tipo_moneda" required>
        <option value="PEN">PEN</option>
        <option value="USD">USD</option>
    </select>

    <label>RUC de la Empresa:</label>
    <input type="text" name="ruc_empresa" required maxlength="11">

    <label>DNI del Empleado:</label>
    <input type="text" name="dni_empleado" required maxlength="8">

    <label>RUC del Cliente:</label>
    <input type="text" name="cliente" required maxlength="11">

    <button type="submit" class="btn btn-crear">Generar Factura</button>

</form>

</section>
</body>
</html>
