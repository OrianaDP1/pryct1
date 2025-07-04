<?php
session_start();
include '../DB/conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$stmtCliente = $con->prepare("SELECT idempresa FROM Empresa_Proveedora WHERE idusuario = :idusuario");
$stmtCliente->bindParam(':idusuario', $idUsuario, PDO::PARAM_INT);
$stmtCliente->execute();
$empresa = $stmtCliente->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    $_SESSION['error'] = "Empresa no encontrada.";
    exit();
}

$_SESSION['empresa_id'] = $empresa['idempresa'];
$empresa_id = $_SESSION['empresa_id'];
$mensaje = '';
$producto = [];
$productos = [];
$categorias = [];
$marcas = [];

try {
    $conexion = $con;

    // Cargar categorías y marcas
    $categorias = $conexion->query("SELECT idcategoria AS id, nombre FROM categoria ORDER BY nombre")
                           ->fetchAll(PDO::FETCH_ASSOC);
    $marcas = $conexion->query("SELECT idmarca AS id, nombre FROM marcas ORDER BY nombre")
                       ->fetchAll(PDO::FETCH_ASSOC);

    // CRUD productos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $categoria_id = $_POST['categoria_id'] ?? null;
        $marca_id = $_POST['marca_id'] ?? null;

        if ($nombre === '') throw new Exception("El nombre del producto es requerido");
        if (!is_numeric($precio) || $precio < 0) throw new Exception("El precio debe ser un número positivo");
        if (!is_numeric($stock) || $stock < 0) throw new Exception("El stock debe ser un número positivo");

       if (empty($id)) {
    // INSERT
    $sql = "INSERT INTO productos 
            (nombre, descripcion, precio, stockactual, idcategoria, idmarca, idempresa)
            VALUES (:nombre, :descripcion, :precio, :stock, :categoria_id, :marca_id, :empresa_id)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':nombre'        => $nombre,
        ':descripcion'   => $descripcion,
        ':precio'        => $precio,
        ':stock'         => $stock,
        ':categoria_id'  => $categoria_id,
        ':marca_id'      => $marca_id,
        ':empresa_id'    => $empresa_id
    ]);
} else {
    // UPDATE
    $sql = "UPDATE productos SET
                nombre = :nombre,
                descripcion = :descripcion,
                precio = :precio,
                stockactual = :stock,
                idcategoria = :categoria_id,
                idmarca = :marca_id
            WHERE idproducto = :id AND idempresa = :empresa_id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':nombre'        => $nombre,
        ':descripcion'   => $descripcion,
        ':precio'        => $precio,
        ':stock'         => $stock,
        ':categoria_id'  => $categoria_id,
        ':marca_id'      => $marca_id,
        ':empresa_id'    => $empresa_id,
        ':id'            => $id
    ]);
}

        header("Location: inventario.php");
        exit();
    }

    // DELETE
    if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
        $id = (int)$_GET['id'];
        $stmt = $conexion->prepare("SELECT idempresa FROM productos WHERE idproducto = :id");
        $stmt->execute([':id' => $id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod || $prod['idempresa'] != $empresa_id) {
            throw new Exception("No tienes permiso para eliminar este producto");
        }
        $stmt = $conexion->prepare("DELETE FROM productos WHERE idproducto = :id AND idempresa = :empresa_id");
        $stmt->execute([':id' => $id, ':empresa_id' => $empresa_id]);
        header("Location: inventario.php");
        exit();
    }

    // Editar producto
    if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
        $id = (int)$_GET['id'];
        $stmt = $conexion->prepare("SELECT * FROM productos WHERE idproducto = :id AND idempresa = :empresa_id");
        $stmt->execute([':id' => $id, ':empresa_id' => $empresa_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (empty($producto)) {
            $mensaje = "Producto no encontrado o sin permiso para editar";
        }
    }

    // Listar productos
    $stmt = $conexion->prepare(
        "SELECT p.idproducto, p.nombre, p.descripcion, p.precio, p.stockactual,
                COALESCE(c.nombre, 'Sin categoría') AS categoria,
                COALESCE(m.nombre, 'Sin marca') AS marca
         FROM productos p
         LEFT JOIN categoria c ON p.idcategoria = c.idcategoria
         LEFT JOIN marcas m ON p.idmarca = m.idmarca
         WHERE p.idempresa = :empresa_id
         ORDER BY p.nombre"
    );
    $stmt->execute([':empresa_id' => $empresa_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h2>Inventario de Productos</h2>
    <?php if ($mensaje): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header"><?= empty($producto) ? 'Agregar Producto' : 'Editar Producto' ?></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($producto['idproducto'] ?? '') ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Precio</label>
                            <input type="number" name="precio" step="0.01" min="0" value="<?= htmlspecialchars($producto['precio'] ?? '') ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Categoría</label>
                            <select name="categoria_id" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= (isset($producto['idcategoria']) && $producto['idcategoria']==$cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Stock</label>
                            <input type="number" name="stock" min="0" value="<?= htmlspecialchars($producto['stockactual'] ?? '') ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Marca</label>
                            <select name="marca_id" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($marcas as $mar): ?>
                                    <option value="<?= $mar['id'] ?>"
                                        <?= (isset($producto['idmarca']) && $producto['idmarca']==$mar['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mar['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary"><?= empty($producto) ? 'Agregar' : 'Actualizar' ?></button>
                <?php if (!empty($producto)): ?>
                    <a href="inventario.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Productos Registrados</div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th><th>Nombre</th><th>Desc.</th><th>Precio</th><th>Stock</th><th>Cat.</th><th>Marca</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($productos): ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><?= $p['idproducto'] ?></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td><?= htmlspecialchars($p['descripcion']) ?></td>
                                <td>$<?= number_format($p['precio'],2) ?></td>
                                <td><?= $p['stockactual'] ?></td>
                                <td><?= htmlspecialchars($p['categoria']) ?></td>
                                <td><?= htmlspecialchars($p['marca']) ?></td>
                                <td>
                                    <a href="?action=edit&id=<?= $p['idproducto'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="?action=delete&id=<?= $p['idproducto'] ?>"
                                       onclick="return confirm('¿Eliminar producto?')"
                                       class="btn btn-sm btn-danger">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No hay productos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
