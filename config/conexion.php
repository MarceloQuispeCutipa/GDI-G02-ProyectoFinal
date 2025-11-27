<?php
// Archivo: config/conexion.php
// Ajusta usuario, contraseña o host si tu entorno es diferente.

$host = "127.0.0.1";
$usuario = "root";
$password = "root";
$base_datos = "Facturacion_BD";

$conexion = new mysqli($host, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>
