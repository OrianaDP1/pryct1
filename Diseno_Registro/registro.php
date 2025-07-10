<?php
ob_clean();
header('Content-Type: application/json');
require_once '../DB/conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre     = $_POST['nombre'];
    $correo     = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $telefono   = $_POST['telefono'];
    $direccion  = $_POST['direccion'];
    $nombres    = $_POST['nombres'];
    $apellidos  = $_POST['apellidos'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $nombre_empresa = $_POST['nombre_empresa'] ?? null;
    $ruc            = $_POST['ruc'] ?? null;

    try {
        $con->beginTransaction();

        $stmtUsuario = $con->prepare("INSERT INTO Usuario (Nombre, Contrasena, Correo, Telefono, Direccion) VALUES (?, ?, ?, ?, ?)");
        $stmtUsuario->execute([$nombre, $contrasena, $correo, $telefono, $direccion]);

        $idUsuario = $con->lastInsertId("usuario_idusuario_seq");

        if ($tipo_usuario === 'cliente') {
            $stmtCliente = $con->prepare("INSERT INTO Clientes (Nombres, Apellidos, IDUsuario) VALUES (?, ?, ?)");
            $stmtCliente->execute([$nombres, $apellidos, $idUsuario]);
        } elseif ($tipo_usuario === 'proveedor') {
            $stmtProveedor = $con->prepare("INSERT INTO Empresa_Proveedora (RUC, Nombre, IDUsuario) VALUES (?, ?, ?)");
            $stmtProveedor->execute([$ruc, $nombre_empresa, $idUsuario]);
        } else { throw new Exception("Tipo de usuario inválido"); }

        $con->commit();
        ob_clean();
        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        $con->rollBack();
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        exit;
    }

} else {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}
?>
