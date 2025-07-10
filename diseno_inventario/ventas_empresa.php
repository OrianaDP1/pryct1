<?php
session_start();
include '../DB/conexion.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

// Procesar marcado como enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_venta']) && isset($_POST['accion'])) {
    $idVenta = $_POST['id_venta'];
    $accion = $_POST['accion'];
    
    try {
        if ($accion === 'marcar_enviado') {
            // Actualizar el estado de envío a 3 (Enviado)
            $stmt = $con->prepare("UPDATE Detalle_Ventas SET Estado_de_Envio = 3 WHERE IDVenta = ?");
            $stmt->execute([$idVenta]);
            $_SESSION['mensaje'] = "El estado de envío se ha actualizado a 'Enviado' correctamente.";
        } elseif ($accion === 'cancelar_venta') {
            // Actualizar el estado de envío a 0 (Cancelado)
            $stmt = $con->prepare("UPDATE Detalle_Ventas SET Estado_de_Envio = 0 WHERE IDVenta = ?");
            $stmt->execute([$idVenta]);
            $_SESSION['mensaje'] = "La venta ha sido cancelada correctamente.";
        }
        
        // Redirigir para evitar reenvío del formulario
        header("Location: ventas_empresa.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al procesar la solicitud: " . $e->getMessage();
        header("Location: ventas_empresa.php");
        exit();
    }
}

// Obtener empresa asociada al usuario
$idUsuario = $_SESSION['id_usuario'];
$stmtEmpresa = $con->prepare("SELECT IDEmpresa as id_empresa, Nombre as nombre_empresa FROM Empresa_Proveedora WHERE IDUsuario = ?");
$stmtEmpresa->execute([$idUsuario]);
$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

if (!$empresa || !isset($empresa['id_empresa'])) {
    die("Empresa no encontrada o no tiene permisos.");
}

$idEmpresa = $empresa['id_empresa'];
$nombreEmpresa = $empresa['nombre_empresa'];

// Obtener ventas
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
        WHERE d.Estado_de_Envio IN (1, 2, 3) AND p.IDEmpresa = ?
        ORDER BY d.Fecha_Venta DESC
    ";

    $stmt = $con->prepare($sql);
    $stmt->execute([$idEmpresa]);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al obtener las ventas: " . $e->getMessage();
}

function estadoEnvioTexto($estado) {
    switch ($estado) {
        case 0: return "Cancelado";
        case 1: return "Preparando";
        case 2: return "Enviando";
        case 3: return "Enviado";
        default: return "Desconocido";
    }
}

function badgeColor($estado) {
    switch ($estado) {
        case 0: return 'bg-secondary';
        case 1: return 'bg-warning text-dark';
        case 2: return 'bg-info text-dark';
        case 3: return 'bg-success';
        default: return 'bg-dark';
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
            margin-bottom: 20px;
        }
        .sidebar .btn {
            width: 100%;
            text-align: left;
            margin-bottom: 0.5rem;
        }
        .badge {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
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
      <h2 class="mb-4">Ventas de <?= htmlspecialchars($nombreEmpresa) ?></h2>
      
      <!-- Mostrar mensajes -->
      <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <?= $_SESSION['mensaje'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <div class="row">
        <?php foreach ($ventas as $venta): ?>
          <div class="col-md-12">
            <div class="card sale-card shadow-sm">
              <div class="d-flex">
                <div class="product-img me-3">
                  <?php if ($venta['imagen_producto']): ?>
                    <img src="data:<?= htmlspecialchars($venta['tipo_mime']) ?>;base64,<?= base64_encode($venta['imagen_producto']) ?>" alt="<?= htmlspecialchars($venta['nombre_producto']) ?>">
                  <?php else: ?>
                    <div class="bg-light p-3 rounded text-center" style="width: 120px; height: 120px;">
                      <small>Sin imagen</small>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="card-body flex-grow-1">
                  <div class="d-flex justify-content-between">
                    <h5 class="card-title"><?= htmlspecialchars($venta['nombre_producto']) ?></h5>
                    <span class="badge <?= badgeColor($venta['estado_de_envio']) ?>">
                      <?= estadoEnvioTexto($venta['estado_de_envio']) ?>
                    </span>
                  </div>
                  
                  <p><strong>Cliente:</strong> <?= htmlspecialchars($venta['nombres'] . ' ' . $venta['apellidos']) ?></p>
                  <p><strong>Cantidad:</strong> <?= htmlspecialchars($venta['cantidad']) ?></p>
                  <p><strong>Precio Unitario:</strong> S/ <?= number_format($venta['precio_unitario'], 2) ?></p>
                  <p><strong>Descuento:</strong> <?= htmlspecialchars($venta['descuento']) ?>%</p>
                  <p><strong>Total:</strong> S/ <?= number_format($venta['cantidad'] * $venta['precio_unitario'] * (1 - $venta['descuento']/100), 2) ?></p>
                  <p><strong>Fecha de Venta:</strong> <?= htmlspecialchars($venta['fecha_venta']) ?></p>
                  <p><strong>Método de Pago:</strong> <?= htmlspecialchars($venta['metodo_pago']) ?></p>

                  <!-- Botones de acción - solo mostrar para estados 1 y 2 -->
                  <?php if ($venta['estado_de_envio'] == 1 || $venta['estado_de_envio'] == 2): ?>
                    <div class="mt-3 d-flex gap-2">
                      <form action="" method="POST">
                        <input type="hidden" name="id_venta" value="<?= $venta['idventa'] ?>">
                        <input type="hidden" name="accion" value="marcar_enviado">
                        <button type="submit" class="btn btn-success btn-sm">Marcar como Enviado</button>
                      </form>
                      <form action="" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?');">
                        <input type="hidden" name="id_venta" value="<?= $venta['idventa'] ?>">
                        <input type="hidden" name="accion" value="cancelar_venta">
                        <button type="submit" class="btn btn-danger btn-sm">Cancelar Venta</button>
                      </form>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($ventas)): ?>
          <div class="col-12">
            <div class="alert alert-warning text-center">No hay ventas activas para mostrar.</div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Cierra automáticamente las alertas después de 5 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);
</script>
</body>
</html>
