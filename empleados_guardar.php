<?php
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dni = $_POST['DNI'];
    $nombre = $_POST['Nombre'];
    $cargo = $_POST['Cargo'];
    $correo = $_POST['Correo'];
    $rucEmpresa = $_POST['RUC_Empresa'];
    $modo = $_POST['modo'];

    if ($modo === "nuevo") {
        $stmt = $conexion->prepare("
            INSERT INTO Empleado (DNI, Correo, Cargo, Nombre, RUC_Empresa)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $dni, $correo, $cargo, $nombre, $rucEmpresa);
        $stmt->execute();
        $stmt->close();

    } else {
        $stmt = $conexion->prepare("
            UPDATE Empleado
            SET Correo = ?, Cargo = ?, Nombre = ?, RUC_Empresa = ?
            WHERE DNI = ?
        ");
        $stmt->bind_param("sssss", $correo, $cargo, $nombre, $rucEmpresa, $dni);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: empleados_listar.php");
exit;
?>
