<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ruc = $_POST['ruc_cliente'];
    $nombre = $_POST['nombre_cliente'];
    $direccion = $_POST['direccion_del_cliente'];
    $modo = $_POST['modo'];

    $id_nombre = isset($_POST['id_nombre']) ? $_POST['id_nombre'] : null;
    $id_direccion = isset($_POST['id_direccion']) ? $_POST['id_direccion'] : null;

    if ($modo === "nuevo") {
        // Llamar al procedimiento almacenado para insertar
        $stmt = $conexion->prepare("CALL sp_insert_cliente(?, ?, ?)");
        $stmt->bind_param("sss", $ruc, $nombre, $direccion);
        $stmt->execute();
        $stmt->close();

    } else {
        // $id_nombre y $id_direccion deben tener valores (ints)
        // Llamar al procedimiento almacenado para actualizar
        $stmt = $conexion->prepare("CALL sp_update_cliente(?, ?, ?, ?, ?)");
        // parametros: p_ruc, p_id_nombre, p_id_direccion, p_nombre, p_direccion
        $stmt->bind_param("siiss", $ruc, $id_nombre, $id_direccion, $nombre, $direccion);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: clientes_listar.php");
exit;
?>
