<?php 
require_once("conexion.php"); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Empleados</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>

<h1>Empleados</h1>

<table border="1" cellpadding="8">
    <tr>
        <th>Nombre</th>
        <th>Cargo</th>
        <th>Correo</th>
        <th>Teléfono</th>
    </tr>

<?php
$sql = "select empleado.nombre, empleado.cargo, empleado.correo, empleado_telefono.telefono from empleado join empleado_telefono on empleado.dni = empleado_telefono.dni_empleado;";

$result = $conexion->query($sql);

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row["nombre"] . "</td>
            <td>" . $row["cargo"] . "</td>
            <td>" . $row["correo"] . "</td>
            <td>" . $row["telefono"] . "</td>
          </tr>";
  }
} 
?>

</table>

</body>
</html>
