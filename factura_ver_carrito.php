<?php
session_start();
require_once "conexion.php";
require_once "header.php";

$ruc = $_GET["ruc"];
$carrito = $_SESSION["carrito"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carrito</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header><h1>Productos Seleccionados</h1></header>

<main class="contenedor">

<table>
<tr>
    <th>ID</th>
    <th>Descripción</th>
    <th>Precio</th>
    <th>Cantidad</th>
    <th>Subtotal</th>
</tr>

<?php 
$total = 0;
foreach ($carrito as $p):
    $sub = $p["precio"] * $p["cantidad"];
    $total += $sub;
?>
<tr>
    <td><?= $p["id"]; ?></td>
    <td><?= $p["descripcion"]; ?></td>
    <td><?= $p["precio"]; ?></td>
    <td><?= $p["cantidad"]; ?></td>
    <td><?= $sub; ?></td>
</tr>
<?php endforeach; ?>
</table>

<h3>Total: S/ <?= $total; ?></h3>

<a href="factura_generar.php?ruc=<?=$ruc?>" class="btn btn-guardar">Emitir Factura</a>

</main>
</body>
</html>
