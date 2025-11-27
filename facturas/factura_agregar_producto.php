<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

/* ELIMINAR PRODUCTO */
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    unset($_SESSION['detalle_factura'][$_GET['index']]);
    $_SESSION['detalle_factura'] = array_values($_SESSION['detalle_factura']);
    header("Location: factura_crear.php");
    exit;
}

/* AGREGAR PRODUCTO */
$id = $_POST['id_producto'];
$cant = intval($_POST['cantidad']);

$stmt = $conexion->prepare("SELECT Descripcion_del_producto, Precio_Unitario, Stock FROM Producto WHERE idProducto=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();
$stmt->close();

if ($cant > $p['Stock']) die("Stock insuficiente");

$_SESSION['detalle_factura'][] = [
    "id" => $id,
    "descripcion" => $p['Descripcion_del_producto'],
    "precio" => $p['Precio_Unitario'],
    "cantidad" => $cant
];

header("Location: factura_crear.php");
