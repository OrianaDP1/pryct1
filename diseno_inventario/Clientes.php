<?php
session_start();
include '../DB/conexion.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$nombreUsuario = $_SESSION['nombre_usuario'];

// Obtener empresa asociada al usuario
$stmtEmpresa = $con->prepare("SELECT IDEmpresa as id_empresa, Nombre as nombre_empresa FROM Empresa_Proveedora WHERE IDUsuario = ?");
$stmtEmpresa->execute([$idUsuario]);
$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

if (!$empresa || !isset($empresa['id_empresa'])) {
    die("Empresa no encontrada o no tiene permisos.");
}

$idEmpresa = $empresa['id_empresa'];
$nombreEmpresa = $empresa['nombre_empresa'];

// Consulta de clientes
$query = "
    SELECT 
        c.IDCliente,
        c.Nombres,
        c.Apellidos,
        u.Correo,
        u.Telefono,
        MAX(dv.Fecha_Venta) as ultima_compra,
        COUNT(v.IDVenta) as total_compras,
        SUM(v.Precio_Unitario * v.Cantidad) as monto_total
    FROM 
        Clientes c
    JOIN 
        Usuario u ON c.IDUsuario = u.IDUsuario
    LEFT JOIN 
        Ventas v ON c.IDCliente = v.IDCliente
    LEFT JOIN 
        Detalle_Ventas dv ON v.IDVenta = dv.IDVenta
    LEFT JOIN 
        Productos p ON v.IDProducto = p.IDProducto
    WHERE 
        p.IDEmpresa = ? OR p.IDEmpresa IS NULL
    GROUP BY 
        c.IDCliente, c.Nombres, c.Apellidos, u.Correo, u.Telefono
    ORDER BY 
        ultima_compra DESC NULLS LAST
";

$stmt = $con->prepare($query);
$stmt->execute([$idEmpresa]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para calcular días desde última compra
function diasDesdeUltimaCompra($fecha) {
    if (!$fecha) return null;
    return floor((time() - strtotime($fecha)) / (60 * 60 * 24));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes - <?= htmlspecialchars($nombreEmpresa) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .header-report {
            background-color: #343a40; color: white; padding: 20px; border-radius: 5px 5px 0 0;
        }
        .table-responsive {
            background-color: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table thead { background-color: #6c757d; color: white; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        .last-purchase { font-weight: 500; }
        .no-purchases { color: #6c757d; font-style: italic; }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="header-report mb-4">
        <div class="row">
            <div class="col-md-8">
                <h2><i class="fas fa-users me-2"></i>Reporte de Clientes</h2>
                <h4><?= htmlspecialchars($nombreEmpresa) ?></h4>
            </div>
            <div class="col-md-4 text-end">
                <p class="mb-1">Generado: <?= date('d/m/Y H:i') ?></p>
                <p class="mb-1">Usuario: <?= htmlspecialchars($nombreUsuario) ?></p>
                <button onclick="window.print()" class="btn btn-light btn-sm mt-2">
                    <i class="fas fa-print me-1"></i>Imprimir Reporte
                </button>
            </div>
        </div>
    </div>

    <div class="table-responsive p-3">
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Contacto</th>
                <th>Última Compra</th>
                <th>Total Compras</th>
                <th>Monto Total</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($clientes) > 0): ?>
                <?php foreach ($clientes as $index => $cliente): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><strong><?= htmlspecialchars(($cliente['Nombres'] ?? '---') . ' ' . ($cliente['Apellidos'] ?? '')) ?></strong></td>
                        <td>
                            <div><?= htmlspecialchars($cliente['Correo'] ?? '---') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($cliente['Telefono'] ?? '---') ?></small>
                        </td>
                        <td class="last-purchase">
                            <?php if (!empty($cliente['ultima_compra'])): ?>
                                <?= date('d/m/Y H:i', strtotime($cliente['ultima_compra'])) ?>
                            <?php else: ?>
                                <span class="no-purchases">Sin compras registradas</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $cliente['total_compras'] ?? 0 ?></td>
                        <td>S/ <?= number_format($cliente['monto_total'] ?? 0, 2) ?></td>
                        <td>
                            <?php
                            if (!empty($cliente['ultima_compra'])) {
                                $dias = diasDesdeUltimaCompra($cliente['ultima_compra']);
                                if ($dias < 30) {
                                    echo '<span class="badge badge-success">Activo</span>';
                                } elseif ($dias < 90) {
                                    echo '<span class="badge badge-warning">Inactivo</span>';
                                } else {
                                    echo '<span class="badge badge-danger">Inactivo (90+ días)</span>';
                                }
                            } else {
                                echo '<span class="badge badge-secondary">Nuevo</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No se encontraron clientes con compras registradas
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>

            <?php if (count($clientes) > 0): ?>
                <tfoot>
                <tr class="table-active">
                    <td colspan="4" class="text-end"><strong>Totales:</strong></td>
                    <td><strong><?= array_sum(array_column($clientes, 'total_compras')) ?></strong></td>
                    <td><strong>S/ <?= number_format(array_sum(array_column($clientes, 'monto_total')), 2) ?></strong></td>
                    <td></td>
                </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-pie me-2"></i>Resumen de Clientes
                </div>
                <div class="card-body text-center row">
                    <div class="col-4">
                        <h3><?= count($clientes) ?></h3>
                        <small class="text-muted">Total Clientes</small>
                    </div>
                    <div class="col-4">
                        <h3>
                            <?= count(array_filter($clientes, function($c) {
                                return !empty($c['ultima_compra']) && diasDesdeUltimaCompra($c['ultima_compra']) < 30;
                            })) ?>
                        </h3>
                        <small class="text-muted">Clientes Activos</small>
                    </div>
                    <div class="col-4">
                        <h3>
                            <?= count(array_filter($clientes, fn($c) => empty($c['ultima_compra']))) ?>
                        </h3>
                        <small class="text-muted">Clientes Nuevos</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-money-bill-wave me-2"></i>Resumen Financiero
                </div>
                <div class="card-body row text-center">
                    <div class="col-6">
                        <p class="mb-1">Ventas Totales:</p>
                        <h4>S/ <?= number_format(array_sum(array_column($clientes, 'monto_total')), 2) ?></h4>
                    </div>
                    <div class="col-6">
                        <p class="mb-1">Compras Promedio:</p>
                        <h4>S/ <?= count($clientes) > 0 ? number_format(array_sum(array_column($clientes, 'monto_total')) / count($clientes), 2) : '0.00' ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
