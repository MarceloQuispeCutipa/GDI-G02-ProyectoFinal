<?php
require_once "../conexion.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM producto WHERE idproducto = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: productos_listar.php");
exit;
?>
