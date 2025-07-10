<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$stmtCliente = $con->prepare("SELECT idcliente FROM clientes WHERE idusuario = :idusuario");
$stmtCliente->bindParam(':idusuario', $idUsuario, PDO::PARAM_INT);
$stmtCliente->execute();
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $_SESSION['error'] = "Cliente no encontrado.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

$idcliente = $cliente['idcliente'];

$idproducto = null;
$cantidad = null;

if (isset($_SESSION['pago_autorizado'], $_SESSION['compra_pendiente']) && $_SESSION['pago_autorizado'] === true) {
    $idproducto = $_SESSION['compra_pendiente']['idproducto'];
    $cantidad = $_SESSION['compra_pendiente']['cantidad'];
    unset($_SESSION['pago_autorizado'], $_SESSION['compra_pendiente']);
} else {
    $idproducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
}

if (!$idproducto || !$cantidad || $cantidad < 1) {
    $_SESSION['error'] = "Datos inválidos para la compra. Producto: " . htmlspecialchars($idproducto) . ", Cantidad: " . htmlspecialchars($cantidad);
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

$stmtProd = $con->prepare("SELECT precio, stockactual FROM productos WHERE idproducto = :idproducto AND estado = B'1'");
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

    $stmtVenta = $con->prepare("
        INSERT INTO ventas (cantidad, precio_unitario, descuento, idproducto, idcliente)
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
        INSERT INTO detalle_ventas (estado_venta, metodo_pago, estado_de_envio, idventa)
        VALUES (:estado, :metodo, :envio, :idventa)
    ");
    $stmtDetalle->execute([
        ':estado' => true,
        ':metodo' => 'Pago en línea',
        ':envio' => 1,
        ':idventa' => $idVenta
    ]);

    $stmtUpdate = $con->prepare("
        UPDATE productos SET stockactual = stockactual - :cantidad
        WHERE idproducto = :idproducto
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
