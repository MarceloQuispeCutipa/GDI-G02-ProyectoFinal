# Sistema de Facturación (Versión mejorada)

Proyecto organizado con:
- Gestión de clientes
- Gestión de empleados
- Gestión de productos (con stock y unidad)
- Emisión de facturas
- Descuento de stock al facturar
- Reportes de facturas y auditorías

## Estructura de carpetas

- `config/`
  - `conexion.php`
- `views/`
  - `header.php`, `footer.php`
- `assets/css/`
  - `estilos.css`
- `clientes/`
  - `clientes_listar.php`
  - `clientes_form.php`
  - `clientes_guardar.php`
  - `clientes_eliminar.php`
- `empleados/`
  - `empleados_listar.php`
  - `empleados_form.php`
  - `empleados_guardar.php`
  - `empleados_eliminar.php`
- `productos/`
  - `productos_listar.php`
  - `productos_form.php`
  - `productos_guardar.php`
  - `productos_eliminar.php`
- `facturas/`
  - `factura_crear.php`
  - `factura_agregar_producto.php`
  - `factura_generar.php`
  - `factura.php`
- `reportes/`
  - `consultas.php`
- `sql/`
  - `facturacion_bd.sql`

## Pasos básicos

1. Importar `sql/facturacion_bd.sql` en MySQL.
2. Ajustar usuario/clave en `config/conexion.php` si es necesario.
3. Colocar la carpeta `sistema_facturacion_v2` en el directorio del servidor web:
   - Linux: `/var/www/html/sistema_facturacion_v2`
4. Abrir en el navegador:
   - `http://localhost/sistema_facturacion_v2/index.php`

## Flujo de uso

- Gestionar clientes y empleados desde el menú.
- Registrar productos con:
  - Código autogenerado (P001, P002, ...).
  - Unidad (unidad, paquete, kilogramo, etc.).
  - Stock disponible.
- En **Emitir factura**:
  - Escribir RUC del cliente (sugerido por lista).
  - Indicar DNI del empleado.
  - Agregar productos indicando cantidad (se valida contra stock).
  - Ver detalle con:
    - Cantidad
    - Precio unitario (sin IGV)
    - Total sin IGV
    - Subtotal + IGV + Total con IGV
  - Emitir factura:
    - Se registran Factura, Pedido, Pedido_Producto y Precios_Pedido.
    - Se descuenta el stock de cada producto.
    - Se muestra la factura lista para imprimir o guardar como PDF (usando la opción de impresión del navegador).

- En **Consultas / Reportes**:
  - Listado de facturas tipo SUNAT (N° factura, fecha, RUC, cliente, montos).
  - Pedidos con detalle de productos.
  - Auditoría de empleados y clientes.

