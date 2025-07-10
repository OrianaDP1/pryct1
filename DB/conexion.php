<?php
    $host = "localhost";
    $puerto = "5432";
    $db_nombre = "SVentaCDB";
    $usuario = "postgres";
    $contraseña = "123";

    try {

        $url = "pgsql:host={$host}; port={$puerto}; dbname={$db_nombre};";
        $con = new PDO($url, $usuario, $contraseña);
        $con = new PDO($url, $usuario, $contraseña, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

    } catch (PDOException $exception) {
        echo "Error de conexión: " . $exception->getMessage();
        exit;
    }
?>
