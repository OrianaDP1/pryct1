<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
</head>
<body>
    <h2>Bienvenido, Administrador <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
    <p>Este es el panel de administración.</p>
    
    <ul>
        <li><a href="#">Gestionar Usuarios</a></li>
        <li><a href="#">Configuración del Sistema</a></li>
        <li><a href="#">Reportes</a></li>
    </ul>
    
    <a href="logout.php">Cerrar Sesión</a>
</body>
</html>