<?php
session_start();
require_once "conexion.php";
require_once "header.php";

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

$ruc = $_GET["ruc"];

$mensaje = "";

// Agregar al carrito
if (isset($_POST["agregar"])) {

    $id = $_POST["idProducto"];
    $cantidad = intval($_POST["cantidad"]);

    $q = $conn->query("SELECT * FROM Producto WHERE idProducto='$id'");

    if ($q->num_rows > 0) {

        $p = $q->fetch_assoc();

        $_SESSION["carrito"][] = [
            "id" => $p["idProducto"],
            "descripcion" => $p["Descripcion_del_producto"],
            "precio" => $p["Precio_Unitario"],
            "cantidad" => $cantidad
        ];

        $mensaje = "Producto agregado.";
    } else {
        $mensaje = "Producto no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agregar Productos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header><h1>Agregar Productos a Factura</h1></header>

<main class="contenedor">

    <h3>RUC Cliente: <?php echo $ruc; ?></h3>

    <form method="POST" class="formulario">
        <label>ID Producto:</label>
        <input type="text" name="idProducto" required>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" required>

        <button class="btn btn-crear" name="agregar">Agregar</button>
    </form>

    <p style="color:green;"><?php echo $mensaje; ?></p>

    <a href="factura_ver_carrito.php?ruc=<?php echo $ruc; ?>" class="btn btn-guardar">Ver Carrito</a>

</main>
</body>
</html>
