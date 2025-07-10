<?php
session_start();
include '../DB/conexion.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_usuario'])) {
    header("Location: ../Diseño_Proce_de_Login/login.html");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$nombreUsuario = $_SESSION['nombre_usuario'];

// Obtener información actual de la empresa
$stmtEmpresa = $con->prepare("SELECT e.*, u.Correo as email_usuario 
                             FROM Empresa_Proveedora e
                             JOIN Usuario u ON e.IDUsuario = u.IDUsuario
                             WHERE e.IDUsuario = ?");
$stmtEmpresa->execute([$idUsuario]);
$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    die("No se encontró información de la empresa asociada a este usuario.");
}

$mensaje = '';
$error = '';
$logoExtensions = ['png', 'jpg', 'jpeg', 'gif'];
$logoExists = false;
$logoUrl = '';
$defaultLogoPath = '../uploads/logos/logo_' . $empresa['idempresa'] . '.';

// Buscar logo actual
foreach ($logoExtensions as $ext) {
    if (file_exists($defaultLogoPath . $ext)) {
        $logoUrl = $defaultLogoPath . $ext . '?v=' . time(); // Cache busting
        $logoExists = true;
        break;
    }
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $con->beginTransaction();

        $nombre = trim($_POST['nombre'] ?? '');
        $ruc = trim($_POST['ruc'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombre) || empty($ruc)) {
            throw new Exception("Nombre y RUC son campos obligatorios.");
        }

        $stmtUpdateEmpresa = $con->prepare("UPDATE Empresa_Proveedora 
                                            SET Nombre = ?, RUC = ?
                                            WHERE IDEmpresa = ?");
        $stmtUpdateEmpresa->execute([$nombre, $ruc, $empresa['idempresa']]);

        if (!empty($email)) {
            $stmtUpdateUsuario = $con->prepare("UPDATE Usuario SET Correo = ? WHERE IDUsuario = ?");
            $stmtUpdateUsuario->execute([$email, $idUsuario]);
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/logos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($logoExtensions as $ext) {
                if (file_exists($defaultLogoPath . $ext)) {
                    unlink($defaultLogoPath . $ext);
                }
            }

            $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $logoExtensions)) {
                throw new Exception("Formato de imagen no permitido.");
            }

            $filename = 'logo_' . $empresa['idempresa'] . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                $logoUrl = $destination . '?v=' . time();
                $logoExists = true;
            }
        }

        $con->commit();
        $mensaje = "Configuración actualizada correctamente.";

        // Recargar empresa
        $stmtEmpresa = $con->prepare("SELECT e.*, u.Correo as email_usuario 
                                     FROM Empresa_Proveedora e
                                     JOIN Usuario u ON e.IDUsuario = u.IDUsuario
                                     WHERE e.IDUsuario = ?");
        $stmtEmpresa->execute([$idUsuario]);
        $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

        // Verificar logo
        foreach ($logoExtensions as $ext) {
            if (file_exists($defaultLogoPath . $ext)) {
                $logoUrl = $defaultLogoPath . $ext . '?v=' . time();
                $logoExists = true;
                break;
            }
        }

    } catch (PDOException $e) {
        $con->rollBack();
        $error = "Error al actualizar la configuración: " . $e->getMessage();
    } catch (Exception $e) {
        $con->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Configuración de Empresa</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" enctype="multipart/form-data" class="border p-4 bg-white rounded shadow-sm mb-4">
        <div class="mb-3">
            <label class="form-label">Nombre de Empresa</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($empresa['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">RUC</label>
            <input type="text" name="ruc" class="form-control" value="<?= htmlspecialchars($empresa['ruc']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Correo Electrónico</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($empresa['email_usuario']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Logo de la Empresa</label><br>
            <?php if ($logoExists): ?>
                <img src="<?= $logoUrl ?>" alt="Logo actual" class="mb-2" style="max-height: 100px;"><br>
            <?php endif; ?>
            <input type="file" name="logo" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>

    <!-- Reporte tipo tabla -->
    <h4 class="mb-3">Resumen de Empresa</h4>
    <table class="table table-bordered table-striped bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID Empresa</th>
                <th>RUC</th>
                <th>Nombre</th>
                <th>ID Usuario</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($empresa['idempresa']) ?></td>
                <td><?= htmlspecialchars($empresa['ruc']) ?></td>
                <td><?= htmlspecialchars($empresa['nombre']) ?></td>
                <td><?= htmlspecialchars($empresa['idusuario']) ?></td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>
