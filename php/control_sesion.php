<?php
// control_sesion.php
// Este archivo se incluye al inicio de CADA página protegida (bienvenida.php, etc.)
// en lugar de session_start() + validación manual.

session_start();

define('TIEMPO_BLOQUEO', 150);  // 2.5 minutos en segundos
define('TIEMPO_LOGOUT', 300);   // 5 minutos en segundos

// Si no hay sesión iniciada, mandamos al login
if (!isset($_SESSION['usuario'])) {
    echo '
        <script>
        alert("Debes de Iniciar Sesion");
        window.location = "index.php";
        </script>
    ';
    session_destroy();
    die();
}

$ahora  = time();
$ultima = isset($_SESSION['ultima_actividad']) ? $_SESSION['ultima_actividad'] : $ahora;
$inactivo = $ahora - $ultima;

// 5 minutos o más de inactividad -> cierre total de sesión
if ($inactivo >= TIEMPO_LOGOUT) {
    session_unset();
    session_destroy();
    header("location: ../index.php?motivo=expirada");
    exit;
}

// Entre 2.5 y 5 minutos -> se marca como bloqueada (no se destruye la sesión)
if ($inactivo >= TIEMPO_BLOQUEO) {
    $_SESSION['bloqueada'] = true;
} else {
    // Sigue activo dentro del margen: refrescamos el contador
    $_SESSION['ultima_actividad'] = $ahora;
    $_SESSION['bloqueada'] = false;
}
?>
