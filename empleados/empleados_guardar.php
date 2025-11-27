<?php
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dni = $_POST['DNI'];
    $nombre = $_POST['Nombre'];
    $correo = $_POST['Correo'];
    $telefono = $_POST['Telefono'];
    $modo = $_POST['modo'];

    // En este ejemplo, asumimos una sola empresa con RUC fijo.
    $rucEmpresa = '20123456789';
    $cargo = 'Vendedor';

    if ($modo === "nuevo") {
        $stmt = $conexion->prepare("
            INSERT INTO Empleado (DNI, Correo, Cargo, Nombre, RUC_Empresa)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $dni, $correo, $cargo, $nombre, $rucEmpresa);
        $stmt->execute();
        $stmt->close();

        if (!empty($telefono)) {
            $stmt = $conexion->prepare("
                INSERT INTO Empleado_Telefono (ID_TelefonoEmpleado, DNI_Empleado, Numero_telefono)
                VALUES (?, ?, ?)
            ");
            $idTel = 'T' . substr($dni, -3);
            $stmt->bind_param("sss", $idTel, $dni, $telefono);
            $stmt->execute();
            $stmt->close();
        }

    } else {
        $stmt = $conexion->prepare("
            UPDATE Empleado
            SET Correo = ?, Nombre = ?
            WHERE DNI = ?
        ");
        $stmt->bind_param("sss", $correo, $nombre, $dni);
        $stmt->execute();
        $stmt->close();

        // Actualizar teléfono (simple: borrar e insertar)
        $conexion->query("DELETE FROM Empleado_Telefono WHERE DNI_Empleado = '" . $conexion->real_escape_string($dni) . "'");
        if (!empty($telefono)) {
            $stmt = $conexion->prepare("
                INSERT INTO Empleado_Telefono (ID_TelefonoEmpleado, DNI_Empleado, Numero_telefono)
                VALUES (?, ?, ?)
            ");
            $idTel = 'T' . substr($dni, -3);
            $stmt->bind_param("sss", $idTel, $dni, $telefono);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header("Location: empleados_listar.php");
exit;
?>
