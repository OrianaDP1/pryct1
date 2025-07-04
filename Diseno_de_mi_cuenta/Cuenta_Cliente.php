<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$stmt = $con->prepare("SELECT u.idusuario, u.nombre, u.correo, c.idcliente, c.nombres, c.apellidos FROM usuario u LEFT JOIN clientes c ON u.idusuario = c.idusuario WHERE u.idusuario = :idUsuario");
$stmt->execute([':idUsuario' => $idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mi Cuenta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<header class="bg-dark text-white p-4">
  <div class="container d-flex justify-content-between align-items-center">
    <h1 class="h3 mb-0">Bienvenido, Cliente</h1>
    <nav>
      <ul class="nav">
        <li class="nav-item"><a class="nav-link text-white" href="../Diseño de las Interfaces/ModuloCliente.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="../Diseno_de_Productos_Cliente/ProductosCliente.php">Compras</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="../Diseno_de_mi_cuenta/Cuenta_Cliente.php">Mi Cuenta</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="../Diseno_P_Carrito/carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="../Diseno_P_de_Logout/logout.php">Cerrar sesión</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="container my-5">
  <div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
      <h4 class="card-title mb-4">Datos de tu cuenta</h4>
      <img src="../Procedimientos/mostrar_imagen_cliente.php?idusuario=<?=(int)$usuario['idusuario']?>" alt="Imagen de <?= htmlspecialchars($usuario['nombre']) ?>" class="img-thumbnail mb-3" style="max-width: 200px;" />
      <p><strong>Nombre de usuario:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo'] ?? 'No registrado') ?></p>
      <p><strong>Nombre completo:</strong> <?= htmlspecialchars(trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''))) ?></p>
      <p><strong>ID Cliente:</strong> <?= htmlspecialchars($usuario['idcliente'] ?? 'No asignado') ?></p>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
