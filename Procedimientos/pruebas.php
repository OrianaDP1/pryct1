<?php
include '../DB/conexion.php'; // conexión PDO a la base

if (!isset($_GET['idproducto'])) {
    http_response_code(400);
    exit('No ID producto.');
}

$idproducto = (int)$_GET['idproducto'];

// Consulta la imagen y el tipo (puedes almacenar también el MIME si quieres)
$stmt = $con->prepare("SELECT imagen FROM Productos WHERE idproducto = :idproducto");
$stmt->execute([':idproducto' => $idproducto]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || !$row['imagen']) {
    http_response_code(404);
    exit('Imagen no encontrada.');
}

// Aquí asumo que el formato es JPG. Si guardas el mime, usa ese para Content-Type
header("Content-Type: image/jpeg");
header("Content-Length: " . strlen($row['imagen']));

// Envía la imagen directamente
echo $row['imagen'];
exit;
