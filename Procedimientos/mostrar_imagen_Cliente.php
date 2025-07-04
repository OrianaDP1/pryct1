<?php
include '../DB/conexion.php';

if (!isset($_GET['idusuario'])) {
    http_response_code(400);
    exit('Falta el parámetro idusuario');
}

$idusuario = (int) $_GET['idusuario'];

$stmt = $con->prepare("SELECT imagen, tipo_mime FROM usuario WHERE idusuario = :idusuario");
$stmt->execute([':idusuario' => $idusuario]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['imagen'])) {
    http_response_code(404);
    exit('Imagen no encontrada');
}

$imageData = is_resource($row['imagen'])
    ? stream_get_contents($row['imagen'])
    : $row['imagen'];

$tipoMime = !empty($row['tipo_mime']) ? $row['tipo_mime'] : 'image/jpeg';

header('Content-Type: ' . $tipoMime);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo $imageData;
exit;
