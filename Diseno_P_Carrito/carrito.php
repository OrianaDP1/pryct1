<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idusuario = $_SESSION['id_usuario'];

// Obtener idcliente desde idusuario (todo en minúsculas)
$stmtcliente = $con->prepare("SELECT idcliente FROM clientes WHERE idusuario = :idusuario");
$stmtcliente->execute([':idusuario' => $idusuario]);
$cliente = $stmtcliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    echo "Cliente no encontrado.";
    exit();
}
$idcliente = $cliente['idcliente'];

// Obtener productos del carrito
$stmtcarrito = $con->prepare("
    SELECT 
        p.idproducto,
        p.nombre,
        p.precio,
        p.tipo_mime
    FROM item_carrito ic
    JOIN carrito_compras cc ON ic.idcarrito = cc.idcarrito
    JOIN productos p ON ic.idproducto = p.idproducto
    WHERE cc.idcliente = :idcliente
");
$stmtcarrito->execute([':idcliente' => $idcliente]);
$productoscarrito = $stmtcarrito->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Carrito - Lista de Deseos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
      .product-img {
        max-width: 100%;
        max-height: 250px;
        object-fit: contain;
        background: #fff;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 0 10px rgb(0 0 0 / 0.1);
      }
    </style>
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

<main class="container my-4">
  <h2 class="mb-4">Mi Carrito de Compras</h2>
  
  <?php if (count($productoscarrito) === 0): ?>
    <div class="alert alert-info">Tu lista de deseos está vacía.</div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php foreach ($productoscarrito as $producto): ?>
        <div class="col">
          <div class="card h-100 shadow-sm">
            <img src="../Procedimientos/mostrar_imagen.php?idproducto=<?= (int)$producto['idproducto'] ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="product-img card-img-top" />
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
              <p class="card-text"><strong>Precio:</strong> $<?= number_format($producto['precio'], 2) ?></p>
              <form method="POST" action="../Diseno_P_Carrito/eliminar_del_carrito.php" class="mt-auto">
                <input type="hidden" name="idproducto" value="<?= (int)$producto['idproducto'] ?>" />
                <button type="submit" class="btn btn-outline-danger w-100">Eliminar</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
