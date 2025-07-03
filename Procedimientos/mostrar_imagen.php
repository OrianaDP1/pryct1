<?php
include '../DB/conexion.php';

if (!isset($_GET['idproducto'])) {
    http_response_code(400);
    exit('Falta el parámetro idproducto');
}

$idproducto = (int) $_GET['idproducto'];

$stmt = $con->prepare("SELECT imagen, tipo_mime FROM Productos WHERE idproducto = :id");
$stmt->execute([':id' => $idproducto]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['imagen'])) {
    http_response_code(404);
    exit('Imagen no encontrada');
}

// Leer el contenido binario desde el recurso
$imageData = is_resource($row['imagen'])
    ? stream_get_contents($row['imagen'])
    : $row['imagen'];

header('Content-Type: ' . ($row['tipo_mime'] ?? 'image/jpeg'));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo $imageData;
exit;
