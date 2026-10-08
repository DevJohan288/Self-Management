<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/jwt.php';

class AuthController
{
    private $userModel;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->userModel = new User($db);
    }

    public function register($data)
    {
        if (!isset($data['nombre'], $data['correo'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'nombre, correo y password son requeridos']);
            return;
        }

        // validar existencia
        if ($this->userModel->getByEmail($data['correo'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Correo ya registrado']);
            return;
        }

        $telefono = $data['telefono'] ?? null;
        $rol = $data['rol'] ?? User::ROLE_CLIENTE;

        $user = $this->userModel->createUser(
            $data['nombre'],
            $data['correo'],
            $data['password'],
            $telefono,
            $rol
        );

        if ($user) {
            http_response_code(201);
            echo json_encode(['message' => 'Usuario creado', 'user' => $user]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear usuario']);
        }
    }

    public function login($data)
    {
        if (!isset($data['correo'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'correo y password requeridos']);
            return;
        }

        $user = $this->userModel->validateCredentials($data['correo'], $data['password']);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
            return;
        }

        $payload = [
            'sub' => $user['id'],
            'email' => $user['correo'],
            'rol' => $user['rol']
        ];

        $token = jwt_encode($payload, JWT_SECRET, 3600*24); // 24h

        echo json_encode([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'correo' => $user['correo'],
                'telefono' => $user['telefono'],
                'rol' => $user['rol'],
                'rol_nombre' => $user['rol_nombre']
            ]
        ]);
    }
}
