<?php
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ruc = $_POST['ruc_cliente'];
    $nombre = $_POST['nombre_cliente'];
    $direccion = $_POST['direccion_del_cliente'];
    $modo = $_POST['modo'];

    $id_nombre = isset($_POST['id_nombre']) ? $_POST['id_nombre'] : null;
    $id_direccion = isset($_POST['id_direccion']) ? $_POST['id_direccion'] : null;

    if ($modo === "nuevo") {
        $stmt = $conexion->prepare("CALL sp_insert_cliente(?, ?, ?)");
        $stmt->bind_param("sss", $ruc, $nombre, $direccion);
        $stmt->execute();
        $stmt->close();

    } else {
        $stmt = $conexion->prepare("CALL sp_update_cliente(?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $ruc, $id_nombre, $id_direccion, $nombre, $direccion);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: clientes_listar.php");
exit;
?>
