<?php
// carrito_agregar.php
session_start();
require_once "conexion.php";

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $precio = floatval($_POST['precio'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 1);

    // buscar producto real (seguridad)
    $stmt = $conexion->prepare("SELECT idProducto, Descripcion_del_producto, Precio_Unitario FROM Producto WHERE idProducto = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($p) {
        $_SESSION['carrito'][] = [
            'id' => $p['idProducto'],
            'descripcion' => $p['Descripcion_del_producto'],
            'precio' => floatval($p['Precio_Unitario']),
            'cantidad' => $cantidad
        ];
        header("Location: carrito_ver.php?added=1");
        exit;
    } else {
        header("Location: productos_para_factura.php?error=notfound");
        exit;
    }
} else {
    header("Location: productos_para_factura.php");
    exit;
}
