<?php
// Simple mysqli connection helper for API v1
function get_db_connection()
{
    $server = "localhost";
    $user = "root";
    $pass = "";
    $db = "db_ssm"; // mantener nombre de la BD existente

    $mysqli = new mysqli($server, $user, $pass, $db);
    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Conexión a base de datos fallida']);
        exit;
    }
    $mysqli->set_charset('utf8');

    // Clave secreta para JWT (cámbiala en producción a una variable de entorno)
    if (!defined('JWT_SECRET')) {
        define('JWT_SECRET', 'cambiar_esta_clave_secreta_por_produccion');
    }

    return $mysqli;
}
