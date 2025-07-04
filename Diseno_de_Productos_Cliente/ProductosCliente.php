<?php
session_start();
require_once '../DB/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

// Obtener IDCliente para el usuario logueado
$stmt = $con->prepare("SELECT idcliente FROM Clientes WHERE idusuario = :id");
$stmt->execute([':id' => $idUsuario]);
$idCliente = $stmt->fetchColumn();

if (!$idCliente) {
    echo "Cliente no encontrado.";
    exit();
}

// Consulta productos comprados por el cliente
$sql = "
SELECT 
    p.idproducto,
    p.nombre,
    p.descripcion,
    p.precio,
    v.cantidad,
    v.precio_unitario,
    d.fecha_venta,
    d.metodo_pago,
    d.estado_de_envio
FROM Ventas v
JOIN Detalle_Ventas d ON v.idventa = d.idventa
JOIN Productos p ON v.idproducto = p.idproducto
WHERE v.idcliente = :idcliente
ORDER BY d.fecha_venta DESC
";
$stmt = $con->prepare($sql);
$stmt->execute([':idcliente' => $idCliente]);
$productosComprados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mis Compras</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .product-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
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

<main class="container mt-4">
  <h2>Mis Productos Comprados</h2>

  <?php if (count($productosComprados) === 0): ?>
    <p>No has comprado productos aún.</p>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php foreach ($productosComprados as $prod): ?>
        <div class="col">
          <div class="card h-100 shadow-sm">
            <img
              src="../Procedimientos/mostrar_imagen.php?idproducto=<?= (int)$prod['idproducto'] ?>"
              alt="<?= htmlspecialchars($prod['nombre']) ?>"
              class="product-img card-img-top"
            />
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($prod['nombre']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($prod['descripcion']) ?></p>
              <ul class="list-unstyled">
                <li><strong>Cantidad:</strong> <?= $prod['cantidad'] ?></li>
                <li><strong>Precio unitario:</strong> $<?= number_format($prod['precio_unitario'], 2) ?></li>
                <li><strong>Fecha de compra:</strong> <?= date('d/m/Y H:i', strtotime($prod['fecha_venta'])) ?></li>
                <li><strong>Método de pago:</strong> <?= htmlspecialchars($prod['metodo_pago']) ?></li>
                <li><strong>Estado de envío:</strong>
                  <?php
                    // Ejemplo de interpretación simple
                    switch ($prod['estado_de_envio']) {
                      case 1: echo "En proceso"; break;
                      case 2: echo "Enviado"; break;
                      case 3: echo "Entregado"; break;
                      default: echo "Desconocido";
                    }
                  ?>
                </li>
              </ul>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<footer class="bg-light text-center py-3 mt-4">
  <p>&copy; 2025 Mi Empresa. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
