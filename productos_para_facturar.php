<?php
// productos_para_factura.php
session_start();
require_once "conexion.php";
$conn = $conexion;

$q = $_GET['q'] ?? '';

$sql = "SELECT idProducto, Descripcion_del_producto, Unidad, Precio_Unitario FROM Producto
        WHERE Descripcion_del_producto LIKE ? OR idProducto LIKE ?
        LIMIT 100";
$stmt = $conn->prepare($sql);
$like = "%$q%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Productos</title>
<link rel="stylesheet" href="estilos.css"></head>
<body>
<header><h1>Productos (Agregar a factura)</h1></header>
<main class="contenedor">
    <form method="GET" class="formulario">
        <label>Buscar producto (código o descripción):</label>
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-crear" type="submit">Buscar</button>
    </form>

    <table>
        <tr>
            <th>ID</th><th>Descripción</th><th>Unidad</th><th>Precio</th><th>Acción</th>
        </tr>
        <?php while ($p = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($p['idProducto']) ?></td>
            <td style="text-align:left"><?= htmlspecialchars($p['Descripcion_del_producto']) ?></td>
            <td><?= htmlspecialchars($p['Unidad']) ?></td>
            <td><?= number_format($p['Precio_Unitario'],2) ?></td>
            <td>
                <form action="carrito_agregar.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($p['idProducto']) ?>">
                    <input type="hidden" name="precio" value="<?= htmlspecialchars($p['Precio_Unitario']) ?>">
                    <input type="number" name="cantidad" value="1" min="1" style="width:70px" required>
                    <button class="btn btn-crear" type="submit">Agregar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a class="btn btn-editar" href="carrito_ver.php">Ver carrito (<?= isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0 ?>)</a>
    <a class="btn btn-crear" href="index.php">Volver</a>
</main>
</body>
</html>
