<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idusuario = $_SESSION['id_usuario'];

// Validar idproducto recibido
$idproducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);
if (!$idproducto) {
    $_SESSION['error'] = "Producto inválido.";
    header("Location: ../Diseno_P_Carrito/carrito.php");
    exit();
}

// Obtener idcliente
$stmtcliente = $con->prepare("SELECT idcliente FROM clientes WHERE idusuario = :idusuario");
$stmtcliente->execute([':idusuario' => $idusuario]);
$cliente = $stmtcliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $_SESSION['error'] = "Cliente no encontrado.";
    header("Location: ../Diseno_P_Carrito/carrito.php");
    exit();
}
$idcliente = $cliente['idcliente'];

// Obtener idcarrito del cliente
$stmtcarrito = $con->prepare("SELECT idcarrito FROM carrito_compras WHERE idcliente = :idcliente");
$stmtcarrito->execute([':idcliente' => $idcliente]);
$carrito = $stmtcarrito->fetch(PDO::FETCH_ASSOC);

if (!$carrito) {
    $_SESSION['error'] = "Carrito no encontrado.";
    header("Location: ../Diseno_P_Carrito/carrito.php");
    exit();
}
$idcarrito = $carrito['idcarrito'];

// Eliminar el producto del carrito
$stmtdelete = $con->prepare("DELETE FROM item_carrito WHERE idcarrito = :idcarrito AND idproducto = :idproducto");
$stmtdelete->execute([
    ':idcarrito' => $idcarrito,
    ':idproducto' => $idproducto
]);

if ($stmtdelete->rowCount() > 0) {
    $_SESSION['success'] = "Producto eliminado del carrito.";
} else {
    $_SESSION['error'] = "No se pudo eliminar el producto del carrito.";
}

header("Location: ../Diseno_P_Carrito/carrito.php");
exit();
