<?php
require_once "conexion.php";
require_once "header.php";
?>

<section class="contenedor">
    <h2>Bienvenido al sistema DARISA</h2>

    <div class="tarjetas">
        <div class="tarjeta">
            <h3>Ver Productos</h3>
            <p>Gestione el catálogo de productos de abarrotes.</p>
            <a class="btn btn-crear" href="productos_listar.php">Ver productos</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Empleados</h3>
            <p>Gestione los empleados y sus datos de contacto.</p>
            <a class="btn btn-crear" href="empleados_listar.php">Ver empleados</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Clientes</h3>
            <p>Gestione los empleados y sus datos de contacto.</p>
            <a class="btn btn-crear" href="clientes_listar.php">Ver Empresas</a>
        </div>

        <div class="tarjeta">
            <h3>Emisión de Facturas</h3>
            <p>Gestione los clientes y sus datos de contacto.</p>
            <a class="btn btn-crear" href="factura_form.php">Emitir Factura</a>
        </div>

        <div class="tarjeta">
            <h3>Ver Pedidos</h3>
            <p>Gestione los clientes y sus datos de contacto.</p>
            <a class="btn btn-crear" href="buscar_pedidos.php">Ver Pedidos</a>
        </div>

</section>

<?php
require_once "footer.php";
?>
