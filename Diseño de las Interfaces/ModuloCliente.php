<?php
session_start();
include '../DB/conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ABD</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Estilos personalizados opcionales -->
  <link rel="stylesheet" href="ModuloCliente.css">
</head>
<body>

<header class="bg-dark text-white p-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h3 mb-0">ABD</h1>
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
  <h2 class="mb-4">Productos Destacados</h2>

  <?php
    $stmt = $con->prepare("SELECT idproducto, nombre, precio FROM Productos");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($productos as $prod): ?>
      <div class="col">
        <div class="card h-100 product-card shadow-sm">
          <img src="../imagenes/celular_generico.jpg" class="card-img-top" alt="Imagen del celular">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($prod['nombre']); ?></h5>
            <p class="card-text"><strong>Precio:</strong> $<?php echo htmlspecialchars($prod['precio']); ?></p>
            <form method="POST" action="../Diseno_Proce_de_PaGeneProductos/productos.php">
              <input type="hidden" name="idproducto" value="<?php echo $prod['idproducto']; ?>">
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
<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php session_destroy(); ?>
