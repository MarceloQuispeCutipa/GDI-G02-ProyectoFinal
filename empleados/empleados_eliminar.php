<?php
require_once __DIR__ . '/../config/conexion.php';

if (isset($_GET['dni'])) {
    $dni = $_GET['dni'];

    $stmt = $conexion->prepare("DELETE FROM Empleado WHERE DNI = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->close();
}

header("Location: empleados_listar.php");
exit;
?>
