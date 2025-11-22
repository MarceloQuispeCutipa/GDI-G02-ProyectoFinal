<?php session_start(); 

require_once "conexion.php";
require_once "header.php";
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Emitir Factura</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="container">

    <h1>Emisión de Facturas</h1>

    <form action="factura_buscar_cliente.php" method="GET" class="form-busqueda">
        <label>RUC del Cliente:</label>
        <input type="text" name="ruc" required>

        <label>DNI del Empleado (Vendedor):</label>
        <input type="text" name="dni_empleado" required>

        <button type="submit">Continuar</button>
    </form>

</div>

</body>
</html>
