<?php
  session_start();
  include '../DB/conexion.php'; 
  require_once('../Procedimientos/ModuloCliente.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ABD - Inicio Cliente</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="ModuloCliente.css">
</head>
<body>

<header class="bg-dark text-white p-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h3 mb-0">Bienvenido, Cliente</h1>
      <nav>
        <ul class="nav">
          <li class="nav-item"><a class="nav-link text-white" href="#">Inicio</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Productos</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Mi Cuenta</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Carrito</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Cerrar sesión</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<main class="container mt-4">

  <h2>Buscar Productos</h2>
  <form method="get" action="../Procedimientos/catalogo_producto.php" class="mb-4">
    <div class="input-group">
      <input type="text" name="buscar" class="form-control" placeholder="Buscar por Nombre o Descripción..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
      <button class="btn btn-primary" type="submit">Buscar</button>
    </div>
  </form>

  <?php if (!empty($productos)): ?>
    <h3>Resultados:</h3>
    <ul class="list-group mb-4">
      <?php foreach ($productos as $producto): ?>
        <li class="list-group-item">
          <?php foreach ($producto as $clave => $valor): ?>
            <strong><?= htmlspecialchars($clave) ?>:</strong> <?= htmlspecialchars($valor) ?><br>
          <?php endforeach; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php elseif (isset($_GET['buscar'])): ?>
    <div class="alert alert-warning">No se encontraron productos con ese término.</div>
  <?php endif; ?>

  <h2 class="mb-4">Productos Destacados</h2>

  <?php
    $stmt = $con->prepare("SELECT idproducto, nombre, precio FROM Productos");
    $stmt->execute();
    $destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($destacados as $prod): ?>
      <div class="col">
        <div class="card h-100 product-card shadow-sm">
          <img src="../imagenes/celular_generico.jpg" class="card-img-top" alt="Imagen del celular">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($prod['nombre']) ?></h5>
            <p class="card-text"><strong>Precio:</strong> $<?= htmlspecialchars($prod['precio']) ?></p>
            <form method="POST" action="../Diseno_Proce_de_PaGeneProductos/productos.php">
              <input type="hidden" name="idproducto" value="<?= $prod['idproducto'] ?>">
              <button type="submit" class="btn btn-primary w-100">Ver Detalles</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</main>

<footer class="bg-light text-center py-3 mt-4">
  <p>&copy; 2025 Mi Empresa. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php session_destroy(); ?>