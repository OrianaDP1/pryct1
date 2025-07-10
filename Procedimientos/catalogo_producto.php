<?php
session_start();
include '../DB/conexion.php'; 
?>

<!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Catálogo de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="ModuloCliente.css" />
    </head>
    <body>

    <header class="bg-dark text-white p-4">
    <div class="container">
        <h1 class="h3 mb-0">Resultados de la búsqueda</h1>
        <a href="../Diseño de las Interfaces/ModuloCliente.php" class="btn btn-light mt-3">Volver</a>
    </div>
    </header>

    <main class="container mt-4">

    <?php
    $buscar = $_GET['buscar'] ?? '';

    if ($buscar === '') {
        echo '<div class="alert alert-warning">Debe ingresar un término para buscar.</div>';
    } else {

        $sql = "SELECT idproducto, nombre, precio FROM Productos 
                WHERE nombre ILIKE :buscar OR descripcion ILIKE :buscar";
        $stmt = $con->prepare($sql);
        $term = '%' . $buscar . '%';
        $stmt->bindParam(':buscar', $term, PDO::PARAM_STR);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$productos) {
            echo '<div class="alert alert-warning">No se encontraron productos relacionados.</div>';
        } else {
            echo '<div class="row row-cols-1 row-cols-md-3 g-4">';
            foreach ($productos as $prod) {
                ?>
                <div class="col">
                <div class="card h-100 product-card shadow-sm">
                    <img src="../Procedimientos/mostrar_imagen.php?idproducto=<?= (int)$prod['idproducto'] ?>" alt="Imagen <?= htmlspecialchars($prod['nombre']) ?>" class="card-img-top" alt="Imagen del producto" />
                    <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($prod['nombre']) ?></h5>
                    <p class="card-text"><strong>Precio:</strong> $<?= htmlspecialchars($prod['precio']) ?></p>
                    <form method="GET" action="../Diseno_Proce_de_PaGeneProductos/productos.php">
                        <input type="hidden" name="idproducto" value="<?= $prod['idproducto'] ?>">
                        <button type="submit" class="btn btn-primary w-100">Ver Detalles</button>
                    </form>
                    </div>
                </div>
                </div>
                <?php
            }
            echo '</div>';
        }
    }
    ?>

    </main>

    <footer class="bg-light text-center py-3 mt-4">
    <p>&copy; 2025 Mi Empresa. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>