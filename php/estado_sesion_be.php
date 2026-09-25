<?php
// estado_sesion_be.php
// El JavaScript del navegador llama a este archivo cada 5 segundos
// para saber si la sesión sigue activa, está bloqueada, o expiró.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['estado' => 'expirada']);
    exit;
}

$ahora  = time();
$ultima = isset($_SESSION['ultima_actividad']) ? $_SESSION['ultima_actividad'] : $ahora;
$inactivo = $ahora - $ultima;

// PRUEBA: 20 segundos -> cierre total (luego regresar a 300)
if ($inactivo >= 20) {
    session_unset();
    session_destroy();
    echo json_encode(['estado' => 'expirada']);
    exit;
}

// PRUEBA: 10 segundos -> bloqueada (luego regresar a 150)
if ($inactivo >= 10) {
    echo json_encode(['estado' => 'bloqueada']);
    exit;
}

// Todo normal
echo json_encode(['estado' => 'activa']);
