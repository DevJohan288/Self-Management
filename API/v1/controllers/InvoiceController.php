<?php
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/User.php';

class InvoiceController
{
    private $db;
    private $invoiceModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->invoiceModel = new Invoice($db);
    }

    // Crear factura
    public function create($data, $user)
    {
        // $data: { cliente_id(optional), fecha, items: [...] , cita_id(optional) }
        if (empty($data['fecha']) || empty($data['items']) || !is_array($data['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'fecha e items son requeridos']);
            return;
        }

        $cliente_id = $data['cliente_id'] ?? $user['id'];
        $fecha = $data['fecha'];
        $cita_id = $data['cita_id'] ?? null;

        $created = $this->invoiceModel->createInvoice($cliente_id, $fecha, $data['items'], $cita_id);
        if ($created) {
            http_response_code(201);
            echo json_encode(['message' => 'Factura creada', 'factura' => $created]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear factura']);
        }
    }

    public function get($id, $user)
    {
        $fact = $this->invoiceModel->getById($id);
        if (!$fact) {
            http_response_code(404);
            echo json_encode(['error' => 'Factura no encontrada']);
            return;
        }

        // permitir si es admin o si es factura del cliente
        if (($user['rol'] ?? null) != User::ROLE_ADMIN && $fact['cliente_id'] != $user['id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        echo json_encode(['factura' => $fact]);
    }

    public function list($user)
    {
        if (($user['rol'] ?? null) == User::ROLE_ADMIN) {
            $list = $this->invoiceModel->getAll();
        } else {
            $list = $this->invoiceModel->getAllByCliente($user['id']);
        }
        echo json_encode(['facturas' => $list]);
    }
}
