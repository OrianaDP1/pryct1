<?php
session_start();
include '../DB/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];    

    $stmt = $con->prepare("SELECT IDUsuario, Nombre, Contrasena FROM Usuario WHERE Nombre = ?");
    $stmt->execute([$nombre_usuario]);
    
    if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "verificado";
        $_SESSION['id_usuario'] = $usuario['idusuario'];
        $_SESSION['nombre_usuario'] = $usuario['nombre'];

        $stmt_cliente = $con->prepare("SELECT IDCliente FROM Clientes WHERE IDUsuario = ?");
        $stmt_cliente->execute([$usuario['idusuario']]);
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        
        $stmt_empresa = $con->prepare("SELECT IDEmpresa FROM Empresa_Proveedora WHERE IDUsuario = ?");
        $stmt_empresa->execute([$usuario['idusuario']]);
        $empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

        if ($empresa) {
            $_SESSION['tipo_usuario'] = 'proveedor';
            echo "1";
            header('location: ../Procedimientos/ModuloAdministrador.php'); 
        } elseif ($cliente) {
            $_SESSION['tipo_usuario'] = 'cliente';
            echo "2";
            header('location: ../Procedimientos/ModuloCliente.php'); 
        } else {
            $_SESSION['tipo_usuario'] = 'desconocido';
            echo "3";
            header("Location: perfil.php");
        }
        exit();
    } else {
        $error = "Nombre de usuario o contraseña incorrectos";
    }
}
?>
