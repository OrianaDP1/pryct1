<?php
    $host = "localhost";
    $puerto = "5432";
    $db_nombre = "SVentaCDB";
    $usuario = "postgres";
    $contraseña = "12345";

    try {

        $url = "pgsql:host={$host}; port={$puerto}; dbname={$db_nombre};";
        $con = new PDO($url, $usuario, $contraseña);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $exception) {
        echo "Error de conexión: " . $exception->getMessage();
    }
?>