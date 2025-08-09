<?php
require_once 'app/models/vehicleModel.php';

class VehicleController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new VehicleModel($db);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener datos del formulario
            $marca = $_POST['marca'] ?? '';
            $modelo = $_POST['modelo'] ?? '';
            $placa = $_POST['placa'] ?? '';
            $color = $_POST['color'] ?? '';
            $anio = $_POST['anio'] ?? '';
            $tipo = $_POST['tipo'] ?? '';
            $cliente_id = $_SESSION['user_id'] ?? 0;

            // Obtener la URL de retorno del formulario
            $return_url = $_POST['return_url'] ?? '';

            // Si no hay URL de retorno, usar una URL por defecto
            if (empty($return_url)) {
                $return_url = '/Self-Management/index.php?controller=client&action=myVehicles';
            }

            // Validación básica
            if (empty($marca) || empty($modelo) || empty($placa) || empty($color) || empty($anio) || empty($tipo)) {
                $_SESSION['error'] = "Todos los campos son requeridos";
            } else {
                // Intentar guardar el vehículo
                $resultado = $this->model->create($marca, $modelo, $placa, $color, $anio, $tipo, $cliente_id);

                if ($resultado) {
                    $_SESSION['success'] = "Vehículo registrado exitosamente";
                } else {
                    $_SESSION['error'] = "Error al registrar el vehículo";
                }
            }

            // Asegurar que la URL de retorno sea válida
            if (
                !filter_var($return_url, FILTER_VALIDATE_URL) &&
                !str_starts_with($return_url, '/')
            ) {
                $return_url = '/Self-Management/index.php?controller=client&action=myVehicles';
            }

            header('Location: ' . $return_url);
            exit();
        }
    }

    public function listarTodos()
    {
        $vehiculos = $this->model->getAll();
        include __DIR__ . '/../views/client/client_my_vehicles.php';
    }
}
