<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

// Buscar el IDCliente correspondiente a este usuario
$stmtCliente = $con->prepare("SELECT IDCliente FROM Clientes WHERE IDUsuario = :idUsuario");
$stmtCliente->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmtCliente->execute();
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $_SESSION['error'] = "Cliente no encontrado.";
    header(" ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

$idcliente = $cliente['IDCliente'];

// Recibir datos de formulario
$idproducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

if (!$idproducto || !$cantidad || $cantidad < 1) {
    $_SESSION['error'] = "Datos inválidos para la compra.";
    header(" ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

// Consultar producto y stock
$stmtProd = $con->prepare("SELECT Precio, StockActual FROM Productos WHERE IDProducto = :idproducto AND Estado = B'1'");
$stmtProd->bindParam(':idproducto', $idproducto, PDO::PARAM_INT);
$stmtProd->execute();
$producto = $stmtProd->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    $_SESSION['error'] = "Producto no encontrado o no disponible.";
    header(" ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

if ($producto['stockactual'] < $cantidad) {
    $_SESSION['error'] = "No hay suficiente stock disponible.";
    header("Location:  ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

try {
    $con->beginTransaction();

    // Insertar venta
    $precio_unit = $producto['precio'];
    $descuento = 0; // Aquí podrías aplicar lógica de descuentos si tienes

    $stmtVenta = $con->prepare("INSERT INTO Ventas (Cantidad, Precio_Unitario, Descuento, IDProducto, IDCliente)
                               VALUES (:cantidad, :precio_unit, :descuento, :idproducto, :idcliente)");
    $stmtVenta->execute([
        ':cantidad' => $cantidad,
        ':precio_unit' => $precio_unit,
        ':descuento' => $descuento,
        ':idproducto' => $idproducto,
        ':idcliente' => $idcliente
    ]);
    
    $idVenta = $con->lastInsertId();

    // Insertar detalle venta
    $stmtDetalle = $con->prepare("INSERT INTO Detalle_Ventas (Estado_Venta, Metodo_Pago, Estado_de_Envio, IDVenta) 
                                 VALUES (:estado, :metodo, :envio, :idVenta)");
    $estado_venta = true; // Por ejemplo, la venta está activa
    $metodo_pago = "Pago en línea"; // Ajusta según tu lógica real o formulario
    $estado_envio = 1; // Pendiente
    
    $stmtDetalle->execute([
        ':estado' => $estado_venta,
        ':metodo' => $metodo_pago,
        ':envio' => $estado_envio,
        ':idVenta' => $idVenta
    ]);

    // Actualizar stock
    $stmtUpdateStock = $con->prepare("UPDATE Productos SET StockActual = StockActual - :cantidad WHERE IDProducto = :idproducto");
    $stmtUpdateStock->execute([
        ':cantidad' => $cantidad,
        ':idproducto' => $idproducto
    ]);

    $con->commit();

    $_SESSION['success'] = "Compra realizada con éxito.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();

} catch (PDOException $e) {
    $con->rollBack();
    $_SESSION['error'] = "Error al procesar la compra: " . $e->getMessage();
    header("Location: Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}
