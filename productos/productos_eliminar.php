<?php
require_once __DIR__ . '/../config/conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conexion->prepare("DELETE FROM Producto WHERE idProducto = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: productos_listar.php");
exit;
?>
