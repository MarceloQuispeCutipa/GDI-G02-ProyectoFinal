<?php
session_start();
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'];
    $cantidad = intval($_POST['cantidad']);

    /* Obtener datos del producto */
    $stmt = $conexion->prepare("SELECT idProducto, Descripcion_del_producto, Precio_Unitario FROM Producto WHERE idProducto = ?");
    $stmt->bind_param("s", $id_producto);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        die("Producto no encontrado.");
    }

    $prod = $res->fetch_assoc();
    $stmt->close();

    /* Agregar al carrito */
    $_SESSION['carrito'][] = [
        "id" => $prod['idProducto'],
        "descripcion" => $prod['Descripcion_del_producto'],
        "precio" => floatval($prod['Precio_Unitario']),
        "cantidad" => $cantidad
    ];

    header("Location: factura_crear.php");
    exit;
}
