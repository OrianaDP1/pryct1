<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contraseña'];
    
    
    $stmt = $cn->prepare("SELECT id, nombre_usuario, contraseña, tipo_usuario FROM usuarios WHERE nombre_usuario = ?");
    $stmt->execute([$nombre_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        
        
        if ($usuario['tipo_usuario'] === 'administrador') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: cliente_dashboard.php");
        }
        exit();
    } else {
        $error = "Nombre de usuario o contraseña incorrectos";
    }
}
?>