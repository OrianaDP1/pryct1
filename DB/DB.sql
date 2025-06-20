-- Database: SVentaCDB

-- DROP DATABASE IF EXISTS "SVentaCDB";

--CREATE DATABASE "SVentaCDB"
--    WITH
--    OWNER = postgres
--    ENCODING = 'UTF8'
--    LC_COLLATE = 'es-ES'
--    LC_CTYPE = 'es-ES'
--    LOCALE_PROVIDER = 'libc'
--    TABLESPACE = pg_default
--    CONNECTION LIMIT = -1
--    IS_TEMPLATE = False;

--Comienza aquí

CREATE TABLE Usuario (
	IDUsuario SERIAL PRIMARY KEY,
	Nombre VARCHAR(90),
	Contrasena VARCHAR(50),
	Correo VARCHAR(120),
	Telefono VARCHAR(12),
	Direccion VARCHAR(100),
	Imagen bytea --Imagen
);

CREATE TABLE Clientes (
	IDCliente SERIAL PRIMARY KEY,
	Nombres VARCHAR(100),
	Apellidos VARCHAR(100),
	IDUsuario INT,
	FOREIGN KEY (IDUsuario) REFERENCES Usuario(IDUsuario)
);

CREATE TABLE Empresa_Proveedora (
	IDEmpresa SERIAL PRIMARY KEY,
	RUC VARCHAR(11),
	Nombre VARCHAR(100),
	IDUsuario INT,
	FOREIGN KEY (IDUsuario) REFERENCES Usuario(IDUsuario)
);

CREATE TABLE Marcas(
	IDMarca SERIAL PRIMARY KEY,
	Nombre VARCHAR(50),
	Imagen bytea --Imagen
);
CREATE TABLE Categoria(
	IDCategoria SERIAL PRIMARY KEY,
	Nombre VARCHAR(200),
	Descripcion VARCHAR(400),
	Estado BIT --SI= 1, NO =0
);

CREATE TABLE Productos (
	IDProducto SERIAL PRIMARY KEY,
	Nombre VARCHAR(200),
	Descripcion VARCHAR(400),
	Precio DECIMAL(12,2),
	StockActual INT,
	Color VARCHAR(6), -- Código HEX
	Imagen BYTEA,
	IDCategoria INT,
	IDMarca INT,
	IDEmpresa INT,
	Estado BIT,
	FechaPublicacion DATE DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (IDCategoria) REFERENCES Categoria(IDCategoria),
	FOREIGN KEY (IDMarca) REFERENCES Marcas(IDMarca),
	FOREIGN KEY (IDEmpresa) REFERENCES Empresa_Proveedora(IDEmpresa)
);

CREATE TABLE Ventas(
	IDVenta SERIAL PRIMARY KEY,
	Cantidad INT NOT NULL,
	Precio_Unitario DECIMAL(10,2) NOT NULL,
	Descuento INT DEFAULT 0,
	IDProducto INT,
	IDCliente INT,
	FOREIGN KEY (IDProducto) REFERENCES Productos(IDProducto),
	FOREIGN KEY (IDCliente) REFERENCES Clientes(IDCliente)
);

CREATE TABLE Detalle_Ventas (
    IDDetalle SERIAL PRIMARY KEY,
    Fecha_Venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estado_Venta BOOLEAN,
    Metodo_Pago VARCHAR(50),
    Estado_de_Envio INT DEFAULT 1 CHECK (Estado_de_Envio IN (1, 2, 3)),
	IDVenta INT,
	FOREIGN KEY (IDVenta) REFERENCES Ventas(IDVenta)
);

CREATE TABLE Carrito_Compras(
	IDCarrito SERIAL PRIMARY KEY,
	Ultima_Actualizacion DATE,
	IDCliente INT,
	FOREIGN KEY (IDCliente) REFERENCES Clientes(IDCliente)
);

CREATE TABLE Item_Carrito(
	IDItem SERIAL PRIMARY KEY,
	Razon_Anulacion VARCHAR(30),
	IDCarrito INT,
	IDProducto INT,
	FOREIGN KEY (IDProducto) REFERENCES Productos(IDProducto),
	FOREIGN KEY (IDCarrito) REFERENCES Carrito_Compras(IDCarrito)
);
