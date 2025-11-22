<?php
require_once "conexion.php";

if (isset($_GET['ruc'])) {

    $ruc = $_GET['ruc'];

    $stmt = $conexion->prepare("SELECT id_nombrecliente, id_direccioncliente FROM cliente WHERE ruc_cliente=?");
    $stmt->bind_param("s", $ruc);
    $stmt->execute();
    $stmt->bind_result($id_nombre, $id_direccion);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM cliente WHERE ruc_cliente=?");
    $stmt->bind_param("s", $ruc);
    $stmt->execute();
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM cliente_nombre WHERE id_nombre_cliente=?");
    $stmt->bind_param("i", $id_nombre);
    $stmt->execute();
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM cliente_direccion WHERE id_direccioncliente=?");
    $stmt->bind_param("i", $id_direccion);
    $stmt->execute();
    $stmt->close();
}

header("Location: clientes_listar.php");
exit;
?>
