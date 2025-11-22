<?php
require_once "conexion.php";
require_once "header.php";
?>

<section class="contenedor">
    <h2>Bienvenido al sistema DARISA</h2>

    <div class="tarjetas">
        <div class="tarjeta">
            <h3>Ver Productos</h3>
            <p>Aqui puede ver y gestionar los productos.</p>
            <a class="btn btn-crear" style="margin-top: 20px"  href="productos_listar.php">Ver productos</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Empleados</h3>
            <p>Aqui puede ver los empleados y gestionarlos.</p>
            <a class="btn btn-crear" style="margin-top: 20px" href="empleados_listar.php">Ver empleados</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Clientes</h3>
            <p>Aqui puede gestionar los clientes o agregar un nuevo cliente.</p>
            <a class="btn btn-crear" style="margin-top: 20px" href="clientes_listar.php">Ver Empresas</a>
        </div>

        <div class="tarjeta">
            <h3>Emisión de Facturas</h3>
            <p>Aqui puede emitir nuevas facturas.</p>
            <a class="btn btn-crear" style="margin-top: 20px" href="factura_crear.php">Emitir Factura</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Pedidos</h3>
            <p>Aqui puede ver y gestionar los pedidos.</p>
            <a class="btn btn-crear" style="margin-top: 20px" href="pedidos.php">Ver Pedidos</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Consultas</h3>
            <p>Aqui puede ver y realizar consultas.</p>
            <a class="btn btn-crear" style="margin-top: 20px" href="consultas.php">Ver Consultas</a>
        </div>

</section>

<?php
require_once "footer.php";
?>
