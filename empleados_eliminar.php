<?php
require_once "conexion.php";

if (isset($_GET['dni'])) {
    $dni = $_GET['dni'];

    $stmtTel = $conexion->prepare("DELETE FROM Empleado_Teléfono WHERE DNI_Empleado = ?");
    $stmtTel->bind_param("s", $dni);
    $stmtTel->execute();
    $stmtTel->close();

    $stmt = $conexion->prepare("DELETE FROM Empleado WHERE DNI = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->close();
}

header("Location: empleados_listar.php");
exit;
?>
