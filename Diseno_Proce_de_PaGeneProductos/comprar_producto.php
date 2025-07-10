<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
echo $idUsuario;
// ✔️ CONSULTAR el IDCliente real usando el IDUsuario
$stmtCliente = $con->prepare("SELECT IDCliente FROM Clientes WHERE idusuario = :idusuario");
$stmtCliente->bindParam(':idusuario', $idUsuario, PDO::PARAM_INT);
$stmtCliente->execute();
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $_SESSION['error'] = "Cliente no encontrado.";
    exit();
}

$idcliente = $cliente ['idcliente'];

// Recibir datos del formulario
$idproducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

if (isset($_SESSION['pago_autorizado'], $_SESSION['compra_pendiente']) && $_SESSION['pago_autorizado'] === true) {
    $idproducto = $_SESSION['compra_pendiente']['idproducto'];
    $cantidad = $_SESSION['compra_pendiente']['cantidad'];
    // Limpiar flags antes de continuar
    unset($_SESSION['pago_autorizado'], $_SESSION['compra_pendiente']);
} else {
    // Fallback al método tradicional vía POST (compra directa sin pago)
    $idproducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
}

if (!$idproducto || !$cantidad || $cantidad < 1) {
    $_SESSION['error'] = "Datos inválidos para la compra.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

// Consultar producto y stock
$stmtProd = $con->prepare("SELECT Precio, StockActual FROM Productos WHERE IDProducto = :idproducto AND Estado = B'1'");
$stmtProd->bindParam(':idproducto', $idproducto, PDO::PARAM_INT);
$stmtProd->execute();
$producto = $stmtProd->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    $_SESSION['error'] = "Producto no encontrado o no disponible.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

if ($producto['stockactual'] < $cantidad) {
    $_SESSION['error'] = "No hay suficiente stock disponible.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

try {
    $con->beginTransaction();

    // Insertar en Ventas
    $stmtVenta = $con->prepare("
        INSERT INTO Ventas (Cantidad, Precio_Unitario, Descuento, IDProducto, IDCliente)
        VALUES (:cantidad, :precio_unit, :descuento, :idproducto, :idcliente)
    ");
    $stmtVenta->execute([
        ':cantidad' => $cantidad,
        ':precio_unit' => $producto['precio'],
        ':descuento' => 0,
        ':idproducto' => $idproducto,
        ':idcliente' => $idcliente  
    ]);
    
    $idVenta = $con->lastInsertId();

    $stmtDetalle = $con->prepare("
        INSERT INTO Detalle_Ventas (Estado_Venta, Metodo_Pago, Estado_de_Envio, IDVenta)
        VALUES (:estado, :metodo, :envio, :idventa)
    ");
    $stmtDetalle->execute([
        ':estado' => true,
        ':metodo' => 'Pago en línea',
        ':envio' => 1,
        ':idventa' => $idVenta
    ]);

    // Actualizar stock
    $stmtUpdate = $con->prepare("
        UPDATE Productos SET StockActual = StockActual - :cantidad
        WHERE IDProducto = :idproducto
    ");
    $stmtUpdate->execute([
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
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}
