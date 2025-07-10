<?php
session_start();

$idproducto = filter_input(INPUT_POST, 'idproducto', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

if (!$idproducto || !$cantidad || $cantidad < 1) {
    $_SESSION['error'] = "Datos inválidos para la compra.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

$_SESSION['compra_pendiente'] = [
    'idproducto' => $idproducto,
    'cantidad' => $cantidad
];

header("Location: pago.php");
exit();
?>