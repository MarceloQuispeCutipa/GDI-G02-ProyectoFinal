<?php
session_start();
require_once "conexion.php";
require_once "header.php";

$buscar = $_GET['buscar'];

$sql = "
SELECT * FROM producto
WHERE Descripcion_del_producto LIKE '%$buscar%'
";

$res = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Producto</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="contenedor">
<h2>Resultados de búsqueda: <?= $buscar ?></h2>

<table>
    <tr>
        <th>ID</th>
        <th>Descripción</th>
        <th>Unidad</th>
        <th>Precio</th>
        <th>Acción</th>
    </tr>

    <?php while ($p = $res->fetch_assoc()) { ?>
    <tr>
        <td><?= $p['idProducto'] ?></td>
        <td><?= $p['Descripcion_del_producto'] ?></td>
        <td><?= $p['Unidad'] ?></td>
        <td><?= $p['Precio_Unitario'] ?></td>
        <td>
            <form action="factura_agregar_producto.php" method="POST">
                <input type="hidden" name="id" value="<?= $p['idProducto'] ?>">
                <input type="number" name="cantidad" min="1" value="1" required>
                <button class="btn btn-crear">Agregar</button>
            </form>
        </td>
    </tr>
    <?php } ?>

</table>

<a href="factura_ver.php" class="btn btn-editar">Ver factura</a>

</div>

</body>
</html>
