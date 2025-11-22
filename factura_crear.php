<?php
session_start();
require_once "conexion.php";
$conn = $conexion;

$ruc_cliente = isset($_GET['ruc']) ? $_GET['ruc'] : '';
if ($ruc_cliente == '') {
    die("No se especificó RUC del cliente.");
}

/* Obtener cliente */
$sqlCliente = "
    SELECT 
        c.RUC_Cliente,
        cn.Nombre_Cliente,
        cd.Direccion_del_Cliente
    FROM Cliente c
    LEFT JOIN Cliente_Nombre cn ON c.ID_NombreCliente = cn.ID_Nombre_Cliente
    LEFT JOIN Cliente_Direccion cd ON c.ID_DireccionCliente = cd.ID_DireccionCliente
    WHERE c.RUC_Cliente = ?
";
$stmt = $conn->prepare($sqlCliente);
$stmt->bind_param("s", $ruc_cliente);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) die("Cliente no encontrado.");
$cliente = $res->fetch_assoc();
$stmt->close();

/* Obtener productos disponibles */
$productos = [];
$resProd = $conn->query("SELECT idProducto, Descripcion_del_producto, Precio_Unitario, Unidad FROM Producto");
while ($row = $resProd->fetch_assoc()) {
    $productos[] = $row;
}

/* Agregar al carrito */
if (isset($_POST['agregar'])) {
    $idProd = $_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    foreach ($productos as $prod) {
        if ($prod['idProducto'] == $idProd) {
            $item = [
                'id' => $prod['idProducto'],
                'descripcion' => $prod['Descripcion_del_producto'],
                'precio' => $prod['Precio_Unitario'],
                'cantidad' => $cantidad
            ];
            $_SESSION['carrito'][] = $item;
        }
    }
}

/* Vaciar carrito */
if (isset($_POST['vaciar'])) {
    unset($_SESSION['carrito']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Factura</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <h1>Crear Factura</h1>
</header>

<main class="contenedor">
    <div class="tarjeta">
        <h3>Cliente:</h3>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['Nombre_Cliente']) ?></p>
        <p><strong>RUC:</strong> <?= htmlspecialchars($cliente['RUC_Cliente']) ?></p>

        <h3>Agregar productos al carrito:</h3>
        <form method="POST">
            <label>Producto:</label>
            <select name="producto_id" required>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['idProducto'] ?>">
                        <?= $p['Descripcion_del_producto'] ?> - S/ <?= $p['Precio_Unitario'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Cantidad:</label>
            <input type="number" name="cantidad" value="1" min="1" required>
            <button type="submit" name="agregar" class="btn btn-crear">Agregar</button>
            <button type="submit" name="vaciar" class="btn btn-eliminar">Vaciar Carrito</button>
        </form>

        <h3>Carrito:</h3>
        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
            <ul>
                <?php foreach ($_SESSION['carrito'] as $item): ?>
                    <li><?= $item['descripcion'] ?> - S/ <?= $item['precio'] ?> x <?= $item['cantidad'] ?></li>
                <?php endforeach; ?>
            </ul>
            <form action="factura_generar.php" method="POST">
                <label>DNI Empleado:</label>
                <input type="text" name="dni_empleado" required>
                <input type="hidden" name="ruc_cliente" value="<?= htmlspecialchars($cliente['RUC_Cliente']) ?>">
                <button type="submit" class="btn btn-crear">Generar Factura</button>
            </form>
        <?php else: ?>
            <p>No hay productos en el carrito.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
