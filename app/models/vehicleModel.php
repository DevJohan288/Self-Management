<?php
class VehicleModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM vehiculos";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($marca, $modelo, $placa, $color, $anio, $tipo, $cliente_id)
    {
        $stmt = $this->db->prepare("INSERT INTO vehiculos (marca, modelo, placa, color, año, tipo, cliente_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $marca, $modelo, $placa, $color, $anio, $tipo, $cliente_id);
        return $stmt->execute();
    }

    public function getByClientId($cliente_id)
    {
        $query = "SELECT * FROM vehiculos WHERE cliente_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM vehiculos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
