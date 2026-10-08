<?php
class User
{
    private $db;
    
    const ROLE_ADMIN = 1;
    const ROLE_EMPLEADO = 2;
    const ROLE_CLIENTE = 3;
    
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUser($nombre, $correo, $password, $telefono = null, $rol = self::ROLE_CLIENTE)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO user (nombre, correo, password, telefono, rol) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssssi', $nombre, $correo, $hash, $telefono, $rol);
        if ($stmt->execute()) {
            return $this->getById($this->db->insert_id);
        }
        return null;
    }

    public function getByEmail($correo)
    {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM user u 
                  LEFT JOIN roles r ON u.rol = r.id 
                  WHERE u.correo = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        if ($user) {
            // No enviar el hash de la contraseña
            unset($user['password']);
        }
        return $user;
    }

    public function getById($id)
    {
        $query = "SELECT u.id, u.nombre, u.correo, u.telefono, u.rol, r.nombre as rol_nombre 
                  FROM user u 
                  LEFT JOIN roles r ON u.rol = r.id 
                  WHERE u.id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public function validateCredentials($correo, $password)
    {
        $query = "SELECT * FROM user WHERE correo = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) return null;
        
        // Si la contraseña está en texto plano (migración pendiente)
        if ($user['password'] === $password || password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

    public function getAll()
    {
        $query = "SELECT u.id, u.nombre, u.correo, u.telefono, u.rol, r.nombre as rol_nombre 
                  FROM user u 
                  LEFT JOIN roles r ON u.rol = r.id";
        $res = $this->db->query($query);
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
