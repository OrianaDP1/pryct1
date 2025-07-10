<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administración de Empresa</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="ModuloEmpresa.css">
</head>
<body>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block sidebar text-white p-3">
        <div class="sidebar-sticky">
          <h4 class="text-center mb-4">Administrador Empresa</h4>
          <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link text-white" href="../Diseño de las Interfaces/ModuloEmpresa.php" data-content="home">Inicio</a></li>
            <li class="nav-item"><a class="nav-link text-white active" href="../diseno_inventario/inventario.php" data-content="inventario">Inventario</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../diseno_inventario/ventas_empresa.php" data-content="ventas">Ventas</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../diseno_inventario/clientes.php" data-content="clientes">Clientes</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../diseno_inventario/Reportes de estadisticas.php" data-content="reportes">Reportes</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../diseno_inventario/configuraciones.php" data-content="configuracion">Configuración</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-white bg-danger rounded" href="../Diseno_P_de_Logout/logout.php">Cerrar sesión</a></li>
          </ul>
        </div>
      </nav>

      <!-- Contenido principal -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <h1 class="mt-4">Bienvenido, Administrador de Empresa</h1>
        <p>Desde este panel puedes gestionar empleados, ventas, inventario y más.</p>
      </main>
    </div>
  </div>

</body>
</html>