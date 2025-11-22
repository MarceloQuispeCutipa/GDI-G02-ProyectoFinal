<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['idproducto'];
    $desc = $_POST['descripcion_del_producto'];
    $unidad = $_POST['unidad'];
    $precio = $_POST['precio_unitario'];
    $modo = $_POST['modo'];

    if ($modo === "nuevo") {
        $stmt = $conexion->prepare("INSERT INTO producto (idproducto, descripcion_del_producto, unidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $id, $desc, $unidad, $precio);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conexion->prepare("UPDATE producto SET descripcion_del_producto=?, unidad=?, precio_unitario=? WHERE idproducto=?");
        $stmt->bind_param("ssss", $desc, $unidad, $precio, $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: productos_listar.php");
exit;
?>
