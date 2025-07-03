<?php
include '../DB/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    $file = $_FILES['imagen'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $imagenBinaria = file_get_contents($file['tmp_name']);

        // Preparar consulta, ejemplo para actualizar imagen de producto con id 1
        $idproducto = 1; // cambia por el id real

        $stmt = $con->prepare("UPDATE Productos SET Imagen = :imagen WHERE IDProducto = :idproducto");
        $stmt->bindParam(':imagen', $imagenBinaria, PDO::PARAM_LOB);
        $stmt->bindParam(':idproducto', $idproducto, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Imagen guardada correctamente.";
        } else {
            echo "Error al guardar la imagen.";
        }
    } else {
        echo "Error en la subida de archivo.";
    }
}
?>
