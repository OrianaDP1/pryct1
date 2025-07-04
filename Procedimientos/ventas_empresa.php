<?php

include '../DB/conexion.php';

try {
    $sql = "
        SELECT v.Cantidad, v.Precio_Unitario, v.Descuento, d.Fecha_Venta, d.Metodo_Pago, d.Estado_de_Envio, p.Nombre AS nombre_producto, p.Imagen AS imagen_producto, p.tipo_mime, c.Nombres, c.Apellidos
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
    <link rel="stylesheet" href="../Diseño de las Interfaces/ventas_empresa.css">
    </head>
    <body>
    <div class="container mt-4">
        <h2>Ventas de la Empresa</h2>
        <div class="row">
            
            <?php foreach ($ventas as $venta): ?>
                <div class="col-md-8 mb-4">
                    <div class="card sale-card">
                        <div class="d-flex">
                            <div class="product-img">
                                <?php if ($venta['imagen_producto']): ?>
                                    <img src="data:<?= $venta['tipo_mime'] ?>;base64,<?= base64_encode($venta['imagen_producto']) ?>" alt="Imagen Producto">
                                <?php else: ?>
                                    <p>Sin imagen</p>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($venta['nombre_producto']) ?></h5>
                                <p><strong>Cliente:</strong> <?= htmlspecialchars($venta['nombres'] . ' ' . $venta['apellidos']) ?></p>
                                <p><strong>Cantidad:</strong> <?= htmlspecialchars($venta['cantidad']) ?></p>
                                <p><strong>Precio Unitario:</strong> S/ <?= number_format($venta['precio_unitario'], 2) ?></p>
                                <p><strong>Descuento:</strong> <?= htmlspecialchars($venta['descuento']) ?>%</p>
                                <p><strong>Fecha de Venta:</strong> <?= htmlspecialchars($venta['fecha_venta']) ?></p>
                                <p><strong>Método de Pago:</strong> <?= htmlspecialchars($venta['metodo_pago']) ?></p>
                                <p><strong>Estado de Envío:</strong> <?= estadoEnvioTexto($venta['estado_de_envio']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($ventas)): ?>
                <div class="col-12">
                    <p class="text-center">No hay ventas en estado "Preparando" o "Enviando".</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </body>
</html>
