<?php 
require_once("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Clientes</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>

<h1>Listado de Clientes</h1>

<table>
    <tr>
        <th>RUC</th>
        <th>Dirección</th>
        <th>Nombre del Cliente</th>
    </tr>

<?php
$sql = "select cliente.ruc_cliente, cliente_direccion.direccion_del_cliente, cliente_nombre.nombre_cliente from cliente join cliente_direccion on cliente_direccion.id_direccioncliente = cliente.id_direccioncliente join cliente_nombre on cliente_nombre.id_nombre_cliente = cliente.id_nombrecliente";

$result = $conexion->query($sql);

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row["ruc_cliente"] . "</td>
            <td>" . $row["direccion_del_cliente"] . "</td>
            <td>" . $row["nombre_cliente"] . "</td>
          </tr>";
  }
} 
?>

</table>

</body>
</html>
