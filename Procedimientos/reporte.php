<?php
include_once '../DB/conexion.php'; // Asegúrate de que la ruta es correcta

try {
    // Consulta de productos vendidos usando Item_Carrito
    $sql = "
        SELECT 
            p.Nombre AS producto,
            COALESCE(p.Descripcion, '') AS descripcion,
            COUNT(ic.iditem) AS cantidad,
            (COUNT(ic.iditem) * p.Precio) AS total_estimado,
            cc.ultima_actualizacion AS fecha_venta
        FROM item_carrito ic
        JOIN productos p ON ic.idproducto = p.idproducto
        JOIN carrito_compras cc ON ic.idcarrito = cc.idcarrito
        WHERE ic.razon_anulacion IS NULL -- Filtra anulados
        GROUP BY p.Nombre, p.Descripcion, p.Precio, cc.ultima_actualizacion
        ORDER BY cc.ultima_actualizacion DESC
    ";

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al consultar los productos vendidos: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos Vendidos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>

<h2>Reporte de Productos Vendidos</h2>

<?php if (count($ventas) > 0): ?>
<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th>Descripción</th>
            <th>Cantidad Vendida</th>
            <th>Total Estimado</th>
            <th>Fecha de Venta</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventas as $venta): ?>
            <tr>
                <td><?= htmlspecialchars($venta['producto']) ?></td>
                <td><?= htmlspecialchars($venta['descripcion']) ?></td>
                <td><?= $venta['cantidad'] ?></td>
                <td class="total">S/ <?= number_format($venta['total_estimado'], 2) ?></td>
                <td><?= $venta['fecha_venta'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p>No se encontraron productos vendidos.</p>
<?php endif; ?>

</body>
</html>