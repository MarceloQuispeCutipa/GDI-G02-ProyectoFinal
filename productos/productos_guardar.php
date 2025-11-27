<?php
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modo = $_POST['modo'];
    $descripcion = $_POST['Descripcion_del_producto'];
    $unidad = $_POST['Unidad'];
    $precio = floatval($_POST['Precio_Unitario']);
    $stock = intval($_POST['Stock']);

    if ($modo === 'nuevo') {
        // Generar código tipo P001, P002, ...
        $res = $conexion->query("SELECT idProducto FROM Producto WHERE idProducto LIKE 'P%' ORDER BY idProducto DESC LIMIT 1");
        $nextNum = 1;
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $num = intval(substr($row['idProducto'], 1));
            $nextNum = $num + 1;
        }
        $codigo = 'P' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $stmt = $conexion->prepare("
            INSERT INTO Producto (idProducto, Descripcion_del_producto, Unidad, Precio_Unitario, Stock)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssdi", $codigo, $descripcion, $unidad, $precio, $stock);
        $stmt->execute();
        $stmt->close();

    } else {
        $id = $_POST['idProducto'];
        $stmt = $conexion->prepare("
            UPDATE Producto
            SET Descripcion_del_producto = ?, Unidad = ?, Precio_Unitario = ?, Stock = ?
            WHERE idProducto = ?
        ");
        $stmt->bind_param("ssd is", $descripcion, $unidad, $precio, $stock, $id);
    }
}

header("Location: productos_listar.php");
exit;
?>
