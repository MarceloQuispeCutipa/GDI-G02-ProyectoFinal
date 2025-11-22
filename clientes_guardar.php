<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ruc = $_POST['ruc_cliente'];
    $nombre = $_POST['nombre_cliente'];
    $direccion = $_POST['direccion_del_cliente'];
    $modo = $_POST['modo'];

    $id_nombre = $_POST['id_nombre'];
    $id_direccion = $_POST['id_direccion'];

    if ($modo === "nuevo") {

        $stmt = $conexion->prepare("INSERT INTO cliente_nombre (nombre_cliente) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $id_nombre = $conexion->insert_id;
        $stmt->close();

        $stmt = $conexion->prepare("INSERT INTO cliente_direccion (direccion_del_cliente) VALUES (?)");
        $stmt->bind_param("s", $direccion);
        $stmt->execute();
        $id_direccion = $conexion->insert_id;
        $stmt->close();

        $stmt = $conexion->prepare("
            INSERT INTO cliente (ruc_cliente, id_nombrecliente, id_direccioncliente)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sii", $ruc, $id_nombre, $id_direccion);
        $stmt->execute();
        $stmt->close();

    } else {

        $stmt = $conexion->prepare("
            UPDATE cliente_nombre SET nombre_cliente=? WHERE id_nombre_cliente=?
        ");
        $stmt->bind_param("si", $nombre, $id_nombre);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexion->prepare("
            UPDATE cliente_direccion SET direccion_del_cliente=? WHERE id_direccioncliente=?
        ");
        $stmt->bind_param("si", $direccion, $id_direccion);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: clientes_listar.php");
exit;
?>
