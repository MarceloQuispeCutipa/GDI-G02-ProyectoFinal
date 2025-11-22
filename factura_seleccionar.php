<?php
require_once "conexion.php";
require_once "conexion.php";
require_once "header.php";
$dni = $_GET['dni'];

$sql = "SELECT p.id_pedido, p.fecha, p.monto, c.nombre 
        FROM pedidos p 
        INNER JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE c.dni = '$dni'";

$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Pedidos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="container">
    <h1>Seleccionar Pedidos para Facturar</h1>

    <?php if ($resultado->num_rows > 0): ?>

        <form action="factura_generar.php" method="POST">
            <input type="hidden" name="dni" value="<?php echo $dni; ?>">

            <table class="tabla">
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>ID Pedido</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Cliente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="pedidos[]" value="<?= $row['id_pedido'] ?>"></td>
                        <td><?= $row['id_pedido'] ?></td>
                        <td><?= $row['fecha'] ?></td>
                        <td>S/ <?= $row['monto'] ?></td>
                        <td><?= $row['nombre'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <button type="submit">Generar Factura</button>
        </form>

    <?php else: ?>
        <p>No existen pedidos para este cliente.</p>
    <?php endif; ?>
</div>

</body>
</html>
