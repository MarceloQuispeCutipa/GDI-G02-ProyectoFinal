<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../views/header.php';

/* ----------------------
   CANCELAR FACTURA
---------------------- */
if (isset($_GET['cancelar'])) {
    unset($_SESSION['detalle_factura'], $_SESSION['ruc_cliente']);
    header("Location: factura_crear.php");
    exit;
}

/* ----------------------
   SESIÓN
---------------------- */
if (!isset($_SESSION['detalle_factura'])) {
    $_SESSION['detalle_factura'] = [];
}

/* ----------------------
   CLIENTES (RUC)
---------------------- */
$clientes = [];
$resCli = $conexion->query("
    SELECT c.RUC_Cliente, cn.Nombre_Cliente
    FROM Cliente c
    LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
");
$clientes = $resCli->fetch_all(MYSQLI_ASSOC);

/* ----------------------
   EMPLEADOS (DNI)
---------------------- */
$empleados = [];
$resEmp = $conexion->query("
    SELECT DNI, Nombre
    FROM Empleado
");
$empleados = $resEmp->fetch_all(MYSQLI_ASSOC);

/* ----------------------
   VALIDAR RUC
---------------------- */
$error_ruc = '';
$razon_social = '';

if (isset($_POST['validar_ruc'])) {
    $ruc = $_POST['ruc_cliente'];

    $stmt = $conexion->prepare("
        SELECT cn.Nombre_Cliente 
        FROM Cliente c
        LEFT JOIN Cliente_Nombre cn ON cn.ID_Nombre_Cliente = c.ID_NombreCliente
        WHERE c.RUC_Cliente = ?
    ");
    $stmt->bind_param("s", $ruc);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $error_ruc = "El RUC ingresado no existe.";
    } else {
        $row = $res->fetch_assoc();
        $_SESSION['ruc_cliente'] = $ruc;
        $_SESSION['razon_social'] = $row['Nombre_Cliente'];
        $razon_social = $row['Nombre_Cliente'];
    }
    $stmt->close();
}

/* ----------------------
   PRODUCTOS
---------------------- */
$productos = [];
$resProd = $conexion->query("
    SELECT idProducto, Descripcion_del_producto, Precio_Unitario, Stock
    FROM Producto
    WHERE Stock > 0
");
$productos = $resProd->fetch_all(MYSQLI_ASSOC);

/* ----------------------
   TOTALES
---------------------- */
$subtotal = 0;
foreach ($_SESSION['detalle_factura'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$igv = round($subtotal * 0.18, 2);
$total = round($subtotal + $igv, 2);
?>

<section class="contenedor">
<h2>Emitir factura</h2>

<?php if (!isset($_SESSION['ruc_cliente'])): ?>
<!-- PASO 1 -->
<form method="post" class="formulario">
    <h3>Cliente</h3>

    <label>RUC del cliente:</label>
    <input list="lista_clientes" name="ruc_cliente" maxlength="11" required>
    <datalist id="lista_clientes">
        <?php foreach ($clientes as $c): ?>
            <option value="<?= $c['RUC_Cliente'] ?>">
                <?= $c['RUC_Cliente'] ?> - <?= $c['Nombre_Cliente'] ?>
            </option>
        <?php endforeach; ?>
    </datalist>

    <?php if ($error_ruc): ?>
        <p class="error"><?= $error_ruc ?></p>
    <?php endif; ?>

    <button name="validar_ruc" class="btn btn-crear">Continuar</button>
    <a href="?cancelar=1" class="btn btn-cancelar">Cancelar</a>
</form>

<?php else: ?>

<!-- PASO 2 -->
<form method="post" class="formulario">
    <h3>Datos de la operación</h3>

    <p><strong>RUC:</strong> <?= $_SESSION['ruc_cliente'] ?></p>
    <p><strong>Razón social:</strong> <?= $_SESSION['razon_social'] ?></p>

    <label>DNI del empleado:</label>
    <input list="lista_empleados" name="dni_empleado" required>
    <datalist id="lista_empleados">
        <?php foreach ($empleados as $e): ?>
            <option value="<?= $e['DNI'] ?>">
                <?= $e['DNI'] ?> - <?= $e['Nombre'] ?>
            </option>
        <?php endforeach; ?>
    </datalist>

    <label>Tipo de moneda:</label>
    <select name="tipo_moneda">
        <option value="PEN">PEN</option>
        <option value="USD">USD</option>
    </select>

    <h3>Agregar producto</h3>

    <label>Producto:</label>
    <select name="id_producto">
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['idProducto'] ?>">
                <?= $p['Descripcion_del_producto'] ?> (Stock: <?= $p['Stock'] ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label>Cantidad:</label>
    <input type="number" name="cantidad" min="1" required>

    <button formaction="factura_agregar_producto.php" class="btn btn-crear">
        Agregar producto
    </button>

    <a href="?cancelar=1" class="btn btn-eliminar">Cancelar factura</a>
</form>

<!-- DETALLE -->
<div class="detalle-factura">
<h3>Detalle</h3>

<?php if (!empty($_SESSION['detalle_factura'])): ?>
<table>
<tr>
    <th>Producto</th><th>Cantidad</th><th>Precio</th><th>Total</th><th></th>
</tr>
<?php foreach ($_SESSION['detalle_factura'] as $i => $item): ?>
<tr>
    <td><?= $item['descripcion'] ?></td>
    <td><?= $item['cantidad'] ?></td>
    <td><?= number_format($item['precio'],2) ?></td>
    <td><?= number_format($item['precio']*$item['cantidad'],2) ?></td>
    <td>
        <a href="factura_agregar_producto.php?accion=eliminar&index=<?= $i ?>"
           class="btn btn-eliminar">Eliminar</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<div class="resumen-totales">
    <div>Subtotal: <?= $subtotal ?></div>
    <div>IGV: <?= $igv ?></div>
    <div><strong>Total: <?= $total ?></strong></div>
</div>

<form action="factura_generar.php" method="post">
    <button class="btn btn-guardar">Emitir factura</button>
</form>
<?php endif; ?>

</div>
<?php endif; ?>
</section>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
