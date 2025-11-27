<?php
require_once __DIR__ . '/../config/conexion.php';

if (isset($_GET['ruc'])) {

    $ruc = $_GET['ruc'];

    $stmt = $conexion->prepare("CALL sp_delete_cliente(?)");
    $stmt->bind_param("s", $ruc);
    $stmt->execute();
    $stmt->close();
}

header("Location: clientes_listar.php");
exit;
?>
