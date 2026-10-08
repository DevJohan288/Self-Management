<?php
// Cargar configuración central (ruta absoluta desde document root)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';

// Validar rol de administrador
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
requireRole('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/funtions.php';

$mensaje = '';

// Comprobar y crear columnas relacionales si no existen (cliente_id, mecanico_id, servicio_id)
$has_cliente_id = (mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'cliente_id'")) > 0);
$has_mecanico_id = (mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'mecanico_id'")) > 0);
$has_servicio_id = (mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'servicio_id'")) > 0);

if (!$has_cliente_id) {
    $ok = mysqli_query($conexion, "ALTER TABLE citas ADD COLUMN cliente_id INT NULL AFTER cliente");
    if ($ok) { $mensaje .= showSuccess('Columna `cliente_id` añadida a `citas`. '); $has_cliente_id = true; }
    else { $mensaje .= showError('No se pudo añadir `cliente_id`: ' . mysqli_error($conexion)); }
}
if (!$has_mecanico_id) {
    $ok = mysqli_query($conexion, "ALTER TABLE citas ADD COLUMN mecanico_id INT NULL AFTER vehiculo");
    if ($ok) { $mensaje .= showSuccess('Columna `mecanico_id` añadida a `citas`. '); $has_mecanico_id = true; }
    else { $mensaje .= showError('No se pudo añadir `mecanico_id`: ' . mysqli_error($conexion)); }
}
if (!$has_servicio_id) {
    $ok = mysqli_query($conexion, "ALTER TABLE citas ADD COLUMN servicio_id INT NULL AFTER servicio");
    if ($ok) { $mensaje .= showSuccess('Columna `servicio_id` añadida a `citas`. '); $has_servicio_id = true; }
    else { $mensaje .= showError('No se pudo añadir `servicio_id`: ' . mysqli_error($conexion)); }
}

// Manejo de POST: actualizar estado y asignar mecánico
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'actualizar_estado') {
        $citaId = intval($_POST['cita_id'] ?? 0);
        $nuevoEstado = sanitize($_POST['estado'] ?? '');
        if ($citaId > 0) {
            $stmt = mysqli_prepare($conexion, "UPDATE citas SET estado = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $nuevoEstado, $citaId);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                if ($ok) $mensaje = showSuccess('Estado actualizado correctamente');
                else $mensaje = showError('Error al actualizar estado: ' . mysqli_error($conexion));
            }
        }
    } elseif ($action === 'asignar_mecanico') {
        $citaId = intval($_POST['cita_id'] ?? 0);
        $mecanicoId = intval($_POST['mecanico_id'] ?? 0);
        if ($citaId > 0 && $mecanicoId > 0) {
            $estadoConfirmada = 'confirmada';
            $stmt = mysqli_prepare($conexion, "UPDATE citas SET mecanico_id = ?, estado = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'isi', $mecanicoId, $estadoConfirmada, $citaId);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                if ($ok) $mensaje = showSuccess('Mecánico asignado correctamente');
                else $mensaje = showError('Error al asignar mecánico: ' . mysqli_error($conexion));
            }
        }
    }
}

// Construir consulta dinámica para listar citas (compatibilidad con esquema existente)
$selectParts = [];
if ($has_cliente_id) {
    $selectParts[] = 'cl.nombre AS cliente_nombre';
    $selectParts[] = 'cl.telefono AS cliente_telefono';
} else {
    $selectParts[] = "c.cliente AS cliente_nombre";
    $selectParts[] = "NULL AS cliente_telefono";
}
if ($has_mecanico_id) {
    $selectParts[] = 'm.nombre AS mecanico_nombre';
} else {
    $selectParts[] = "NULL AS mecanico_nombre";
}
if ($has_servicio_id) {
    $selectParts[] = 's.nombre AS servicio_nombre';
    $selectParts[] = 's.precio AS servicio_precio';
} else {
    $selectParts[] = "c.servicio AS servicio_nombre";
    $selectParts[] = "NULL AS servicio_precio";
}

$sql = 'SELECT c.*, ' . implode(', ', $selectParts) . ' FROM citas c';
if ($has_cliente_id) $sql .= ' LEFT JOIN user cl ON c.cliente_id = cl.id';
if ($has_mecanico_id) $sql .= ' LEFT JOIN user m ON c.mecanico_id = m.id';
if ($has_servicio_id) $sql .= ' LEFT JOIN servicios s ON c.servicio_id = s.id';
$sql .= ' ORDER BY c.fecha DESC, c.hora DESC';

$citas = [];
$res = mysqli_query($conexion, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $citas[] = $row;
    }
    mysqli_free_result($res);
}

// Obtener lista de mecánicos para asignar (rol = 2)
$mecanicos = [];
$mQuery = "SELECT id, nombre FROM user WHERE rol = 2";
$resM = mysqli_query($conexion, $mQuery);
if ($resM) {
    while ($r = mysqli_fetch_assoc($resM)) $mecanicos[] = $r;
    mysqli_free_result($resM);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de citas</title>
    <!-- Libraries -->
    <!-- Bootstrap 5 JS y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/style.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
    <link rel="stylesheet" href="/Self-Management/public/css/admin-dates.css">
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
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? ($_SESSION['user_nombre'] ?? 'Administrador')); ?> (Admin)</span>
            </div>

            <h2>Gestión de Citas</h2>
            <?php if (!empty($mensaje)): ?>
                <div class="messages">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="button-container">
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">Agregar cita</button>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">

        <!-- Modal con formulario -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <form id="popupForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalLabel">Formulario de Servicio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Tecnico</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Vehiculo</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Servicio</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Fecha</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Hora</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Estado</label>
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

        <!-- Modal Ver Detalles -->
        <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-custom">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detalleModalLabel">Detalles de la Cita</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Cliente:</strong> Juan Pérez</p>
                        <p><strong>Teléfono:</strong> 555-1234</p>
                        <p><strong>Correo:</strong> juan@example.com</p>
                        <p><strong>Vehículo:</strong> Toyota Corolla 2019</p>
                        <p><strong>Placa:</strong> XYZ-123</p>
                        <p><strong>Fecha:</strong> 2025-05-10</p>
                        <p><strong>Hora:</strong> 10:30</p>
                        <p><strong>Estado:</strong> Pendiente</p>
                        <p><strong>Servicios:</strong> Cambio de aceite, revisión general</p>
                        <p><strong>Observaciones:</strong> Cliente solicita revisión extra de frenos.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="alert('Ver historial cliente')">Historial cliente</button>
                        <button type="button" class="btn btn-primary" onclick="alert('Ver historial vehículo')">Historial vehículo</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="search-filter-container">
                <input type="text" id="search-bar" placeholder="Buscar cliente..." />
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
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Vehículo</th>
                        <th>Fecha/Hora</th>
                        <th>Mecánico</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($citas)): ?>
                        <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['id']); ?></td>
                            <td><?php echo htmlspecialchars($cita['cliente_nombre'] ?? ($cita['cliente'] ?? '—')); ?></td>
                            <td><?php echo htmlspecialchars($cita['cliente_telefono'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($cita['servicio_nombre'] ?? ($cita['servicio'] ?? '—')); ?></td>
                            <td><?php echo htmlspecialchars($cita['vehiculo'] ?? '—'); ?></td>
                            <td><?php 
                                echo !empty($cita['fecha']) ? formatDate($cita['fecha']) : '—';
                                echo !empty($cita['hora']) ? ' ' . formatTime($cita['hora']) : '';
                            ?></td>
                            <td>
                                <?php if (!empty($cita['mecanico_nombre'])): ?>
                                    <?php echo htmlspecialchars($cita['mecanico_nombre']); ?>
                                <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="asignar_mecanico">
                                        <input type="hidden" name="cita_id" value="<?php echo htmlspecialchars($cita['id']); ?>">
                                        <select name="mecanico_id" onchange="this.form.submit()" class="select-inline form-select form-select-sm">
                                            <option value="">Asignar...</option>
                                            <?php foreach ($mecanicos as $mec): ?>
                                                <option value="<?php echo htmlspecialchars($mec['id']); ?>"><?php echo htmlspecialchars($mec['nombre']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><?php echo getEstadoBadge($cita['estado'] ?? 'pendiente'); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="actualizar_estado">
                                    <input type="hidden" name="cita_id" value="<?php echo htmlspecialchars($cita['id']); ?>">
                                    <select name="estado" onchange="this.form.submit()" class="select-inline form-select form-select-sm">
                                        <option value="pendiente" <?php echo ($cita['estado'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="confirmada" <?php echo ($cita['estado'] === 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                        <option value="en_proceso" <?php echo ($cita['estado'] === 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                        <option value="completada" <?php echo ($cita['estado'] === 'completada') ? 'selected' : ''; ?>>Completada</option>
                                        <option value="cancelada" <?php echo ($cita['estado'] === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                    </select>
                                </form>
                                
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detalleModal">
                                    Ver detalles
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center">No hay citas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <!-- JS para manejar el envío del formulario -->
    <script>
        document.getElementById('popupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí puedes procesar los datos del formulario
            alert('Formulario enviado');
            const modal = bootstrap.Modal.getInstance(document.getElementById('formModal'));
            modal.hide(); // Cierra el modal después de enviar
        });
    </script>
    <!-- JS para manejar el modal de detalles -->
    <script>
        function verDetalles() {
            document.getElementById('detalleModal').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('detalleModal').style.display = 'none';
        }

        // También permite cerrar haciendo clic fuera del modal
        window.onclick = function(event) {
            const modal = document.getElementById('detalleModal');
            if (event.target === modal) cerrarModal();
        };
    </script>
</body>

</html>

<!-- Reasignar el mecanico -->