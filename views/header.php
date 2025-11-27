<?php
// Archivo: views/header.php
// Calcula la raíz del proyecto (ej: /sistema_facturacion)
$script = trim($_SERVER['SCRIPT_NAME'], '/');
$parts = explode('/', $script);
$root = '/' . $parts[0];
$base = $root;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Facturación</title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/estilos.css">
</head>
<body>
<header>
    <div class="header-container">
        <h1>Sistema de Facturación</h1>
        <nav>
            <ul>
                <li><a href="<?php echo $base; ?>/index.php">Inicio</a></li>
                <li><a href="<?php echo $base; ?>/clientes/clientes_listar.php">Clientes</a></li>
                <li><a href="<?php echo $base; ?>/empleados/empleados_listar.php">Empleados</a></li>
                <li><a href="<?php echo $base; ?>/productos/productos_listar.php">Productos</a></li>
                <li><a href="<?php echo $base; ?>/facturas/factura_crear.php">Emitir factura</a></li>
                <li><a href="<?php echo $base; ?>/reportes/consultas.php">Consultas / Reportes</a></li>
            </ul>
        </nav>
    </div>
</header>
