<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
// Nuevo enrutador para la versión 1 de la API
require_once __DIR__ . '/v1/index.php';
