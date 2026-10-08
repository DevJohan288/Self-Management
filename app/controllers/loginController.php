<?php
require_once __DIR__ . '/../models/UserModel.php';

class LoginController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new UserModel($db);
    }

    public function login($correo, $password)
    {
        $user = $this->userModel->validateLogin($correo, $password);

        if ($user) {
            session_start();
            $_SESSION['id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            return $user['rol'] == 1
                ? '/Self-Management/app/views/admin/admin_home.php'
                : '/Self-Management/app/views/client/client_home.php';
        }

        return false;
    }
    // public function validar()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $correo = $_POST['correo'] ?? '';
    //         $password = $_POST['password'] ?? '';

    //         $ruta = $this->login($correo, $password);

    //         if ($ruta) {
    //             header("Location: $ruta");
    //             exit;
    //         } else {
    //             session_start();
    //             $_SESSION['error'] = "Correo o contraseña incorrectos.";
    //             header("Location: /Self-Management/app/views/auth/login.php"); // ← redirigir al formulario
    //             exit;
    //         }
    //     } else {
    //         echo "Método no permitido.";
    //     }
    // }
}
