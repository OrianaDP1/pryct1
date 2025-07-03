<?php
session_start();
include '../DB/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (empty($nombre_usuario) || empty($contrasena)) {
        $error = "Nombre o contraseña vacíos";
    } else {

        $stmt = $con->prepare("SELECT idusuario, nombre, contrasena FROM usuario WHERE nombre = ? AND contrasena = ?");
        $stmt->execute([$nombre_usuario, $contrasena]);

        if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['id_usuario'] = $usuario['idusuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];

            // Buscar si es cliente
            $stmt_cliente = $con->prepare("SELECT idcliente FROM clientes WHERE idusuario = ?");
            $stmt_cliente->execute([$usuario['idusuario']]);
            $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

            // Buscar si es empresa
            $stmt_empresa = $con->prepare("SELECT idempresa FROM empresa_proveedora WHERE idusuario = ?");
            $stmt_empresa->execute([$usuario['idusuario']]);
            $empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

            if ($empresa) {
                $_SESSION['tipo_usuario'] = 'proveedor';
                echo "1";
                header('Location: ../Diseño de las Interfaces/ModuloAdministrador.php');
                exit();
            } elseif ($cliente) {
                $_SESSION['tipo_usuario'] = 'cliente';
                echo "2";
                header('Location: ../Diseño de las Interfaces/ModuloCliente.php');
                exit();
            } else {
                $_SESSION['tipo_usuario'] = 'desconocido';
                echo "3";
                header("Location: perfil.php");
                exit();
            }
        } else {
            $error = "Nombre de usuario o contraseña incorrectos";
        }
    }
}
?>
<!-- Mostrar error en el HTML si existe -->
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
