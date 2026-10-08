<?php
// API v1 - simple router
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/jwt.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Invoice.php';
require_once __DIR__ . '/controllers/InvoiceController.php';

$db = get_db_connection();
$auth = new AuthController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

// Payload JSON
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Simple routing usando strpos para ser tolerante a subdirectorios
if ($method === 'GET' && strpos($path, '/api/v1') !== false && (substr(rtrim($path, '/'), -7) === '/api/v1' || substr(rtrim($path, '/'), -8) === '/api/v1/')) {
    echo json_encode(['message' => 'API v1 - OK']);
    exit;
}

if ($method === 'POST' && strpos($path, '/api/v1/register') !== false) {
    $auth->register($input);
    exit;
}

if ($method === 'POST' && strpos($path, '/api/v1/login') !== false) {
    $auth->login($input);
    exit;
}

if ($method === 'GET' && strpos($path, '/api/v1/profile') !== false) {
    $user = authenticate_request($db);
    if ($user) echo json_encode(['user' => $user]);
    exit;
}

if ($method === 'GET' && strpos($path, '/api/v1/users') !== false) {
    $user = authenticate_request($db);
    if ($user) {
        if (($user['rol'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }
        $um = new User($db);
        $list = $um->getAll();
        echo json_encode(['users' => $list]);
    }
    exit;
}

// Invoices routes
if (strpos($path, '/api/v1/invoices') !== false) {
    $invController = new InvoiceController($db);
    // POST /api/v1/invoices -> create
    if ($method === 'POST' && strpos($path, '/api/v1/invoices') !== false) {
        $user = authenticate_request($db);
        if ($user) $invController->create($input, $user);
        exit;
    }

    // GET /api/v1/invoices/{id}
    if ($method === 'GET') {
        // intentar extraer ID
        if (preg_match('#/api/v1/invoices/(\d+)$#', rtrim($path, '/'), $m)) {
            $id = (int)$m[1];
            $user = authenticate_request($db);
            if ($user) $invController->get($id, $user);
            exit;
        }

        // GET /api/v1/invoices -> lista
        if (preg_match('#/api/v1/invoices/?$#', $path)) {
            $user = authenticate_request($db);
            if ($user) $invController->list($user);
            exit;
        }
    }

    // si llega aquí, ruta no encontrada para invoices
    http_response_code(404);
    echo json_encode(['error' => 'Ruta de facturas no encontrada']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Ruta no encontrada']);

function get_authorization_header()
{
    // Preferir HTTP_AUTHORIZATION (FastCGI) o REDIRECT_HTTP_AUTHORIZATION; si no, intentar apache_request_headers si existe
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (!empty($headers['Authorization'])) return trim($headers['Authorization']);
        if (!empty($headers['authorization'])) return trim($headers['authorization']);
    }
    return null;
}

function authenticate_request($db)
{
    $authHeader = get_authorization_header();
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(['error' => 'Token requerido']);
        return null;
    }

    if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        if (!defined('JWT_SECRET')) {
            http_response_code(500);
            echo json_encode(['error' => 'JWT secret no configurado']);
            return null;
        }
        $secret = JWT_SECRET;
        $payload = jwt_decode($token, $secret);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido o expirado']);
            return null;
        }
        $userModel = new User($db);
        $user = $userModel->getById($payload->sub ?? $payload->id ?? null);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return null;
        }
        return $user;
    }

    http_response_code(401);
    echo json_encode(['error' => 'Formato de autorización inválido']);
    return null;
}
