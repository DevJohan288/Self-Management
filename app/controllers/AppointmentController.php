<?php
require_once __DIR__ . '/../models/AppointmentModel.php';

class AppointmentController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new AppointmentModel($db);
    }

    public function index()
    {
        $appointments = $this->model->getAll();
        include __DIR__ . '/../views/client/client_my_appointments.php'; // Assuming this view lists all appointments
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fecha = $_POST['fecha'] ?? null;
            $hora = $_POST['hora'] ?? null;
            $cliente = $_POST['cliente'] ?? null;
            $servicio = $_POST['servicio'] ?? null;
            $vehiculo = $_POST['vehiculo'] ?? null;
            $comentario = $_POST['comentario'] ?? '';

            if ($fecha && $hora && $cliente && $servicio && $vehiculo) {
                $this->model->create($fecha, $hora, $cliente, $servicio, $vehiculo, $comentario);

                // Redirige al historial de citas
                header('Location: /Self-Management/index.php?controller=appointment&action=history');
                exit;
            } else {
                $error = "Todos los campos obligatorios deben completarse.";
                $appointments = $this->model->getAll();
                include __DIR__ . '/../views/client/client_my_appointments.php'; // Show the form with error
            }
        }
    }

    public function history()
    {
        $appointments = $this->model->getAll();
        include __DIR__ . '/../views/client/client_my_appointments.php'; // Assuming this view lists all appointments
    }


    // public function edit()
    // {
    //     $id = $_GET['id'] ?? null;

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $this->model->update($id, $_POST['fecha'], $_POST['hora'], $_POST['cliente']);
    //         header('Location: /Self-Management/index.php?controller=appointment&action=index');
    //     } else {
    //         $appointment = $this->model->getById($id);
    //         include __DIR__ . '/../views/admin/edit_appointment.php';
    //     }
    // }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            if ($id) {
                $this->model->delete($id);
            }
        }

        // Redirigir nuevamente al historial
        header('Location: /Self-Management/index.php?controller=appointment&action=history');
        exit;
    }
}
