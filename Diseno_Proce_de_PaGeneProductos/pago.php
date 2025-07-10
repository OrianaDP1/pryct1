<?php
$exito = null;
$mensaje = null;

function validar_tarjeta($numero_tarjeta) {
    return preg_match('/^\d{16}$/', $numero_tarjeta);
}

function validar_fecha_vencimiento($fecha_vencimiento) {
    $fecha_actual = date('Y-m');
    return strtotime($fecha_vencimiento) > strtotime($fecha_actual);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_tarjeta = $_POST['numero_tarjeta'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $cvv = $_POST['cvv'];

    if (validar_tarjeta($numero_tarjeta) && validar_fecha_vencimiento($fecha_vencimiento) && is_numeric($cvv) && strlen($cvv) === 3) {
        $_SESSION['pago_autorizado'] = true; // ✅ Flag de que el pago fue válido
        header("Location: comprar_producto.php");
        exit;
    } else {
        $exito = false;
        $mensaje = "Error: Por favor, verifica los datos de la tarjeta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago con Tarjeta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Pago con Tarjeta de Crédito</h3>

                    <?php if ($exito !== null): ?>
                        <div class="alert <?php echo $exito ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="numero_tarjeta" class="form-label">Número de Tarjeta</label>
                            <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 5678 9012 3456" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="month" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                        </div>

                        <div class="mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Procesar Pago</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="javascript:history.back()" class="text-decoration-none">← Volver a la página anterior</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
