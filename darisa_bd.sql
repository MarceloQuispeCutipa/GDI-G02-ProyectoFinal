CREATE DATABASE Darisa_BD;
USE Darisa_BD;

-- Producto
CREATE TABLE Producto (
  idProducto VARCHAR(4) NOT NULL,
  Descripcion_del_producto TEXT(50) NULL,
  Unidad VARCHAR(2) NULL,
  Precio_Unitario INT NULL,
  PRIMARY KEY (idProducto)
) ENGINE = InnoDB;

-- Empresa_Direccion
CREATE TABLE Empresa_Direccion (
  ID_Direccion VARCHAR(4) NOT NULL,
  Direccion VARCHAR(100) NULL,
  PRIMARY KEY (ID_Direccion)
) ENGINE = InnoDB;

-- Empresa
CREATE TABLE Empresa (
  RUC_Empresa VARCHAR(11) NOT NULL,
  Razon_Social VARCHAR(50) NULL,
  ID_Direccion VARCHAR(4) NULL,
  PRIMARY KEY (RUC_Empresa),
  FOREIGN KEY (ID_Direccion) REFERENCES Empresa_Direccion(ID_Direccion)
) ENGINE = InnoDB;

-- Empleado
CREATE TABLE Empleado (
  DNI VARCHAR(8) NOT NULL,
  Correo VARCHAR(45) NULL,
  Cargo VARCHAR(45) NULL,
  Nombre VARCHAR(45) NULL,
  RUC_Empresa VARCHAR(11) NULL,
  PRIMARY KEY (DNI),
  FOREIGN KEY (RUC_Empresa) REFERENCES Empresa(RUC_Empresa)
) ENGINE = InnoDB;

-- Factura
CREATE TABLE Factura (
  Numero_Factura VARCHAR(4) NOT NULL,
  Tipo_de_moneda VARCHAR(4) NULL,
  Observaciones TEXT(100) NULL,
  Importe_total INT NULL,
  IGV INT NULL,
  Valor_venta INT NULL,
  Sub_total_ventas INT NULL,
  Fecha_de_emision DATE NULL,
  RUC_Empresa VARCHAR(11) NOT NULL,
  DNI_Empleado VARCHAR(8) NOT NULL,
  PRIMARY KEY (Numero_Factura),
  FOREIGN KEY (RUC_Empresa) REFERENCES Empresa(RUC_Empresa),
  FOREIGN KEY (DNI_Empleado) REFERENCES Empleado(DNI)
) ENGINE = InnoDB;

-- Cliente_Direccion
CREATE TABLE Cliente_Direccion (
  ID_DireccionCliente INT NOT NULL AUTO_INCREMENT,
  Direccion_del_Cliente VARCHAR(100) NULL,
  PRIMARY KEY (ID_DireccionCliente)
) ENGINE = InnoDB;

-- Cliente_Nombre
CREATE TABLE Cliente_Nombre (
  ID_Nombre_Cliente INT NOT NULL AUTO_INCREMENT,
  Nombre_Cliente VARCHAR(45) NULL,
  PRIMARY KEY (ID_Nombre_Cliente)
) ENGINE = InnoDB;

-- Cliente
CREATE TABLE Cliente (
  RUC_Cliente VARCHAR(11) NOT NULL,
  ID_NombreCliente INT NULL,
  ID_DireccionCliente INT NULL,
  PRIMARY KEY (RUC_Cliente),
  FOREIGN KEY (ID_DireccionCliente) REFERENCES Cliente_Direccion(ID_DireccionCliente),
  FOREIGN KEY (ID_NombreCliente) REFERENCES Cliente_Nombre(ID_Nombre_Cliente)
) ENGINE = InnoDB;

-- Pedido
CREATE TABLE Pedido (
  Nombre_de_la_hoja VARCHAR(30) NOT NULL,
  RUC_Cliente VARCHAR(11) NOT NULL,
  DNI_Empleado VARCHAR(8) NOT NULL,
  Numero_de_factura VARCHAR(4) NOT NULL,
  PRIMARY KEY (Nombre_de_la_hoja),
  FOREIGN KEY (DNI_Empleado) REFERENCES Empleado(DNI),
  FOREIGN KEY (Numero_de_factura) REFERENCES Factura(Numero_Factura),
  FOREIGN KEY (RUC_Cliente) REFERENCES Cliente(RUC_Cliente)
) ENGINE = InnoDB;

-- Precios_Pedido
CREATE TABLE Precios_Pedido (
  ID_Precios INT NOT NULL,
  NombreDeLaHoja_Pedido VARCHAR(45) NOT NULL,
  Costo_Total VARCHAR(45) NULL,
  Precio_sin_IGV VARCHAR(45) NULL,
  Precio_total VARCHAR(45) NULL,
  PRIMARY KEY (ID_Precios, NombreDeLaHoja_Pedido),
  FOREIGN KEY (NombreDeLaHoja_Pedido) REFERENCES Pedido(Nombre_de_la_hoja)
) ENGINE = InnoDB;

-- Empleado_Telefono
CREATE TABLE Empleado_Telefono (
  ID_Telefono VARCHAR(4) NOT NULL,
  DNI_Empleado VARCHAR(8) NULL,
  Telefono VARCHAR(9) NULL,
  PRIMARY KEY (ID_Telefono),
  FOREIGN KEY (DNI_Empleado) REFERENCES Empleado(DNI)
) ENGINE = InnoDB;

-- Pedido_Producto
CREATE TABLE Pedido_Producto (
  ID_Pedido_Producto VARCHAR(4) NOT NULL,
  NombreDeLaHoja_Pedido VARCHAR(45) NOT NULL,
  Cantidad INT NULL,
  ID_Producto VARCHAR(4) NULL,
  PRIMARY KEY (ID_Pedido_Producto, NombreDeLaHoja_Pedido),
  FOREIGN KEY (ID_Producto) REFERENCES Producto(idProducto),
  FOREIGN KEY (NombreDeLaHoja_Pedido) REFERENCES Pedido(Nombre_de_la_hoja)
) ENGINE = InnoDB;

-- TABLAS PARA LOS TRIGGERS

-- Logs para el cliente
CREATE TABLE IF NOT EXISTS cliente_audit (
  audit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  action_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
  ruc_cliente VARCHAR(11),
  id_nombrecliente INT,
  id_direccioncliente INT,
  action_user VARCHAR(100) NULL,
  action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  details TEXT NULL
) ENGINE=InnoDB;

-- Logs para el empleado

CREATE TABLE IF NOT EXISTS empleado_audit (
  audit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  action_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
  dni VARCHAR(8) NULL,
  nombre_old VARCHAR(100) NULL,
  correo_old VARCHAR(100) NULL,
  cargo_old VARCHAR(100) NULL,
  ruc_empresa_old VARCHAR(11) NULL,
  nombre_new VARCHAR(100) NULL,
  correo_new VARCHAR(100) NULL,
  cargo_new VARCHAR(100) NULL,
  ruc_empresa_new VARCHAR(11) NULL,
  action_user VARCHAR(100) NULL,
  action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  details TEXT NULL
) ENGINE=InnoDB;

-- PROCESOS

-- Insertar clientes
DELIMITER $$
CREATE PROCEDURE sp_insert_cliente(
  IN p_ruc VARCHAR(11),
  IN p_nombre VARCHAR(100),
  IN p_direccion VARCHAR(200)
)
BEGIN
  DECLARE v_id_nombre INT;
  DECLARE v_id_direccion INT;

  INSERT INTO Cliente_Nombre (nombre_cliente) VALUES (p_nombre);
  SET v_id_nombre = LAST_INSERT_ID();

  INSERT INTO Cliente_Direccion (direccion_del_cliente) VALUES (p_direccion);
  SET v_id_direccion = LAST_INSERT_ID();

  INSERT INTO Cliente (RUC_Cliente, ID_NombreCliente, ID_DireccionCliente)
    VALUES (p_ruc, v_id_nombre, v_id_direccion);

  INSERT INTO cliente_audit (action_type, ruc_cliente, id_nombrecliente, id_direccioncliente, details)
    VALUES ('INSERT', p_ruc, v_id_nombre, v_id_direccion, CONCAT('Inserted cliente ', p_ruc));
END $$
DELIMITER ;

-- Actualizar clientes
DELIMITER $$
CREATE PROCEDURE sp_update_cliente(
  IN p_ruc VARCHAR(11),
  IN p_id_nombre INT,
  IN p_id_direccion INT,
  IN p_nombre VARCHAR(100),
  IN p_direccion VARCHAR(200)
)
BEGIN
  UPDATE Cliente_Nombre SET nombre_cliente = p_nombre WHERE id_nombre_cliente = p_id_nombre;
  UPDATE Cliente_Direccion SET direccion_del_cliente = p_direccion WHERE id_direccioncliente = p_id_direccion;

  INSERT INTO cliente_audit (action_type, ruc_cliente, id_nombrecliente, id_direccioncliente, details)
    VALUES ('UPDATE', p_ruc, p_id_nombre, p_id_direccion, CONCAT('Updated cliente ', p_ruc));
END $$
DELIMITER ;

-- Eliminar Clientes

DELIMITER $$
CREATE PROCEDURE sp_delete_cliente(
  IN p_ruc VARCHAR(11)
)
BEGIN
  DECLARE v_id_nombre INT;
  DECLARE v_id_direccion INT;

  SELECT ID_NombreCliente, ID_DireccionCliente INTO v_id_nombre, v_id_direccion
    FROM Cliente WHERE RUC_Cliente = p_ruc LIMIT 1;

  DELETE FROM Cliente WHERE RUC_Cliente = p_ruc;

  DELETE FROM Cliente_Nombre WHERE id_nombre_cliente = v_id_nombre;
  DELETE FROM Cliente_Direccion WHERE id_direccioncliente = v_id_direccion;

  INSERT INTO cliente_audit (action_type, ruc_cliente, id_nombrecliente, id_direccioncliente, details)
    VALUES ('DELETE', p_ruc, v_id_nombre, v_id_direccion, CONCAT('Deleted cliente ', p_ruc));
END $$
DELIMITER ;

-- insertar empleados

DELIMITER //
CREATE PROCEDURE sp_empleado_insertar(
    IN pDNI VARCHAR(8),
    IN pCorreo VARCHAR(45),
    IN pCargo VARCHAR(45),
    IN pNombre VARCHAR(45),
    IN pRUC VARCHAR(11)
)
BEGIN
    INSERT INTO Empleado (DNI, Correo, Cargo, Nombre, RUC_Empresa)
    VALUES (pDNI, pCorreo, pCargo, pNombre, pRUC);
END //
DELIMITER ;

-- Actualizar empleados

DELIMITER //
CREATE PROCEDURE sp_empleado_actualizar(
    IN pDNI VARCHAR(8),
    IN pCorreo VARCHAR(45),
    IN pCargo VARCHAR(45),
    IN pNombre VARCHAR(45),
    IN pRUC VARCHAR(11)
)
BEGIN
    UPDATE Empleado
    SET Correo = pCorreo,
        Cargo = pCargo,
        Nombre = pNombre,
        RUC_Empresa = pRUC
    WHERE DNI = pDNI;
END //
DELIMITER ;


-- Listar empleados

DELIMITER //
CREATE PROCEDURE sp_empleado_listar()
BEGIN
    SELECT * FROM Empleado;
END //
DELIMITER ;

-- eliminar empleado

DELIMITER //
CREATE PROCEDURE sp_empleado_eliminar(IN pDNI VARCHAR(8))
BEGIN
    DELETE FROM Empleado WHERE DNI = pDNI;
END //
DELIMITER ;


-- TRIGGERS

-- Luego de una inserciones en clientes
DELIMITER $$
CREATE TRIGGER trg_cliente_after_insert
AFTER INSERT ON Cliente
FOR EACH ROW
BEGIN
  INSERT INTO cliente_audit (action_type, ruc_cliente, id_nombrecliente, id_direccioncliente, details)
    VALUES ('INSERT', NEW.RUC_Cliente, NEW.ID_NombreCliente, NEW.ID_DireccionCliente, 'Trigger: after insert');
END $$
DELIMITER ;

-- Luego de actualizar un cliente

DELIMITER $$
CREATE TRIGGER trg_cliente_after_update
AFTER UPDATE ON Cliente
FOR EACH ROW
BEGIN
  INSERT INTO cliente_audit (action_type, ruc_cliente, id_nombrecliente, id_direccioncliente, details)
    VALUES ('UPDATE', NEW.RUC_Cliente, NEW.ID_NombreCliente, NEW.ID_DireccionCliente, 'Trigger: after update');
END $$
DELIMITER ;

-- Luego de borrar un cliente

DELIMITER $$
CREATE TRIGGER trg_cliente_after_delete
AFTER DELETE ON Cliente
FOR EACH ROW
BEGIN
  INSERT INTO cliente_audit (action_type, ruc_cliente, id_nombrecliente, id_direccioncliente, details)
    VALUES ('DELETE', OLD.RUC_Cliente, OLD.ID_NombreCliente, OLD.ID_DireccionCliente, 'Trigger: after delete');
END $$
DELIMITER ;

-- Antes de borrar un empleado

DELIMITER $$
DROP TRIGGER IF EXISTS tr_empleado_before_delete$$
CREATE TRIGGER tr_empleado_before_delete
BEFORE DELETE ON Empleado
FOR EACH ROW
BEGIN
    DELETE FROM Empleado_Telefono WHERE DNI_Empleado = OLD.DNI;
END$$
DELIMITER ;

-- despues de insertar un empleado

DELIMITER $$
DROP TRIGGER IF EXISTS tr_empleado_after_insert$$
CREATE TRIGGER tr_empleado_after_insert
AFTER INSERT ON Empleado
FOR EACH ROW
BEGIN
    INSERT INTO empleado_audit (
      action_type, dni,
      nombre_new, correo_new, cargo_new, ruc_empresa_new,
      details
    ) VALUES (
      'INSERT', NEW.DNI,
      NEW.Nombre, NEW.Correo, NEW.Cargo, NEW.RUC_Empresa,
      CONCAT('Inserted empleado ', NEW.DNI)
    );
END$$
DELIMITER ;

-- despues de actualizar un empleado

DELIMITER $$
DROP TRIGGER IF EXISTS tr_empleado_after_update$$
CREATE TRIGGER tr_empleado_after_update
AFTER UPDATE ON Empleado
FOR EACH ROW
BEGIN
    INSERT INTO empleado_audit (
      action_type, dni,
      nombre_old, correo_old, cargo_old, ruc_empresa_old,
      nombre_new, correo_new, cargo_new, ruc_empresa_new,
      details
    ) VALUES (
      'UPDATE', NEW.DNI,
      OLD.Nombre, OLD.Correo, OLD.Cargo, OLD.RUC_Empresa,
      NEW.Nombre, NEW.Correo, NEW.Cargo, NEW.RUC_Empresa,
      CONCAT('Updated empleado ', NEW.DNI)
    );
END$$
DELIMITER ;

-- despues de eliminar un empleado

DELIMITER $$
DROP TRIGGER IF EXISTS tr_empleado_after_delete$$
CREATE TRIGGER tr_empleado_after_delete
AFTER DELETE ON Empleado
FOR EACH ROW
BEGIN
    INSERT INTO empleado_audit (
      action_type, dni,
      nombre_old, correo_old, cargo_old, ruc_empresa_old,
      details
    ) VALUES (
      'DELETE', OLD.DNI,
      OLD.Nombre, OLD.Correo, OLD.Cargo, OLD.RUC_Empresa,
      CONCAT('Deleted empleado ', OLD.DNI)
    );
END$$
DELIMITER ;


-- INSERCION DE DATOS

INSERT INTO Empresa_Direccion VALUES
('D001','Mercado Productores');

INSERT INTO Empresa VALUES
('20611646306','COMERCIAL DARISA','D001');

INSERT INTO Empleado VALUES
('23280984','walsu.walsu@gmail.com','Vendedor','Walter Sucasaire','20611646306'),
('73894578','darely.cruzt@gmail.com','Vendedor','Darely Cruz','20611646306');

INSERT INTO Empleado_Telefono VALUES
('T001','23280984','959555798'),
('T002','73894578','958066330');

INSERT INTO Producto VALUES
('P001','Arroz Costeño 5kg','Kg',18),
('P002','Aceite Primor 1L','Lt',10),
('P003','Leche Gloria Azul','g',4),
('P004','Azúcar Blanca 1kg','kg',4),
('P005','Atún Florida 170g','g',6),
('P006','Huevos Pardos docena','Un',10),
('P007','Fideos Don Vittorio','g',4),
('P008','Coca Cola 1.5L','Lt',8),
('P009','Galleta Soda Field','g',2),
('P010','Agua San Luis 2.5L','Lt',4);

INSERT INTO Cliente_Nombre VALUES
(1,'ECOTAMBO'),
(2,'ECOVITA'),
(3,'FIORELA DINA AGUILA'),
(4,'GASTRONOMIA ORIENTAL'),
(5,'GASTRO SERVICIOS'),
(6,'GESTION AMBIENTAL'),
(7,'GOMEZ NADIA F'),
(8,'GRUPO KAZAN'),
(9,'INGEMMET'),
(10,'JDK');

INSERT INTO Cliente_Direccion VALUES
(1,'Cayma - Arequipa'),
(2,'Cerro Colorado'),
(3,'Miraflores'),
(4,'Paucarpata'),
(5,'Bustamante'),
(6,'Yanahuara'),
(7,'Hunter'),
(8,'Umacollo'),
(9,'Cercado'),
(10,'Cerro Colorado');

INSERT INTO Cliente VALUES
('20559163491',1,1),
('20454335369',2,2),
('10465714072',3,3),
('20609084856',4,4),
('20558250411',5,5),
('20507850091',6,6),
('10769491606',7,7),
('20607892513',8,8),
('20112919377',9,9),
('20507418261',10,10);


INSERT INTO Factura VALUES
('F001','PEN','Venta local',90,14,76,90,'2025-01-05','20611646306','23280984'),
('F002','PEN','Yape',30,5,25,30,'2025-01-12','20611646306','73894578'),
('F003','PEN','Delivery',62,10,52,62,'2025-02-03','20611646306','23280984'),
('F004','PEN','Pago efectivo',112,18,94,112,'2025-02-18','20611646306','73894578'),
('F005','PEN','Pedido mayorista',160,26,134,160,'2025-03-07','20611646306','23280984'),
('F006','PEN','POS',20,3,17,20,'2025-03-21','20611646306','73894578'),
('F007','PEN','Yape',46,7,39,46,'2025-04-10','20611646306','23280984'),
('F008','PEN','Express',80,13,67,80,'2025-04-25','20611646306','73894578'),
('F009','PEN','Mayorista',92,15,77,92,'2025-05-11','20611646306','23280984'),
('F010','PEN','Compra rapida',16,3,13,16,'2025-05-28','20611646306','73894578');

-- PEDIDOS
INSERT INTO Pedido VALUES
('P001','20559163491','23280984','F001'),
('P002','20454335369','73894578','F002'),
('P003','10465714072','23280984','F003'),
('P004','20609084856','73894578','F004'),
('P005','20558250411','23280984','F005'),
('P006','20507850091','73894578','F006'),
('P007','10769491606','23280984','F007'),
('P008','20607892513','73894578','F008'),
('P009','20112919377','23280984','F009'),
('P010','20507418261','73894578','F010');

-- DETALLE DE PEDIDOS
INSERT INTO Pedido_Producto VALUES
('PP01','P001',2,'P003'),
('PP02','P001',1,'P008'),
('PP03','P002',3,'P007'),
('PP04','P003',2,'P006'),
('PP05','P004',4,'P004'),
('PP06','P005',8,'P001'),
('PP07','P006',2,'P010'),
('PP08','P007',1,'P008'),
('PP09','P008',4,'P002'),
('PP10','P009',5,'P009'),
('PP11','P010',2,'P003');

-- PRECIOS DE PEDIDO 
INSERT INTO Precios_Pedido VALUES
(1,'P001','90','76','90'),
(2,'P002','30','25','30'),
(3,'P003','62','52','62'),
(4,'P004','112','94','112'),
(5,'P005','160','134','160'),
(6,'P006','20','17','20'),
(7,'P007','46','39','46'),
(8,'P008','80','67','80'),
(9,'P009','92','77','92'),
(10,'P010','16','13','16');

