-- crea BD DARISA
CREATE DATABASE IF NOT EXISTS DARISA;
USE DARISA;

-- TABLAS

CREATE TABLE producto(
    idproducto VARCHAR(4) PRIMARY KEY,
    descripcion_del_producto TEXT,
    unidad VARCHAR(2),
    precio_unitario INT
) ENGINE=InnoDB;

CREATE TABLE empresa(
    ruc_empresa VARCHAR(11),
    razon_social VARCHAR(50),
    PRIMARY KEY(ruc_empresa)
) ENGINE=InnoDB;

CREATE TABLE empresa_direccion(
    id_direccion VARCHAR(4),
    direccion VARCHAR(100),
    ruc_empresa VARCHAR(11),
    PRIMARY KEY(id_direccion),
    FOREIGN KEY(ruc_empresa) REFERENCES empresa(ruc_empresa)
) ENGINE=InnoDB;

CREATE TABLE empleado(
    dni VARCHAR(8),
    correo VARCHAR(45),
    cargo VARCHAR(45),
    nombre VARCHAR(45),
    ruc_empresa VARCHAR(11),
    empleadocol VARCHAR(45),
    PRIMARY KEY(dni),
    FOREIGN KEY(ruc_empresa) REFERENCES empresa(ruc_empresa)
) ENGINE=InnoDB;

CREATE TABLE empleado_telefono(
    id_telefono VARCHAR(4),
    dni_empleado VARCHAR(8),
    telefono VARCHAR(9),
    PRIMARY KEY (id_telefono),
    FOREIGN KEY (dni_empleado) REFERENCES empleado(dni)
) ENGINE=InnoDB;

CREATE TABLE factura(
    numero_factura VARCHAR(4),
    tipo_de_moneda VARCHAR(4),
    observaciones TEXT,
    importe_total INT,
    igv INT,
    valor_venta INT,
    sub_total_ventas INT,
    ruc_empresa VARCHAR(11),
    dni_empleado VARCHAR(8),
    PRIMARY KEY(numero_factura),
    FOREIGN KEY(ruc_empresa) REFERENCES empresa(ruc_empresa),
    FOREIGN KEY(dni_empleado) REFERENCES empleado(dni)
) ENGINE=InnoDB;

CREATE TABLE pedido(
    nombre_de_la_hoja VARCHAR(30),
    fecha_de_emision DATE,
    ruc_cliente VARCHAR(11),
    dni_empleado VARCHAR(8),
    numero_de_factura VARCHAR(4),
    PRIMARY KEY(nombre_de_la_hoja),
    FOREIGN KEY(dni_empleado) REFERENCES empleado(dni),
    FOREIGN KEY(numero_de_factura) REFERENCES factura (numero_factura)
) ENGINE=InnoDB;

CREATE TABLE pedido_producto(
    id_pedido_producto VARCHAR(4),
    id_producto VARCHAR(4),
    nombredelahoja_pedido VARCHAR(45),
    cantidad INT,
    PRIMARY KEY (id_pedido_producto),
    FOREIGN KEY (id_producto) REFERENCES producto(idproducto),
    FOREIGN KEY (nombredelahoja_pedido) REFERENCES pedido(nombre_de_la_hoja)
) ENGINE=InnoDB;


-- ÍNDICES

CREATE FULLTEXT INDEX IDX_Descripcion
ON producto (descripcion_del_producto);

CREATE INDEX IDX_dniempleado_pedido ON pedido (dni_empleado);
CREATE INDEX IDX_numerofactura ON pedido (numero_de_factura);

CREATE INDEX IDX_rucempresa_factura ON factura (ruc_empresa);
CREATE INDEX IDX_dniempleado_factura ON factura (dni_empleado);

CREATE INDEX IDX_direccionempresa ON empresa_direccion (id_direccion);
CREATE FULLTEXT INDEX IDX_Direccion_Text ON empresa_direccion (direccion);

CREATE INDEX IDX_rucempresa_Empleado ON empleado (ruc_empresa);
CREATE INDEX IDX_dniempleado_Tel ON empleado_telefono (dni_empleado);

CREATE INDEX IDX_Producto ON pedido_producto (id_producto);


-- INSERTS

-- EMPRESA
INSERT INTO empresa VALUES
('20100000001','Abarrotes DARISA'),
('20100000002','Distribuidora San José'),
('20100000003','Comercial El Progreso'),
('20100000004','Mayorista Pacífico'),
('20100000005','Alimentos La Merced'),
('20100000006','Mercado Central SAC'),
('20100000007','Bodegas Unidas'),
('20100000008','Importadora Andina'),
('20100000009','Granos del Sur'),
('20100000010','Multiservicios Barrio');

-- EMPRESA_DIRECCION
INSERT INTO empresa_direccion VALUES
('D001','Av. Arequipa 101','20100000001'),
('D002','Jr. Piura 234','20100000002'),
('D003','Av. Los Productores 876','20100000003'),
('D004','Jr. Grau 300','20100000004'),
('D005','Calle Central 155','20100000005'),
('D006','Av. Industrial 567','20100000006'),
('D007','Av. Progreso 222','20100000007'),
('D008','Calle Comercio 321','20100000008'),
('D009','Av. Las Flores 400','20100000009'),
('D010','Jr. Libertad 110','20100000010');

-- PRODUCTO (abarrotes)
INSERT INTO producto VALUES
('P001','Arroz corriente 1kg','u',4),
('P002','Azúcar rubia 1kg','u',4),
('P003','Aceite vegetal 1L','u',10),
('P004','Leche evaporada 400g','u',4),
('P005','Fideo tallarín 500g','u',3),
('P006','Atún en lata 170g','u',6),
('P007','Galleta surtida paquete','u',5),
('P008','Detergente bolsa 1kg','u',8),
('P009','Papel higiénico x4','u',7),
('P010','Gaseosa 1.5L','u',8);

-- EMPLEADO
INSERT INTO empleado VALUES
('80000001','caja1@darisa.com','Cajera','Ana Torres','20100000001','Caja'),
('80000002','ventas1@darisa.com','Vendedor','Carlos Ruiz','20100000001','Ventas'),
('80000003','almacen@darisa.com','Almacenero','José Pérez','20100000001','Almacén'),
('80000004','adm@darisa.com','Administrador','Lucía Ramos','20100000001','Administración'),
('80000005','reparto@darisa.com','Repartidor','Mario Gutiérrez','20100000001','Reparto'),
('80000006','caja2@darisa.com','Cajera','Elena Soto','20100000001','Caja'),
('80000007','ventas2@darisa.com','Vendedor','Pedro Alarcón','20100000001','Ventas'),
('80000008','conta@darisa.com','Contador','Sofía Morales','20100000001','Contabilidad'),
('80000009','sistemas@darisa.com','Soporte','Raúl López','20100000001','Sistemas'),
('80000010','asistente@darisa.com','Asistente','Paula García','20100000001','Apoyo');

-- EMPLEADO_TELEFONO
INSERT INTO empleado_telefono VALUES
('T001','80000001','987654321'),
('T002','80000002','912345678'),
('T003','80000003','923456789'),
('T004','80000004','934567890'),
('T005','80000005','945678901'),
('T006','80000006','956789012'),
('T007','80000007','967890123'),
('T008','80000008','978901234'),
('T009','80000009','989012345'),
('T010','80000010','900123456');

-- FACTURA (ventas de abarrotes)
INSERT INTO factura VALUES
('F001','PEN','Venta mostrador - básicos',120,22,98,98,'20100000001','80000001'),
('F002','PEN','Venta productos limpieza',180,32,148,148,'20100000001','80000002'),
('F003','PEN','Venta enlatados y leche',95,17,78,78,'20100000001','80000006'),
('F004','PEN','Venta gaseosas y galletas',140,25,115,115,'20100000001','80000001'),
('F005','PEN','Venta arroz y azúcar',210,38,172,172,'20100000001','80000002'),
('F006','PEN','Pedido minorista',160,29,131,131,'20100000001','80000007'),
('F007','PEN','Venta detergentes',90,16,74,74,'20100000001','80000003'),
('F008','PEN','Venta surtida',250,45,205,205,'20100000001','80000004'),
('F009','PEN','Venta bebidas',130,23,107,107,'20100000001','80000001'),
('F010','PEN','Venta abarrotes varios',175,31,144,144,'20100000001','80000005');

-- PEDIDO
INSERT INTO pedido VALUES
('Pedido001','2025-10-01','10400000001','80000001','F001'),
('Pedido002','2025-10-02','10400000002','80000002','F002'),
('Pedido003','2025-10-03','10400000003','80000006','F003'),
('Pedido004','2025-10-04','10400000004','80000001','F004'),
('Pedido005','2025-10-05','10400000005','80000002','F005'),
('Pedido006','2025-10-06','10400000006','80000007','F006'),
('Pedido007','2025-10-07','10400000007','80000003','F007'),
('Pedido008','2025-10-08','10400000008','80000004','F008'),
('Pedido009','2025-10-09','10400000009','80000001','F009'),
('Pedido010','2025-10-10','10400000010','80000005','F010');

-- PEDIDO_PRODUCTO (venta)
INSERT INTO pedido_producto VALUES
('PP01','P001','Pedido001',5),  -- 5 kg arroz
('PP02','P002','Pedido002',4),  -- 4 kg azúcar
('PP03','P003','Pedido003',3),  -- 3 botellas aceite
('PP04','P010','Pedido004',6),  -- 6 gaseosas
('PP05','P004','Pedido005',8),  -- 8 leches
('PP06','P006','Pedido006',10), -- 10 atunes
('PP07','P008','Pedido007',2),  -- 2 detergentes
('PP08','P009','Pedido008',5),  -- 5 packs papel higiénico
('PP09','P005','Pedido009',12), 
('PP10','P007','Pedido010',7);  


-- 10 consultas

-- 1. empresas con su dirección
SELECT e.ruc_empresa, e.razon_social, ed.direccion
FROM empresa e
JOIN empresa_direccion ed ON e.ruc_empresa = ed.ruc_empresa;

-- 2. empleados con teléfonos
SELECT emp.nombre, emp.cargo, emp.correo, tel.telefono
FROM empleado emp
JOIN empleado_telefono tel ON emp.dni = tel.dni_empleado;

-- 3. pedidos después de la quincena
SELECT p.nombre_de_la_hoja, p.fecha_de_emision, p.dni_empleado
FROM pedido p
WHERE p.fecha_de_emision > '2025-10-05';

-- 4. producto y cantidad vendida en cada pedido
SELECT p.nombre_de_la_hoja,
       pr.descripcion_del_producto,
       pp.cantidad
FROM pedido p
JOIN pedido_producto pp ON p.nombre_de_la_hoja = pp.nombredelahoja_pedido
JOIN producto pr ON pp.id_producto = pr.idproducto;

-- 5. pedidos con su factura y empresa emisora
SELECT p.nombre_de_la_hoja,
       f.numero_factura,
       e.razon_social,
       f.importe_total
FROM pedido p
JOIN factura f ON p.numero_de_factura = f.numero_factura
JOIN empresa e ON f.ruc_empresa = e.ruc_empresa;

-- 6. total de ventas por empresa
SELECT e.razon_social,
       SUM(f.importe_total) AS total_ventas
FROM factura f
JOIN empresa e ON f.ruc_empresa = e.ruc_empresa
GROUP BY e.razon_social
ORDER BY total_ventas DESC;

-- 7. promedio de facturas
SELECT AVG(importe_total) AS promedio_facturas
FROM factura;

-- 8. pedidos con el empleado que lo atendió
SELECT p.nombre_de_la_hoja,
       emp.nombre AS empleado
FROM pedido p
JOIN empleado emp ON p.dni_empleado = emp.dni;

-- 9. total de pedidos por fecha
SELECT p.fecha_de_emision,
       COUNT(*) AS total_pedidos
FROM pedido p
GROUP BY p.fecha_de_emision
ORDER BY p.fecha_de_emision DESC;

-- 10. facturas atendidas por empleado
SELECT f.numero_factura,
       emp.nombre,
       f.importe_total
FROM factura f
JOIN empleado emp ON f.dni_empleado = emp.dni;



USE DARISA;
DELIMITER $$

-- Procedimiento: listar productos
CREATE PROCEDURE sp_listar_productos()
BEGIN
    SELECT idproducto,
           descripcion_del_producto,
           unidad,
           precio_unitario
    FROM producto
    ORDER BY idproducto;
END$$

-- Procedimiento: insertar producto
CREATE PROCEDURE sp_insertar_producto(
    IN p_id VARCHAR(4),
    IN p_desc TEXT,
    IN p_unidad VARCHAR(2),
    IN p_precio INT
)
BEGIN
    INSERT INTO producto(idproducto, descripcion_del_producto, unidad, precio_unitario)
    VALUES (p_id, p_desc, p_unidad, p_precio);
END$$

-- Procedimiento: actualizar producto
CREATE PROCEDURE sp_actualizar_producto(
    IN p_id VARCHAR(4),
    IN p_desc TEXT,
    IN p_unidad VARCHAR(2),
    IN p_precio INT
)
BEGIN
    UPDATE producto
    SET descripcion_del_producto = p_desc,
        unidad = p_unidad,
        precio_unitario = p_precio
    WHERE idproducto = p_id;
END$$

-- Procedimiento: eliminar producto
CREATE PROCEDURE sp_eliminar_producto(IN p_id VARCHAR(4))
BEGIN
    DELETE FROM pedido_producto WHERE id_producto = p_id;
    DELETE FROM producto WHERE idproducto = p_id;
END$$

-- Función: calcular total de un pedido 
CREATE FUNCTION fn_total_pedido(p_nombre_hoja VARCHAR(30))
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT SUM(pp.cantidad * pr.precio_unitario)
    INTO total
    FROM pedido_producto pp
    JOIN producto pr ON pp.id_producto = pr.idproducto
    WHERE pp.nombredelahoja_pedido = p_nombre_hoja;
    RETURN IFNULL(total, 0);
END$$

DELIMITER ;
