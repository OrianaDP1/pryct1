<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$idProducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);

if (!$idProducto) {
    header("Location: historial_compras.php");
    exit();
}

// Obtener IDCliente del usuario actual
$stmt = $con->prepare("SELECT IDCliente FROM Clientes WHERE idusuario = :idusuario");
$stmt->bindParam(':idusuario', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: historial_compras.php");
    exit();
}

$idCliente = $cliente['idcliente'];

try {
    // Primero eliminar de Detalle_Ventas si hay relación
    $stmtDetalle = $con->prepare("
        DELETE FROM Detalle_Ventas
        WHERE IDVenta IN (
            SELECT IDVenta FROM Ventas WHERE IDProducto = :idproducto AND IDCliente = :idcliente
        )
        LIMIT 1
    ");
    $stmtDetalle->execute([
        ':idproducto' => $idProducto,
        ':idcliente' => $idCliente
    ]);

    // Luego eliminar de Ventas
    $stmtVenta = $con->prepare("
        DELETE FROM Ventas
        WHERE IDProducto = :idproducto AND IDCliente = :idcliente
        LIMIT 1
    ");
    $stmtVenta->execute([
        ':idproducto' => $idProducto,
        ':idcliente' => $idCliente
    ]);

} catch (PDOException $e) {
}

header("Location: ProductosCliente.php");
exit();
