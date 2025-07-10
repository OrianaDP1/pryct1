<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

// Mapeo de estados y sus valores en BD
$estados = [
    1 => 'En proceso',
    2 => 'Enviado',
    3 => 'Entregado'
];

// Verificamos que recibimos el id de venta
if (!isset($_GET['id_venta'])) {
    die("No se especificó la venta.");
}

$idVenta = intval($_GET['id_venta']);
$error = '';
$mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['estado_envio']) && array_key_exists($_POST['estado_envio'], $estados)) {
        $nuevoEstado = intval($_POST['estado_envio']);
        try {
            $sql = "UPDATE Detalle_Ventas SET Estado_de_Envio = :estado WHERE IDVenta = :idVenta";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_INT);
            $stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje = "Estado actualizado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al actualizar estado: " . $e->getMessage();
        }
    } else {
        $error = "Estado inválido.";
    }
}

// Obtener el estado actual de la venta para seleccionar el combo box
try {
    $sql = "SELECT Estado_de_Envio FROM Detalle_Ventas WHERE IDVenta = :idVenta";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
    $stmt->execute();
    $estadoActual = $stmt->fetchColumn();

    if ($estadoActual === false) {
        die("Venta no encontrada.");
    }
} catch (PDOException $e) {
    die("Error al consultar estado actual: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Estado de Envío</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Actualizar Estado de Envío - Venta #<?= htmlspecialchars($idVenta) ?></h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="estado_envio" class="form-label">Estado de Envío</label>
            <select name="estado_envio" id="estado_envio" class="form-select" required>
                <?php foreach ($estados as $valor => $texto): ?>
                    <option value="<?= $valor ?>" <?= ($valor == $estadoActual) ? 'selected' : '' ?>>
                        <?= $texto ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Estado</button>
        <a href="nombre_de_tu_pagina_de_ventas.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
