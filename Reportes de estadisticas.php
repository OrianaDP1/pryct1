<?php
include_once '../DB/conexion.php';

try {
    // Consultas
    $sql1 = "
        SELECT cat.nombre AS categoria, COUNT(v.idventa) AS total_vendidos
        FROM ventas v
        JOIN productos p ON v.idproducto = p.idproducto
        JOIN categoria cat ON p.idcategoria = cat.idcategoria
        JOIN detalle_ventas dv ON dv.idventa = v.idventa
        WHERE dv.estado_venta = TRUE
        GROUP BY cat.nombre
        ORDER BY total_vendidos DESC
    ";
    $sql2 = "
        SELECT p.nombre, COUNT(v.idventa) AS cantidad_vendida
        FROM ventas v
        JOIN productos p ON v.idproducto = p.idproducto
        JOIN detalle_ventas dv ON dv.idventa = v.idventa
        WHERE dv.estado_venta = TRUE
        GROUP BY p.nombre
        ORDER BY cantidad_vendida DESC
        LIMIT 1
    ";
    $sql3 = "
        SELECT nombre, stockactual
        FROM productos
        ORDER BY stockactual DESC
        LIMIT 1
    ";
    $sql4 = "
        SELECT nombre, fechapublicacion
        FROM productos
        ORDER BY fechapublicacion DESC
        LIMIT 1
    ";
    $sql5 = "
        SELECT m.nombre AS marca, COUNT(v.idventa) AS total_vendidos
        FROM ventas v
        JOIN productos p ON v.idproducto = p.idproducto
        JOIN marcas m ON p.idmarca = m.idmarca
        JOIN detalle_ventas dv ON dv.idventa = v.idventa
        WHERE dv.estado_venta = TRUE
        GROUP BY m.nombre
        ORDER BY total_vendidos DESC
        LIMIT 1
    ";
    $sql6 = "
        SELECT SUM((v.cantidad * v.precio_unitario) - v.descuento) AS total_ventas
        FROM ventas v
        JOIN detalle_ventas dv ON dv.idventa = v.idventa
        WHERE dv.estado_venta = TRUE
    ";

    $stmt1 = $con->query($sql1);
    $stmt2 = $con->query($sql2);
    $stmt3 = $con->query($sql3);
    $stmt4 = $con->query($sql4);
    $stmt5 = $con->query($sql5);
    $stmt6 = $con->query($sql6);

    $porCategoria = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    $masVendido = $stmt2->fetch(PDO::FETCH_ASSOC);
    $mayorStock = $stmt3->fetch(PDO::FETCH_ASSOC);
    $masReciente = $stmt4->fetch(PDO::FETCH_ASSOC);
    $marcaTop = $stmt5->fetch(PDO::FETCH_ASSOC);
    $totalVentas = $stmt6->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Estadístico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4 text-center">📊 Reporte Estadístico General</h1>

    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>1. Productos Vendidos por Categoría</strong>
                </div>
                <div class="card-body">
                    <?php if (count($porCategoria) > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Total Vendidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($porCategoria as $cat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cat['categoria']) ?></td>
                                        <td><?= $cat['total_vendidos'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No hay datos disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">2️⃣ Producto más vendido</h5>
                    <p class="card-text">
                        <strong><?= $masVendido['nombre'] ?? 'Ninguno' ?></strong> con <?= $masVendido['cantidad_vendida'] ?? 0 ?> ventas.
                    </p>
                </div>
            </div>
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">3️⃣ Producto con mayor stock</h5>
                    <p class="card-text">
                        <strong><?= $mayorStock['nombre'] ?? 'Ninguno' ?></strong> con <?= $mayorStock['stockactual'] ?? 0 ?> unidades.
                    </p>
                </div>
            </div>
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">4️⃣ Producto más reciente</h5>
                    <p class="card-text">
                        <strong><?= $masReciente['nombre'] ?? 'Ninguno' ?></strong> publicado el <?= $masReciente['fechapublicacion'] ?? '-' ?>.
                    </p>
                </div>
            </div>
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">5️⃣ Marca con más productos vendidos</h5>
                    <p class="card-text">
                        <strong><?= $marcaTop['marca'] ?? 'Ninguna' ?></strong> con <?= $marcaTop['total_vendidos'] ?? 0 ?> ventas.
                    </p>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">6️⃣ Total estimado de ventas</h5>
                    <p class="card-text text-success fs-5">
                        S/ <?= number_format($totalVentas['total_ventas'] ?? 0, 2) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
