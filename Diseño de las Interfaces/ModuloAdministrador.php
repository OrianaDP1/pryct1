<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administración</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="ModuloAdministrador.css">
</head>
<body>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar text-white p-3">
        <h4>Admin</h4>
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link text-white" href="#">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Usuarios</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Reportes</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="#">Configuración</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="../Diseno_P_de_Logout/logout.php">Cerrar sesión</a></li>
        </ul>
      </nav>

      <!-- Contenido principal -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <h1 class="mt-4">Bienvenido, Administrador</h1>
        <p>Desde aquí puedes gestionar el sistema.</p>
      </main>
    </div>
  </div>

</body>
</html>
