<?php
  include '../DB/conexion.php';
  $productos = [];

  if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
    if ($busqueda !== '') {
      $like = "%$busqueda%";
      $stmt = $con->prepare("SELECT Nombre, Precio FROM Productos WHERE Nombre ILIKE :buscar");
      $stmt->execute(['buscar' => $like]);
      $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  }
?>