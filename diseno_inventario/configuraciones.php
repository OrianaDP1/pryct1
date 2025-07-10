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

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $con->beginTransaction();

        // Validar y sanitizar datos
        $nombre = trim($_POST['nombre'] ?? '');
        $ruc = trim($_POST['ruc'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombre) || empty($ruc)) {
            throw new Exception("Nombre y RUC son campos obligatorios.");
        }

        // Actualizar datos de la empresa
        $stmtUpdateEmpresa = $con->prepare("UPDATE Empresa_Proveedora 
                                          SET Nombre = ?, RUC = ?, Direccion = ?, Telefono = ?
                                          WHERE IDEmpresa = ?");
        $stmtUpdateEmpresa->execute([
            $nombre,
            $ruc,
            $direccion,
            $telefono,
            $empresa['IDEmpresa']
        ]);

        // Actualizar email del usuario
        if (!empty($email)) {
            $stmtUpdateUsuario = $con->prepare("UPDATE Usuario SET Correo = ? WHERE IDUsuario = ?");
            $stmtUpdateUsuario->execute([$email, $idUsuario]);
        }

        // Manejo de imagen de logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logo = file_get_contents($_FILES['logo']['tmp_name']);
            $tipo_mime = $_FILES['logo']['type'];
            
            $stmtUpdateLogo = $con->prepare("UPDATE Empresa_Proveedora 
                                            SET Logo = ?, TipoMimeLogo = ?
                                            WHERE IDEmpresa = ?");
            $stmtUpdateLogo->execute([$logo, $tipo_mime, $empresa['IDEmpresa']]);
        }

        $con->commit();
        $mensaje = "Configuración actualizada correctamente.";
        
        // Actualizar datos en variable de sesión
        $_SESSION['nombre_usuario'] = $nombreUsuario;
        
        // Recargar datos de la empresa
        $stmtEmpresa->execute([$idUsuario]);
        $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $con->rollBack();
        $error = "Error al actualizar la configuración: " . $e->getMessage();
    } catch (Exception $e) {
        $con->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .config-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 5px;
            background-color: #f8f9fa;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-submit {
            background-color: #6c63ff;
            border: none;
            padding: 10px 25px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background-color: #5a52d5;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="config-container">
            <h2 class="mb-4"><i class="fas fa-cog me-2"></i>Configuración de la Empresa</h2>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Empresa</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($empresa['Nombre'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" class="form-control" id="ruc" name="ruc" 
                                   value="<?= htmlspecialchars($empresa['RUC'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= htmlspecialchars($empresa['Direccion'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?= htmlspecialchars($empresa['Telefono'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email de Contacto</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($empresa['email_usuario'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo de la Empresa</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12 text-center">
                        <?php if (!empty($empresa['Logo'])): ?>
                            <img src="data:<?= htmlspecialchars($empresa['TipoMimeLogo']) ?>;base64,<?= base64_encode($empresa['Logo']) ?>" 
                                 class="logo-preview mb-3" id="logoPreview">
                        <?php else: ?>
                            <div class="logo-preview mb-3 d-flex align-items-center justify-content-center" id="logoPreview">
                                <i class="fas fa-building text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-submit text-white">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Vista previa del logo seleccionado
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('logoPreview');
                    preview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'logo-preview';
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>