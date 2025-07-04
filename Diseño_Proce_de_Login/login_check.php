<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'loggedIn' => true,
        'tipo' => $_SESSION['tipo_usuario'] ?? 'desconocido'
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
