<?php
include '../DB/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen']) && isset($_POST['idusuario'])) {
    $file = $_FILES['imagen'];
    $idusuario = (int) $_POST['idusuario'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $imagenBinaria = file_get_contents($file['tmp_name']);
        $tipoMime = $file['type']; // tipo MIME enviado por el navegador

        $stmt = $con->prepare("UPDATE usuario SET imagen = :imagen, tipo_mime = :tipo_mime WHERE idusuario = :idusuario");
        $stmt->bindParam(':imagen', $imagenBinaria, PDO::PARAM_LOB);
        $stmt->bindParam(':tipo_mime', $tipoMime);
        $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Imagen guardada correctamente.";
        } else {
            echo "Error al guardar la imagen.";
        }
    } else {
        echo "Error en la subida de archivo.";
    }
} else {
    echo "Método no permitido o datos incompletos.";
}
