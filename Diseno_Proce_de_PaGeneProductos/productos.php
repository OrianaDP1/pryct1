<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

// Manejo de búsqueda
$buscar = trim($_GET['buscar'] ?? '');

// Si hay búsqueda, traemos productos que coincidan
if ($buscar !== '') {
    $stmt = $con->prepare("SELECT * FROM Productos WHERE (nombre ILIKE :busqueda OR descripcion ILIKE :busqueda) AND estado = B'1' ORDER BY nombre");
    $likeBuscar = "%$buscar%";
    $stmt->bindParam(':busqueda', $likeBuscar, PDO::PARAM_STR);
} else {
    $stmt = $con->prepare("SELECT * FROM Productos WHERE estado = B'1' ORDER BY nombre");
}
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

  <!-- Buscador -->
  <section class="mb-5">
    <form method="get" class="d-flex" role="search" aria-label="Buscar productos">
      <input
        type="search"
        name="buscar"
        class="form-control form-control-lg me-2"
        placeholder="Busca tu celular por nombre o descripción..."
        value="<?= htmlspecialchars($buscar) ?>"
        autofocus
        autocomplete="off"
      />
      <button type="submit" class="btn btn-primary btn-lg">Buscar</button>
    </form>
  </section>

  <!-- Lista de productos: mostramos solo el primero destacado y los demás abajo -->
  <?php if (count($productos) > 0): ?>

    <?php
      // Producto destacado: el primero de la lista
      $destacado = $productos[0];
      $resto = array_slice($productos, 1);
    ?>

    <section class="product-detail row g-4 mb-5">
      <div class="col-md-6 d-flex justify-content-center align-items-center">
        <img src="../Procedimientos/mostrar_imagen.php?idproducto=<?= (int)$destacado['idproducto'] ?>" alt="Imagen <?= htmlspecialchars($destacado['nombre']) ?>" class="product-img" />
      </div>
      <div class="col-md-6">
        <h2><?= htmlspecialchars($destacado['nombre']) ?></h2>
        <p class="text-muted">Código Producto: <?= (int)$destacado['idproducto'] ?></p>
        <h3 class="text-success">$<?= number_format($destacado['precio'], 2, ',', '.') ?></h3>
        <p><strong>Stock disponible:</strong> <?= (int)$destacado['stockactual'] ?></p>
        <p><?= nl2br(htmlspecialchars($destacado['descripcion'])) ?></p>

        
        <form method="post" action="pagointermedio.php" class="mt-4">
          <input type="hidden" name="idproducto" value="<?= (int)$destacado['idproducto'] ?>">
          <label for="cantidad" class="form-label">Cantidad:</label>
          <input
            type="number"
            id="cantidad"
            name="cantidad"
            class="form-control"
            style="max-width: 100px;"
            value="1"
            min="1"
            max="<?= (int)$destacado['stockactual'] ?>"
            required
          />
          <button type="submit" class="btn btn-success btn-lg mt-3">Comprar Ahora</button>
        </form>
      </div>
    </section>

    <?php if (count($resto) > 0): ?>
      <section class="product-list">
        <h3 class="mb-4">Otros celulares disponibles</h3>
        <div class="row row-cols-1 row-cols-md-3 g-4">
          <?php foreach ($resto as $prod): ?>
            <div class="col">
              <div class="card h-100 shadow-sm">
                <img src="../imagenes/celular_generico.jpg" class="card-img-top" alt="Imagen de <?= htmlspecialchars($prod['nombre']) ?>" />
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= htmlspecialchars($prod['nombre']) ?></h5>
                  <p class="card-text text-success fw-bold">$<?= number_format($prod['precio'], 2, ',', '.') ?></p>
                  <p class="card-text"><small>Stock: <?= (int)$prod['stockactual'] ?></small></p>
                  <form method="post" action="comprar_producto.php" class="mt-auto">
                    <input type="hidden" name="idproducto" value="<?= (int)$prod['idproducto'] ?>">
                    <input type="hidden" name="cantidad" value="1" />
                    <button type="submit" class="btn btn-sm btn-success w-100">Comprar Ahora</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  <?php else: ?>
    <div class="alert alert-warning">No se encontraron productos que coincidan.</div>
  <?php endif; ?>

</main>

<footer class="bg-light text-center py-3 mt-5">
  <p>&copy; 2025 Mi Empresa. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
