-- Usuario
INSERT INTO Usuario (Nombre, Contrasena, Correo, Telefono, Direccion, Imagen)
VALUES ('Juan Perez', '$2y$10$eFqVPfrSvjR0jSIQBqFn6OdV7xsoA/i0DIFpYoXtqG3WCHyhbIaLu', 'juan@example.com', '987654321', 'Av. Siempre Viva 123', NULL);

-- Clientes
INSERT INTO Clientes (Nombres, Apellidos, IDUsuario)
VALUES ('Carlos', 'Ramirez', 1);

-- Empresa_Proveedora
INSERT INTO Empresa_Proveedora (RUC, Nombre, IDUsuario)
VALUES ('12345678901', 'Proveedor SAC', 1);

-- Marcas
INSERT INTO Marcas (Nombre, Imagen)
VALUES ('MarcaX', NULL);

-- Categoria
INSERT INTO Categoria (Nombre, Descripcion, Estado)
VALUES ('Electrónica', 'Productos electrónicos y gadgets', B'1');

-- Productos
INSERT INTO Productos (Nombre, Descripcion, Precio, StockActual, Color, Imagen, IDCategoria, IDMarca, IDEmpresa, Estado)
VALUES ('Smartphone XYZ', 'Teléfono inteligente con muchas funciones', 999.99, 50, 'FF0000', NULL, 1, 1, 1, B'1');

-- Ventas
INSERT INTO Ventas (Cantidad, Precio_Unitario, Descuento, IDProducto, IDCliente)
VALUES (2, 999.99, 10, 1, 1);

-- Detalle_Ventas
INSERT INTO Detalle_Ventas (Fecha_Venta, Estado_Venta, Metodo_Pago, Estado_de_Envio, IDVenta)
VALUES (CURRENT_TIMESTAMP, TRUE, 'Tarjeta de crédito', 1, 1);

-- Carrito_Compras
INSERT INTO Carrito_Compras (Ultima_Actualizacion, IDCliente)
VALUES (CURRENT_DATE, 1);

-- Item_Carrito
INSERT INTO Item_Carrito (Razon_Anulacion, IDCarrito, IDProducto)
VALUES ('Cliente canceló', 1, 1);