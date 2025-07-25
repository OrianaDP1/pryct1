<?php
session_start();
include __DIR__ . '/../DB/conexion.php';

if (isset($_SESSION['id_usuario']) && isset($_SESSION['tipo_usuario'])) {
    // Redirige según tipo de usuario
    if ($_SESSION['tipo_usuario'] === 'cliente') {
        header('Location: ../Diseño de las Interfaces/ModuloCliente.php');
        exit();
    } elseif ($_SESSION['tipo_usuario'] === 'proveedor') {
        header('Location: ../Diseño de las Interfaces/ModuloAdministrador.php');
        exit();
    } else {
        header('Location: perfil.php');
        exit();
    }
}

// Leer JSON POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['usuario']) || !isset($data['contrasena'])) {
    echo json_encode(['success' => false, 'error' => 'Datos no válidos']);
    exit;
}

header('Content-Type: application/json');

$nombre_usuario = trim($data['usuario']);
$contrasena = trim($data['contrasena']);

if ($nombre_usuario === '' || $contrasena === '') {
    echo json_encode(['success' => false, 'error' => 'Usuario o contraseña vacíos']);
    exit;
}

// Aquí debes poner hashing de contraseñas en producción!!! Esto es solo demo.
$stmt = $con->prepare("SELECT idusuario, nombre, contrasena FROM usuario WHERE nombre = :nombre");
$stmt->execute(['nombre' => $nombre_usuario]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
    exit;
}

// Guardar sesión
$_SESSION['id_usuario'] = $usuario['idusuario'];
$_SESSION['nombre_usuario'] = $usuario['nombre'];

// Buscar tipo usuario para redirigir
$stmt_cliente = $con->prepare("SELECT idcliente FROM clientes WHERE idusuario = ?");
$stmt_cliente->execute([$usuario['idusuario']]);
$cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

$stmt_empresa = $con->prepare("SELECT idempresa FROM empresa_proveedora WHERE idusuario = ?");
$stmt_empresa->execute([$usuario['idusuario']]);
$empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

if ($empresa) {
    $_SESSION['tipo_usuario'] = 'proveedor';
    echo json_encode(['success' => true, 'redirect' => '../Diseño de las Interfaces/ModuloEmpresa.php']);
    exit;
} elseif ($cliente) {
    $_SESSION['tipo_usuario'] = 'cliente';
    echo json_encode(['success' => true, 'redirect' => '../Diseño de las Interfaces/ModuloCliente.php']);
    exit;
} else {
    $_SESSION['tipo_usuario'] = 'desconocido';
    echo json_encode(['success' => true, 'redirect' => '../Diseño de las Interfaces/ModuloAdministrador.php']);
    exit;
}
