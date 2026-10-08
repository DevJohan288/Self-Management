<?php
// Cargar configuración central (ruta absoluta desde document root)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';

// Validar rol de administrador
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
requireRole('admin');
// ==========================================
// Obtener mecánicos (rol = 2 = empleado/mecánico) con estadísticas desde `citas.mecanico_id`
$query = "
    SELECT 
        u.id, 
        u.nombre, 
        u.correo, 
        u.telefono,
        COUNT(c.id) AS total_citas,
        SUM(CASE WHEN c.estado = 'Completada' THEN 1 ELSE 0 END) AS citas_completadas
    FROM user u
    LEFT JOIN citas c ON u.id = c.mecanico_id
    WHERE u.rol = 2
    GROUP BY u.id, u.nombre, u.correo, u.telefono
    ORDER BY u.nombre ASC
";
$mecanicos = [];
$res = mysqli_query($conexion, $query);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        // Asegurar valores numéricos
        $row['total_citas'] = intval($row['total_citas'] ?? 0);
        $row['citas_completadas'] = intval($row['citas_completadas'] ?? 0);
        $mecanicos[] = $row;
    }
    mysqli_free_result($res);
} else {
    // En desarrollo registrar el error si es necesario
    // error_log('Error al obtener mecanicos: ' . mysqli_error($conexion));
    $mecanicos = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de tecnicos</title>
    <!-- Libraries -->
    <!-- Bootstrap 5 JS y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/login_style.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
    <!-- Favicon/images -->
    <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png">
    <!-- JS Scripts -->
    <script src="/Self-Management/public/js/icon-theme.js"></script>
</head>

<body>
    <!--Include componenet: Sidebar -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_admin.php'; ?>
    <main>
        <div class="text-content">
            <h2>Gestión de Técnicos</h2>
            <div class="button-container">
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">Agregar tecnico
                </button>
            </div>
        </div>

                    
            <div class="card">
                <p><strong>Nota:</strong> Para agregar un nuevo mecánico, ve a la sección de <a href="usuarios.php">Usuarios</a> y crea un usuario con rol "Mecánico".</p>
            </div>

        <!-- Modal con formulario -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <form id="popupForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalLabel">Formulario de tecnico</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Id</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Nombre</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Especialidad</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Dispoblidad</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="stats-grid">
                <?php if (count($mecanicos) > 0): ?>
                    <?php foreach ($mecanicos as $m): ?>
                        <div class="card">
                            <h3>🔧 <?php echo htmlspecialchars($m['nombre']); ?></h3>
                            <p><strong>Correo:</strong> <?php echo htmlspecialchars($m['correo']); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($m['telefono']); ?></p>
                            <hr>
                            <div class="stat-small">
                                <span class="stat-label">Total Citas:</span>
                                <span class="stat-value"><?php echo $m['total_citas']; ?></span>
                            </div>
                            <div class="stat-small">
                                <span class="stat-label">Completadas:</span>
                                <span class="stat-value"><?php echo $m['citas_completadas']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <p>No hay mecánicos registrados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="container">
            <div class="search-filter-container">
                <input type="text" id="search-bar" placeholder="Buscar..." />
                <!-- Filter: Otros -->
                <select id="sort-filter">
                    <option value="recientes">Todos</option>
                    <option value="populares">Pendientes</option>
                    <option value="mayor-precio">En proceso</option>
                    <option value="menor-precio">Finalizada</option>
                </select>
            </div>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Especialidad</th>
                        <th>Disponibilidad</th>
                        <th>Cliente Asignado</th>
                        <th>Servicio Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#003</td>
                        <td>Carlos Gómez</td>
                        <td>Plomería</td>
                        <td>Disponible</td>
                        <td>Cliente C</td>
                        <td>Mantenimiento de tuberías</td>
                        <td>
                            <button class="btn btn-primary btn-sm">Reasignar</button>
                            <button class="btn btn-warning btn-sm">Editar</button>
                            <button class="btn btn-danger btn-sm">Cancelar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="user-info">
                <span><?php echo $_SESSION['user_name']; ?> (Admin)</span>
            </div>
    </main>
    <!-- JS para el formulario del modal -->
    <script>
        document.getElementById('popupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí puedes procesar los datos del formulario
            alert('Formulario enviado');
            const modal = bootstrap.Modal.getInstance(document.getElementById('formModal'));
            modal.hide(); // Cierra el modal después de enviar
        });
    </script>
</body>

</html>


<!-- Include componenet: Footer 
 Form para agregar tectnico
 Nombre: name
 Escialidades: electricos electricos
 Dispoblidad: horion-->


<!-- Reasignar el mecanico 
  mediante un modal al prulsar el boton me muetra al cleinte al que es reasinado-->