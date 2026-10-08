<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/app/includes/auth.php';
require_once __DIR__ . '/app/includes/funtions.php';

// ==========================================
// Verificar si el usuario ya está logueado
// ==========================================
if (isLoggedIn()) {
    switch ($_SESSION['user_role']) {
        case 1: header('Location: /Self-Management/app/views/admin/admin_dashboard.php'); exit;
        case 2: header('Location: /Self-Management/app/views/employeer/emply_dashboard.php'); exit;
        case 3: header('Location: /Self-Management/app/views/client/client_dashboard.php'); exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

        if (loginUser($conexion, $correo, $password)) {
        switch ($_SESSION['user_role']) {
            case 1: header('Location: /Self-Management/app/views/admin/admin_dashboard.php'); exit;
            case 2: header('Location: /Self-Management/app/views/employeer/emply_dashboard.php'); exit;
            case 3: header('Location: /Self-Management/app/views/client/client_dashboard.php'); exit;
        }
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css" />
    <link rel="stylesheet" href="/Self-Management/public/css/login_style.css" />
    <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png" />
    <script src="/Self-Management/public/js/icon-theme.js" defer></script>
</head>
<body>
    <main class="auth-container" id="login">
        <div class="logo-container">
            <img src="/Self-Management/public/images/large_lg-light.png" alt="Logo" />
        </div>

        <form method="POST">
            <div class="form-field">
                <input class="form-input" type="text" id="correo" name="correo" required placeholder=" " />
                <label class="input-label" for="correo">Correo electrónico</label>
            </div>

            <div class="form-field">
                <input class="form-input" type="password" id="password" name="password" required placeholder=" " />
                <label class="input-label" for="password">Contraseña</label>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert"><?= $error ?></div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Ingresar</button>

            <div class="form-link">
                <p>
                    ¿No tienes cuenta?
                    <a href="/Self-Management/app/views/auth/register_log.php">Regístrate</a>
                </p>
                <a href="/Self-Management/app/views/auth/forgot_password.php">Olvidé contraseña</a>
            </div>
        </form>
    </main>
</body>
</html>
