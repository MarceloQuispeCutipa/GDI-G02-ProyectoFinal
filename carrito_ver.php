<?php
// carrito_ver.php
session_start();
require_once "conexion.php";

// eliminar item
if (isset($_GET['remove'])) {
    $idx = intval($_GET['remove']);
    if (isset($_SESSION['carrito'][$idx])) {
        array_splice($_SESSION['carrito'], $idx, 1);
    }
    header("Location: carrito_ver.php");
    exit;
}

// vaciar carrito
if (isset($_GET['clear'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito_ver.php");
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
foreach ($carrito as $it) $total += $it['precio'] * $it['cantidad'];
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Carrito</title>
<link rel="stylesheet" href="estilos.css"></head>
<body>
<header><h1>Carrito de Factura</h1></header>
<main class="contenedor">
    <?php if (empty($carrito)): ?>
        <p>No hay productos en el carrito.</p>
        <a class="btn btn-crear" href="productos_para_factura.php">Agregar productos</a>
    <?php else: ?>
        <table>
            <tr><th>#</th><th>ID</th><th>Descripción</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th>Acción</th></tr>
            <?php foreach ($carrito as $i => $it): $sub = $it['precio'] * $it['cantidad']; ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($it['id']) ?></td>
                <td style="text-align:left"><?= htmlspecialchars($it['descripcion']) ?></td>
                <td><?= $it['cantidad'] ?></td>
                <td><?= number_format($it['precio'],2) ?></td>
                <td><?= number_format($sub,2) ?></td>
                <td><a class="btn btn-eliminar" href="carrito_ver.php?remove=<?= $i ?>" onclick="return confirm('Quitar?')">Quitar</a></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3>Total: S/ <?= number_format($total,2) ?></h3>

        <a class="btn btn-cancelar" href="carrito_ver.php?clear=1" onclick="return confirm('Vaciar carrito?')">Vaciar carrito</a>

        <hr>

        <h3>Continuar para emitir factura</h3>
        <a class="btn btn-crear" href="factura_buscar_cliente.php">Buscar Cliente / Emitir</a>
    <?php endif; ?>
    <br><br>
    <a class="btn" href="productos_para_factura.php">Seguir comprando</a>
</main>
</body>
</html>
