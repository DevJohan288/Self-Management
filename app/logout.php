<?php
// Carga las funciones de autenticación (archivo en includes/)
require_once __DIR__ . '/includes/auth.php';

// Sólo aceptar logout vía POST para evitar CSRF por GET accidental
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Self-Management/index.php');
    exit();
}

// Asegura que la sesión esté activa antes de cerrarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ejecuta la función logout() definida en includes/auth.php
logout();

// Invalida la cookie de sesión (por seguridad)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Redirige al login (index.php en la raíz del proyecto)
header('Location: /Self-Management/index.php');
exit();
?>
