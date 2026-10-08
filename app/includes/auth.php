<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Función para loguear al usuario
function loginUser($db, $correo, $password) {
    $user = null;
    if ($db instanceof PDO) {
        $stmt = $db->prepare("SELECT * FROM user WHERE correo = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); 

    // Si es mysqli (objeto o conexión procedimental)
    } elseif ($db instanceof mysqli || (is_resource($db) || is_object($db))) {
        // Soportar tanto mysqli object como conexión procedimental
        if ($db instanceof mysqli) {
            $stmt = $db->prepare("SELECT id, nombre, correo, password, rol FROM user WHERE correo = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $correo);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res ? $res->fetch_assoc() : null;
            }
        } else {
            // Procedural mysqli link
            $query = sprintf("SELECT id, nombre, correo, password, rol FROM user WHERE correo = '%s' LIMIT 1", mysqli_real_escape_string($db, $correo));
            $result = mysqli_query($db, $query);
            $user = $result ? mysqli_fetch_assoc($result) : null;
        }
    }

    if (!$user) return false;

    // Verificación de contraseña (soporta texto plano temporalmente)
    if (password_verify($password, $user['password']) || $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol'];
        return true;
    }

    return false;
}

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para requerir un rol específico
function requireRole($role) {
    if (!isLoggedIn()) {
        header("Location: /Self-Management/app/views/auth/login.php");
        exit;
    }

    $roleMap = [
        'admin' => 1,
        'empleado' => 2,
        'cliente' => 3
    ];

    $requiredRoleId = $roleMap[$role] ?? null;
    
    if ($requiredRoleId === null || !isset($_SESSION['user_role']) || 
        intval($_SESSION['user_role']) !== $requiredRoleId) {
        header("Location: /Self-Management/index.php");
        exit;
    }
}

// Cerrar sesión
function logout() {
    session_unset();
    session_destroy();
}

function getCurrentUser()
{
    global $conexion;
    if (!isLoggedIn()) return null;

    $id = $_SESSION['user_id'];
    $query = "SELECT id, nombre, correo, rol FROM `user` WHERE id = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return null;

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_assoc() : null;
}
?>
