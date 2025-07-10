<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idproducto = filter_input(INPUT_GET, 'idproducto', FILTER_VALIDATE_INT);

if (!$idproducto) {
    $_SESSION['error'] = "Producto no especificado o inválido.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

// Buscar el producto solicitado
$stmt = $con->prepare("SELECT * FROM productos WHERE idproducto = :idproducto AND estado = B'1'");
$stmt->bindParam(':idproducto', $idproducto, PDO::PARAM_INT);
$stmt->execute();
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    $_SESSION['error'] = "Producto no encontrado o no disponible.";
    header("Location: ../Diseño de las Interfaces/ModuloCliente.php");
    exit();
}

// Capturar mensajes flash
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Compra de Celulares - ABD</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="productos.css">
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

<main class="container">

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <section class="product-detail row g-4 mb-5">
    <div class="col-md-6 d-flex justify-content-center align-items-center">
      <img src="../Procedimientos/mostrar_imagen.php?idproducto=<?= (int)$producto['idproducto'] ?>" alt="Imagen <?= htmlspecialchars($producto['nombre']) ?>" class="product-img" />
    </div>
    <div class="col-md-6">
      <h2><?= htmlspecialchars($producto['nombre']) ?></h2>
      <p class="text-muted">Código Producto: <?= (int)$producto['idproducto'] ?></p>
      <h3 class="text-success">$<?= number_format($producto['precio'], 2, ',', '.') ?></h3>
      <p><strong>Stock disponible:</strong> <?= (int)$producto['stockactual'] ?></p>
      <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

      <form method="post" action="pago.php" class="mt-4">
        <input type="hidden" name="idproducto" value="<?= (int)$producto['idproducto'] ?>">
        <label for="cantidad" class="form-label">Cantidad:</label>
        <input
          type="number"
          id="cantidad"
          name="cantidad"
          class="form-control"
          style="max-width: 100px;"
          value="1"
          min="1"
          max="<?= (int)$producto['stockactual'] ?>"
          required
        />
        <button type="submit" class="btn btn-success btn-lg mt-3">Comprar Ahora</button>
      </form>
    </div>
  </section>

</main>

<footer class="bg-light text-center py-3 mt-5">
  <p>&copy; 2025 Mi Empresa. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

