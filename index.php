<?php
require_once __DIR__ . '/views/header.php';
?>
<main class="contenedor">
    <h2>Panel principal</h2>
    <div class="tarjetas">
        <a class="tarjeta" href="clientes/clientes_listar.php">
            <h3>Clientes</h3>
            <p>Registrar, editar y eliminar clientes.</p>
        </a>
        <a class="tarjeta" href="empleados/empleados_listar.php">
            <h3>Empleados</h3>
            <p>Gestionar los datos de los empleados.</p>
        </a>
        <a class="tarjeta" href="productos/productos_listar.php">
            <h3>Productos</h3>
            <p>Gestionar catálogo, unidades y stock.</p>
        </a>
        <a class="tarjeta" href="facturas/factura_crear.php">
            <h3>Emitir factura</h3>
            <p>Seleccionar cliente, agregar productos y emitir.</p>
        </a>
        <a class="tarjeta" href="reportes/consultas.php">
            <h3>Consultas y reportes</h3>
            <p>Listados de facturas, pedidos y auditorías.</p>
        </a>
    </div>
</main>
<?php
require_once __DIR__ . '/views/footer.php';
?>
