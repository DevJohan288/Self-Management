<?php
class Invoice
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // $items = [ [ 'servicio_id'=>1, 'descripcion'=>'', 'cantidad'=>1, 'precio_unitario'=>35.00 ], ... ]
    public function createInvoice($cliente_id, $fecha, $items = [], $cita_id = null)
    {
        // calcular total
        $total = 0.0;
        foreach ($items as $it) {
            $cantidad = isset($it['cantidad']) ? (int)$it['cantidad'] : 1;
            $precio = isset($it['precio_unitario']) ? (float)$it['precio_unitario'] : 0.0;
            $total += $cantidad * $precio;
        }

        $this->db->begin_transaction();
        try {
            $query = "INSERT INTO facturas (cita_id, cliente_id, total, fecha, estado) VALUES (?, ?, ?, ?, 'Pendiente')";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iids', $cita_id, $cliente_id, $total, $fecha);
            if (!$stmt->execute()) throw new Exception('No se pudo crear factura');
            $factura_id = $this->db->insert_id;

            $itemQuery = "INSERT INTO factura_items (factura_id, servicio_id, descripcion, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
            $itStmt = $this->db->prepare($itemQuery);
            foreach ($items as $it) {
                $servicio_id = isset($it['servicio_id']) ? (int)$it['servicio_id'] : null;
                $descripcion = $it['descripcion'] ?? null;
                $cantidad = isset($it['cantidad']) ? (int)$it['cantidad'] : 1;
                $precio = isset($it['precio_unitario']) ? (float)$it['precio_unitario'] : 0.0;
                $subtotal = $cantidad * $precio;
                $itStmt->bind_param('iisiid', $factura_id, $servicio_id, $descripcion, $cantidad, $precio, $subtotal);
                if (!$itStmt->execute()) throw new Exception('No se pudo crear item');
            }

            $this->db->commit();
            return $this->getById($factura_id);
        } catch (Exception $e) {
            $this->db->rollback();
            return null;
        }
    }

    public function getById($id)
    {
        $query = "SELECT f.*, u.nombre as cliente_nombre, u.correo as cliente_correo FROM facturas f LEFT JOIN user u ON f.cliente_id = u.id WHERE f.id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $fact = $stmt->get_result()->fetch_assoc();
        if (!$fact) return null;

        $itemsQ = "SELECT fi.*, s.nombre as servicio_nombre FROM factura_items fi LEFT JOIN servicios s ON fi.servicio_id = s.id WHERE fi.factura_id = ?";
        $itStmt = $this->db->prepare($itemsQ);
        $itStmt->bind_param('i', $id);
        $itStmt->execute();
        $fact['items'] = $itStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return $fact;
    }

    public function getAllByCliente($cliente_id)
    {
        $query = "SELECT * FROM facturas WHERE cliente_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll()
    {
        $query = "SELECT f.*, u.nombre as cliente_nombre FROM facturas f LEFT JOIN user u ON f.cliente_id = u.id ORDER BY f.created_at DESC";
        $res = $this->db->query($query);
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
