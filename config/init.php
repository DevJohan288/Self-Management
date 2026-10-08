<?php
// ==========================================
// Configuración de conexión MySQLi
// ==========================================
$server = "localhost";
$user = "root";
$pass = "";
$db = "db_ssm";

$conexion = mysqli_connect($server, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Asegurar que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
