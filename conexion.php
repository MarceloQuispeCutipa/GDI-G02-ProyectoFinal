<?php
//Esta es la conexión realzada. Si es necesario cambiar los credeciales para el acceso a la base de datos, como: username y password, puede hacerlo en esta parte:
$conexion = new mysqli("localhost", "root", "root", "darisa_bd");

//Esta condicional ayuda comprobar si existe un error en la conexión o no.
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>
