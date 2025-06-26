<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Cliente</title>
</head>
<body>
    <h2>Bienvenido, Cliente <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
    <p>Este es tu panel de cliente.</p>
    
    <ul>
        <li><a href="#">Mis Pedidos</a></li>
        <li><a href="#">Mi Perfil</a></li>
        <li><a href="#">Soporte</a></li>
    </ul>
    
    <a href="logout.php">Cerrar Sesión</a>
</body>
</html>