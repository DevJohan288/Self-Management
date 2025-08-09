<?php
class AppointmentModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        return $this->db->query("SELECT * FROM citas");
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM citas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($fecha, $hora, $cliente, $servicio, $vehiculo, $comentario)
    {
        $stmt = $this->db->prepare("INSERT INTO citas (fecha, hora, cliente, servicio, vehiculo, comentario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fecha, $hora, $cliente, $servicio, $vehiculo, $comentario);
        return $stmt->execute();
    }


    public function update($id, $fecha, $hora, $cliente)
    {
        $stmt = $this->db->prepare("UPDATE citas SET fecha = ?, hora = ?, cliente = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fecha, $hora, $cliente, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM citas WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
