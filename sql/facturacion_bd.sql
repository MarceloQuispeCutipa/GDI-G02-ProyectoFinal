-- Script: sql/facturacion_bd.sql
-- Crea la base de datos y tablas para un sistema de facturación sencillo.

DROP DATABASE IF EXISTS Facturacion_BD;
CREATE DATABASE Facturacion_BD CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Facturacion_BD;

-- =========================
-- TABLAS BÁSICAS
-- =========================

CREATE TABLE Producto(
    idProducto VARCHAR(4) PRIMARY KEY,
    Descripcion_del_producto VARCHAR(100),
    Unidad VARCHAR(20),
    Precio_Unitario DECIMAL(10,2),
    Stock INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE Empresa_Direccion(
    ID_Direccion VARCHAR(4) PRIMARY KEY,
    Direccion VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Empresa(
    RUC_Empresa VARCHAR(11) PRIMARY KEY,
    Razon_Social VARCHAR(80),
    ID_Direccion VARCHAR(4),
    FOREIGN KEY (ID_Direccion) REFERENCES Empresa_Direccion(ID_Direccion)
) ENGINE=InnoDB;

CREATE TABLE Empleado(
    DNI VARCHAR(8) PRIMARY KEY,
    Correo VARCHAR(45),
    Cargo VARCHAR(45),
    Nombre VARCHAR(45),
    RUC_Empresa VARCHAR(11),
    Empleadocol VARCHAR(45) NULL,
    FOREIGN KEY (RUC_Empresa) REFERENCES Empresa(RUC_Empresa)
) ENGINE=InnoDB;

CREATE TABLE Empleado_Telefono(
    ID_TelefonoEmpleado VARCHAR(4) PRIMARY KEY,
    DNI_Empleado VARCHAR(8),
    Numero_telefono VARCHAR(9),
    FOREIGN KEY (DNI_Empleado) REFERENCES Empleado(DNI)
) ENGINE=InnoDB;

CREATE TABLE Cliente_Nombre(
    ID_Nombre_Cliente INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nome_Cliente VARCHAR(80) -- corregiremos abajo (typo intencional para usar ALTER)
) ENGINE=InnoDB;

ALTER TABLE Cliente_Nombre CHANGE COLUMN Nome_Cliente Nombre_Cliente VARCHAR(80);

CREATE TABLE Cliente_Direccion(
    ID_DireccionCliente INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Direccion_del_Cliente VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Cliente(
    RUC_Cliente VARCHAR(11) PRIMARY KEY,
    ID_NombreCliente INT,
    ID_DireccionCliente INT,
    FOREIGN KEY (ID_NombreCliente) REFERENCES Cliente_Nombre(ID_Nombre_Cliente),
    FOREIGN KEY (ID_DireccionCliente) REFERENCES Cliente_Direccion(ID_DireccionCliente)
) ENGINE=InnoDB;

CREATE TABLE Factura(
    Numero_Factura VARCHAR(4) PRIMARY KEY,
    Tipo_de_moneda VARCHAR(4),
    Observaciones VARCHAR(100),
    Importe_total INT,
    IGV INT,
    Valor_venta INT,
    Sub_total_ventas INT,
    Fecha_de_emision DATE,
    RUC_Empresa VARCHAR(11),
    DNI_Empleado VARCHAR(8),
    FOREIGN KEY (RUC_Empresa) REFERENCES Empresa(RUC_Empresa),
    FOREIGN KEY (DNI_Empleado) REFERENCES Empleado(DNI)
) ENGINE=InnoDB;

CREATE TABLE Pedido(
    Nombre_de_la_hoja VARCHAR(4) PRIMARY KEY,
    RUC_Cliente VARCHAR(11),
    DNI_Empleado VARCHAR(8),
    Numero_de_factura VARCHAR(4),
    FOREIGN KEY (RUC_Cliente) REFERENCES Cliente(RUC_Cliente),
    FOREIGN KEY (DNI_Empleado) REFERENCES Empleado(DNI),
    FOREIGN KEY (Numero_de_factura) REFERENCES Factura(Numero_Factura)
) ENGINE=InnoDB;

CREATE TABLE Pedido_Producto(
    ID_Pedido_Producto VARCHAR(4) PRIMARY KEY,
    NombreDeLaHoja_Pedido VARCHAR(4),
    Cantidad INT,
    ID_Producto VARCHAR(4),
    FOREIGN KEY (NombreDeLaHoja_Pedido) REFERENCES Pedido(Nombre_de_la_hoja),
    FOREIGN KEY (ID_Producto) REFERENCES Producto(idProducto)
) ENGINE=InnoDB;

CREATE TABLE Precios_Pedido(
    ID_Precios INT,
    NombreDeLaHoja_Pedido VARCHAR(4),
    Costo_Total VARCHAR(45),
    Precio_sin_IGV VARCHAR(45),
    Precio_total VARCHAR(45),
    PRIMARY KEY (ID_Precios, NombreDeLaHoja_Pedido),
    FOREIGN KEY (NombreDeLaHoja_Pedido) REFERENCES Pedido(Nombre_de_la_hoja)
) ENGINE=InnoDB;

-- =========================
-- TABLAS DE AUDITORÍA
-- =========================

CREATE TABLE empleado_audit(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(8),
    correo_anterior VARCHAR(45),
    cargo_anterior VARCHAR(45),
    nombre_anterior VARCHAR(45),
    ruc_empresa_anterior VARCHAR(11),
    tipo_operacion VARCHAR(50),
    fecha_cambio DATE,
    hora_cambio TIME
) ENGINE=InnoDB;

CREATE TABLE cliente_audit(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ruc_cliente VARCHAR(11),
    id_nombre_anterior INT,
    id_direccion_anterior INT,
    tipo_operacion VARCHAR(50),
    fecha_cambio DATE,
    hora_cambio TIME
) ENGINE=InnoDB;

-- =========================
-- PROCEDIMIENTOS ALMACENADOS
-- =========================

DELIMITER $$

CREATE PROCEDURE sp_insert_cliente(
    IN p_ruc_cliente VARCHAR(11),
    IN p_nombre_cliente VARCHAR(80),
    IN p_direccion_cliente VARCHAR(100)
)
BEGIN
    DECLARE v_id_nombre INT;
    DECLARE v_id_direccion INT;

    INSERT INTO Cliente_Nombre (Nombre_Cliente) VALUES (p_nombre_cliente);
    SET v_id_nombre = LAST_INSERT_ID();

    INSERT INTO Cliente_Direccion (Direccion_del_Cliente) VALUES (p_direccion_cliente);
    SET v_id_direccion = LAST_INSERT_ID();

    INSERT INTO Cliente (RUC_Cliente, ID_NombreCliente, ID_DireccionCliente)
    VALUES (p_ruc_cliente, v_id_nombre, v_id_direccion);
END$$

CREATE PROCEDURE sp_update_cliente(
    IN p_ruc_cliente VARCHAR(11),
    IN p_id_nombre INT,
    IN p_id_direccion INT,
    IN p_nombre_cliente VARCHAR(80),
    IN p_direccion_cliente VARCHAR(100)
)
BEGIN
    UPDATE Cliente_Nombre
    SET Nombre_Cliente = p_nombre_cliente
    WHERE ID_Nombre_Cliente = p_id_nombre;

    UPDATE Cliente_Direccion
    SET Direccion_del_Cliente = p_direccion_cliente
    WHERE ID_DireccionCliente = p_id_direccion;

    UPDATE Cliente
    SET ID_NombreCliente = p_id_nombre,
        ID_DireccionCliente = p_id_direccion
    WHERE RUC_Cliente = p_ruc_cliente;
END$$

CREATE PROCEDURE sp_delete_cliente(
    IN p_ruc_cliente VARCHAR(11)
)
BEGIN
    DECLARE v_id_nombre INT;
    DECLARE v_id_direccion INT;

    SELECT ID_NombreCliente, ID_DireccionCliente
    INTO v_id_nombre, v_id_direccion
    FROM Cliente
    WHERE RUC_Cliente = p_ruc_cliente;

    DELETE FROM Cliente WHERE RUC_Cliente = p_ruc_cliente;

    IF v_id_nombre IS NOT NULL THEN
        DELETE FROM Cliente_Nombre WHERE ID_Nombre_Cliente = v_id_nombre;
    END IF;

    IF v_id_direccion IS NOT NULL THEN
        DELETE FROM Cliente_Direccion WHERE ID_DireccionCliente = v_id_direccion;
    END IF;
END$$

-- =========================
-- TRIGGERS
-- =========================

DROP TRIGGER IF EXISTS tr_empleado_before_delete$$
CREATE TRIGGER tr_empleado_before_delete
BEFORE DELETE ON Empleado
FOR EACH ROW
BEGIN
    DELETE FROM Empleado_Telefono WHERE DNI_Empleado = OLD.DNI;
    INSERT INTO empleado_audit(dni, correo_anterior, cargo_anterior, nombre_anterior, ruc_empresa_anterior,
                               tipo_operacion, fecha_cambio, hora_cambio)
    VALUES(OLD.DNI, OLD.Correo, OLD.Cargo, OLD.Nombre, OLD.RUC_Empresa,
           'ELIMINAR', CURDATE(), CURTIME());
END$$

DROP TRIGGER IF EXISTS tr_empleado_before_update$$
CREATE TRIGGER tr_empleado_before_update
BEFORE UPDATE ON Empleado
FOR EACH ROW
BEGIN
    INSERT INTO empleado_audit(dni, correo_anterior, cargo_anterior, nombre_anterior, ruc_empresa_anterior,
                               tipo_operacion, fecha_cambio, hora_cambio)
    VALUES(OLD.DNI, OLD.Correo, OLD.Cargo, OLD.Nombre, OLD.RUC_Empresa,
           'ACTUALIZAR', CURDATE(), CURTIME());
END$$

DROP TRIGGER IF EXISTS tr_cliente_before_update$$
CREATE TRIGGER tr_cliente_before_update
BEFORE UPDATE ON Cliente
FOR EACH ROW
BEGIN
    INSERT INTO cliente_audit(ruc_cliente, id_nombre_anterior, id_direccion_anterior,
                              tipo_operacion, fecha_cambio, hora_cambio)
    VALUES(OLD.RUC_Cliente, OLD.ID_NombreCliente, OLD.ID_DireccionCliente,
           'ACTUALIZAR', CURDATE(), CURTIME());
END$$

DROP TRIGGER IF EXISTS tr_cliente_before_delete$$
CREATE TRIGGER tr_cliente_before_delete
BEFORE DELETE ON Cliente
FOR EACH ROW
BEGIN
    INSERT INTO cliente_audit(ruc_cliente, id_nombre_anterior, id_direccion_anterior,
                              tipo_operacion, fecha_cambio, hora_cambio)
    VALUES(OLD.RUC_Cliente, OLD.ID_NombreCliente, OLD.ID_DireccionCliente,
           'ELIMINAR', CURDATE(), CURTIME());
END$$

DELIMITER ;

-- =========================
-- DATOS DE EJEMPLO (ANÓNIMOS)
-- =========================

-- Empresa y dirección
INSERT INTO Empresa_Direccion (ID_Direccion, Direccion) VALUES
('D001', 'Av. Los Olivos 123, Lima'),
('D002', 'Jr. Comercio 456, Arequipa');

INSERT INTO Empresa (RUC_Empresa, Razon_Social, ID_Direccion) VALUES
('20123456789', 'COMERCIAL NOVAMARKET S.A.C.', 'D001');

-- Empleados
INSERT INTO Empleado (DNI, Correo, Cargo, Nombre, RUC_Empresa, Empleadocol) VALUES
('12345678', 'carlos.perez@novamarket.com', 'Vendedor', 'Carlos Perez', '20123456789', NULL),
('87654321', 'ana.torres@novamarket.com', 'Vendedor', 'Ana Torres', '20123456789', NULL);

INSERT INTO Empleado_Telefono (ID_TelefonoEmpleado, DNI_Empleado, Numero_telefono) VALUES
('T001', '12345678', '999111222'),
('T002', '87654321', '988222333');

-- Productos de ejemplo (con stock)
INSERT INTO Producto (idProducto, Descripcion_del_producto, Unidad, Precio_Unitario, Stock) VALUES
('P001', 'Arroz extra 5kg', 'Saco', 25.00, 50),
('P002', 'Azucar rubia 1kg', 'Kilogramo', 5.00, 80),
('P003', 'Aceite vegetal 1L', 'Litro', 9.00, 60),
('P004', 'Leche evaporada 400g', 'Unidad', 4.00, 100),
('P005', 'Fideos tallarín 500g', 'Paquete', 3.00, 120),
('P006', 'Avena instantanea 1kg', 'Kilogramo', 12.00, 40),
('P007', 'Gaseosa cola 3L', 'Unidad', 10.00, 35),
('P008', 'Detergente en polvo 1kg', 'Kilogramo', 15.00, 45),
('P009', 'Jabon de lavar 200g', 'Unidad', 2.00, 200),
('P010', 'Atun en lata 170g', 'Unidad', 6.00, 90);

-- Clientes (nombres y direcciones anónimas)
INSERT INTO Cliente_Nombre (Nombre_Cliente) VALUES
('Alimentos Andinos S.A.C.'),
('Minimarket Sol Naciente'),
('Distribuidora El Mercado'),
('Supermercado Santa Ana'),
('Bodega La Esquina'),
('Comercial Los Pinos'),
('Servicios Generales Pacífico'),
('Restaurante El Sabor Criollo'),
('Panaderia Dulce Hogar'),
('Polleria Buen Gusto');

INSERT INTO Cliente_Direccion (Direccion_del_Cliente) VALUES
('Av. Principal 101, Cercado'),
('Jr. Lima 234, Miraflores'),
('Av. Los Andes 876, Cayma'),
('Jr. Grau 300, Yanahuara'),
('Calle Central 155, Sachaca'),
('Av. Industrial 567, Paucarpata'),
('Av. Progreso 222, Mariano Melgar'),
('Calle Comercio 321, JLB'),
('Av. Las Flores 400, Cerro Colorado'),
('Jr. Libertad 110, Hunter');

INSERT INTO Cliente (RUC_Cliente, ID_NombreCliente, ID_DireccionCliente) VALUES
('20400000001', 1, 1),
('20400000002', 2, 2),
('20400000003', 3, 3),
('20400000004', 4, 4),
('20400000005', 5, 5),
('20400000006', 6, 6),
('20400000007', 7, 7),
('20400000008', 8, 8),
('20400000009', 9, 9),
('20400000010', 10, 10);

