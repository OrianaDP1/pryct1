<?php
session_start();
include '../DB/conexion.php';

try {
    $sql = "
        SELECT 
            v.IDVenta AS idventa,
            v.Cantidad, 
            v.Precio_Unitario, 
            v.Descuento, 
            d.Fecha_Venta, 
            d.Metodo_Pago, 
            d.Estado_de_Envio, 
            p.Nombre AS nombre_producto, 
            p.Imagen AS imagen_producto, 
            p.tipo_mime, 
            c.Nombres, 
            c.Apellidos
        FROM Ventas v
        INNER JOIN Detalle_Ventas d ON v.IDVenta = d.IDVenta
        INNER JOIN Productos p ON v.IDProducto = p.IDProducto
        INNER JOIN Clientes c ON v.IDCliente = c.IDCliente
        WHERE d.Estado_de_Envio IN (1, 2)
    ";

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    die();
}

function estadoEnvioTexto($estado) {
    switch ($estado) {
        case 1: return "Preparando";
        case 2: return "Enviando";
        case 3: return "Entregado"; // Agregamos "Entregado" por si lo usas en el futuro
        default: return "Desconocido";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img img {
            max-width: 120px;
            height: auto;
            border-radius: 8px;
        }
        .sale-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            background: #fff;
        }
        .sidebar .btn {
            width: 100%;
            text-align: left;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar text-white p-3 min-vh-100">
      <h4 class="mb-4">Administrador Empresa</h4>
      <div class="d-grid gap-2">
        <a href="../Diseño de las Interfaces/ModuloEmpresa.php" class="btn btn-outline-light text-start">Inicio</a>
        <a href="" class="btn btn-outline-light text-start">Inventario</a>
        <a href="#" class="btn btn-outline-light text-start">Configuración</a>
        <a href="../Diseno_P_de_Logout/logout.php" class="btn btn-outline-danger text-start">Cerrar sesión</a>
      </div>
    </nav>

    <!-- Contenido principal -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <h2 class="mb-4">Ventas de la Empresa</h2>
      <div class="row">
        <?php foreach ($ventas as $venta): ?>
          <div class="col-md-8 mb-4">
            <div class="card sale-card shadow-sm">
              <div class="d-flex">
                <div class="product-img me-3">
                  <?php if ($venta['imagen_producto']): ?>
                    <img src="data:<?= $venta['tipo_mime'] ?>;base64,<?= base64_encode($venta['imagen_producto']) ?>" alt="Producto">
                  <?php else: ?>
                    <p>Sin imagen</p>
                  <?php endif; ?>
                </div>
                <div class="card-body">
                  <h5 class="card-title"><?= $venta['nombre_producto'] ?></h5>
                  <p><strong>Cliente:</strong> <?= $venta['nombres'] . ' ' . $venta['Apellidos'] ?></p>
                  <p><strong>Cantidad:</strong> <?= $venta['cantidad'] ?></p>
                  <p><strong>Precio Unitario:</strong> S/ <?= number_format($venta['Precio_Unitario'], 2) ?></p>
                  <p><strong>Descuento:</strong> <?= $venta['descuento'] ?>%</p>
                  <p><strong>Fecha de Venta:</strong> <?= $venta['fecha_Venta'] ?></p>
                  <p><strong>Método de Pago:</strong> <?= $venta['metodo_Pago'] ?></p>
                  <p><strong>Estado de Envío:</strong> <?= estadoEnvioTexto($venta['estado_de_Envio']) ?></p>

                  <!-- Botones de acción -->
                  <div class="mt-3 d-flex gap-2">
                    <form action="marcar_enviado.php" method="POST">
                      <input type="hidden" name="id_venta" value="<?= $venta['idventa'] ?>">
                      <button type="submit" class="btn btn-success btn-sm">Marcar como Enviado</button>
                    </form>
                    <form action="cancelar_venta.php" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?');">
                      <input type="hidden" name="id_venta" value="<?= $venta['idventa'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Cancelar Venta</button>
                    </form>
                    <!-- Nuevo botón de actualización de estado -->
                    <a href="estado de venta.php?id_venta=<?= $venta['idventa'] ?>" class="btn btn-warning btn-sm">Actualizar Estado</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($ventas)): ?>
          <div class="col-12">
            <div class="alert alert-warning text-center">No hay ventas en estado "Preparando" o "Enviando".</div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
</body>
</html>
