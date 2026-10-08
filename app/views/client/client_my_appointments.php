<?php
// Cargar configuración central (ruta absoluta desde document root)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/funtions.php';
requireRole('cliente');

$mensaje = '';
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$userName = $_SESSION['user_nombre'] ?? $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Invitado';

// Detectar si la tabla citas tiene columnas relacionales
$has_cliente_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'cliente_id'")) > 0);
$has_mecanico_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'mecanico_id'")) > 0);
$has_servicio_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'servicio_id'")) > 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicioId = intval($_POST['servicio_id'] ?? 0);
    $mecanicoId = intval($_POST['mecanico_id'] ?? 0) ?: null;
    $fecha = sanitize($_POST['fecha'] ?? '');
    $hora = sanitize($_POST['hora'] ?? '');
    $vehiculoMarca = sanitize($_POST['vehiculo_marca'] ?? '');
    $vehiculoModelo = sanitize($_POST['vehiculo_modelo'] ?? '');
    $vehiculoPlaca = sanitize($_POST['vehiculo_placa'] ?? '');
    $notas = sanitize($_POST['notas'] ?? '');

    if ($conexion) {
        // Resolve servicio name from servicios table
        $servicioNombre = '';
        if ($servicioId) {
            $rs = mysqli_query($conexion, "SELECT nombre FROM servicios WHERE id = " . intval($servicioId));
            if ($rs && $r = mysqli_fetch_assoc($rs)) $servicioNombre = $r['nombre'];
        }

        $vehiculoStr = trim("$vehiculoMarca $vehiculoModelo - $vehiculoPlaca");

        // Insert into citas using prepared statement
        if ($has_cliente_id && $has_mecanico_id && $has_servicio_id) {
            // Use relational columns
            $stmt = mysqli_prepare($conexion, 
                "INSERT INTO citas (cliente_id, mecanico_id, servicio_id, fecha, hora, vehiculo, comentario, estado) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $estado = 'Pendiente';
                mysqli_stmt_bind_param($stmt, 'iiisssss', $userId, $mecanicoId, $servicioId, $fecha, $hora, $vehiculoStr, $notas, $estado);
                if (mysqli_stmt_execute($stmt)) {
                    $mensaje = showSuccess('Cita agendada correctamente. En breve un mecánico será asignado.');
                } else {
                    $mensaje = showError('Error al agendar la cita: ' . mysqli_error($conexion));
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // Use text columns (current DB schema)
            $stmt = mysqli_prepare($conexion, 
                "INSERT INTO citas (cliente, servicio, fecha, hora, vehiculo, comentario, estado) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $estado = 'Pendiente';
                mysqli_stmt_bind_param($stmt, 'sssssss', $userName, $servicioNombre, $fecha, $hora, $vehiculoStr, $notas, $estado);
                if (mysqli_stmt_execute($stmt)) {
                    $mensaje = showSuccess('Cita agendada correctamente. En breve un mecánico será asignado.');
                } else {
                    $mensaje = showError('Error al agendar la cita: ' . mysqli_error($conexion));
                }
                mysqli_stmt_close($stmt);
            }
        }
    } else {
        $mensaje = showError('Error: No hay conexión a la base de datos');
    }
}

// Fetch servicios and mecanicos for form selects
$servicios = [];
$mecanicos = [];
if ($conexion) {
    // Get active services
    $rs = mysqli_query($conexion, "SELECT * FROM servicios ORDER BY nombre");
    if ($rs) while ($r = mysqli_fetch_assoc($rs)) $servicios[] = $r;

    // Get mechanics (users with rol = 2 [empleado])
    $rs = mysqli_query($conexion, "SELECT id, nombre FROM user WHERE rol = 2");
    if ($rs) while ($r = mysqli_fetch_assoc($rs)) $mecanicos[] = $r;
}

// Fetch user's vehicles
$vehiculos = [];
if ($conexion && $userId) {
    $rs = mysqli_query($conexion, "SELECT * FROM vehiculos WHERE cliente_id = " . intval($userId));
    if ($rs) while ($r = mysqli_fetch_assoc($rs)) $vehiculos[] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas</title>
    <!-- Libraries -->
    <!-- Bootstrap 5 JS y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/styles.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
    <!-- Favicon/images -->
    <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png">
    <!-- JS Scripts -->
    <script src="/Self-Management/public/js/icon-theme.js"></script>
</head>

<body>
    <!--Include componenet: Sidebar -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_client.php'; ?>
    <main>
        <div class="text-content">
            <h2>Mis Citas Programadas</h2>
            <div class="button-container">
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">Agendar cita
                </button>
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formVehicle">Agregar Vehiculo
                </button>
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
        </div>

        <div class="separator">
            <div class="container">
                <div class="stat-card active">
                    <div class="stat-info">
                        <h1>Revisión de frenos</h1>
                        <p>Fecha: <strong>2025-05-08</strong></p>
                        <p>Hora: <strong>10:00 AM</strong></p>
                        <p>Estado: <strong>Pendiente</strong> </p>
                        <p>Vehiculo: <strong>Toyota Corolla 2020</strong> </p>
                        <p>Comentario: <strong>Quiero revisar un ruido al frenar.</strong> </p>
                        <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">Ver Datalles</button>
                    </div>
                    <span class="badge">Finalizada</span>
                </div>

                <div class="separator">
                    <div class="stat-card active">
                        <div class="stat-info">
                            <h1>Revisión de frenos</h1>
                            <p>Fecha: <strong>2025-05-08</strong></p>
                            <p>Hora: <strong>10:00 AM</strong></p>
                            <p>Estado: <strong>Pendiente</strong> </p>
                            <p>Vehiculo: <strong>Toyota Corolla 2020</strong> </p>
                            <p>Comentario: <strong>Quiero revisar un ruido al frenar.</strong> </p>
                            <button type="button" class="button success" data-bs-toggle="modal" data-bs-target="#formModal">Reprogramar</button>
                            <button type="button" class="button cancel" data-bs-toggle="modal" data-bs-target="#formModal">Cancelar cita</button>
                        </div>
                        <span class="badge success">CONFIRMADA</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal con formulario -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <form id="popupForm" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalLabel">Agendar Nueva Cita</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        
                        <div class="modal-body">
                            <?php if ($mensaje): ?>
                                <?php echo $mensaje; ?>
                            <?php endif; ?>

                            <!-- Servicio -->
                            <div class="mb-3">
                                <label for="servicio_id">Servicio: *</label>
                                <select id="servicio_id" name="servicio_id" class="form-select" required>
                                    <option value="">-- Selecciona un servicio --</option>
                                    <?php foreach ($servicios as $servicio): ?>
                                        <option value="<?php echo htmlspecialchars($servicio['id']); ?>">
                                            <?php echo htmlspecialchars($servicio['nombre']); ?> - 
                                            $<?php echo number_format($servicio['precio'], 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Mecánico (opcional) -->
                            <div class="mb-3">
                                <label for="mecanico_id">Mecánico: (opcional)</label>
                                <select id="mecanico_id" name="mecanico_id" class="form-select">
                                    <option value="">El taller asignará un mecánico</option>
                                    <?php foreach ($mecanicos as $mecanico): ?>
                                        <option value="<?php echo htmlspecialchars($mecanico['id']); ?>">
                                            <?php echo htmlspecialchars($mecanico['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Fecha y Hora -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha">Fecha: *</label>
                                    <input type="date" id="fecha" name="fecha" class="form-control" 
                                           required min="<?php echo date('Y-m-d'); ?>" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hora">Hora: *</label>
                                    <input type="time" id="hora" name="hora" class="form-control" required />
                                </div>
                            </div>

                            <!-- Información del Vehículo -->
                            <h6 class="mt-4">Información del Vehículo</h6>
                            <?php if (!empty($vehiculos)): ?>
                            <div class="mb-3">
                                <label for="vehiculo_select">Seleccionar vehículo registrado:</label>
                                <select id="vehiculo_select" class="form-select" onchange="fillVehicleInfo()">
                                    <option value="">Nuevo vehículo</option>
                                    <?php foreach ($vehiculos as $v): ?>
                                        <option value="<?php echo htmlspecialchars(json_encode([
                                            'marca' => $v['marca'],
                                            'modelo' => $v['modelo'],
                                            'placa' => $v['placa']
                                        ])); ?>">
                                            <?php echo htmlspecialchars("{$v['marca']} {$v['modelo']} - {$v['placa']}"); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="vehiculo_marca">Marca: *</label>
                                    <input type="text" id="vehiculo_marca" name="vehiculo_marca" 
                                           class="form-control" required placeholder="Ej: Toyota" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="vehiculo_modelo">Modelo: *</label>
                                    <input type="text" id="vehiculo_modelo" name="vehiculo_modelo" 
                                           class="form-control" required placeholder="Ej: Corolla" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="vehiculo_placa">Placa: *</label>
                                <input type="text" id="vehiculo_placa" name="vehiculo_placa" 
                                       class="form-control" required placeholder="Ej: ABC-123" />
                            </div>

                            <div class="mb-3">
                                <label for="notas">Notas adicionales:</label>
                                <textarea id="notas" name="notas" class="form-control" rows="3" 
                                        placeholder="Describe el problema o solicitud especial..."></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Agendar Cita</button>
                        </div>
                    </form>

                    <script>
                    function fillVehicleInfo() {
                        const select = document.getElementById('vehiculo_select');
                        if (!select) return;
                        
                        const vehicleInfo = select.value ? JSON.parse(select.value) : null;
                        
                        document.getElementById('vehiculo_marca').value = vehicleInfo ? vehicleInfo.marca : '';
                        document.getElementById('vehiculo_modelo').value = vehicleInfo ? vehicleInfo.modelo : '';
                        document.getElementById('vehiculo_placa').value = vehicleInfo ? vehicleInfo.placa : '';
                    }
                    </script>
                </div>
            </div>
        </div>

        <!-- Modal con formulario [Vehiculo] -->
        <div class="modal fade" id="formVehicle" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <form id="popupForm" action="/Self-Management/index.php?controller=vehicle&action=guardar" method="POST">
                        <div class="modal-header">
                            <!-- Agregar campo oculto para la URL de retorno -->
                            <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <h5 class="modal-title" id="formModalLabel">Registrar Vehículo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="marca">Marca:</label>
                                <select id="marca" name="marca" required>
                                    <option value="">-- Selecciona la marca --</option>
                                    <option value="Toyota">Toyota</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Ford">Ford</option>
                                    <option value="Chevrolet">Chevrolet</option>
                                    <option value="Nissan">Nissan</option>
                                    <option value="Hyundai">Hyundai</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" required>
                            </div>
                            <div class="mb-3">
                                <label for="anio" class="form-label">Año</label>
                                <input type="number" class="form-control" id="anio" name="anio" required min="1900" max="2099">
                            </div>
                            <div class="mb-3">
                                <label for="placa" class="form-label">Placa</label>
                                <input type="text" class="form-control" id="placa" name="placa" required>
                            </div>
                            <div class="mb-3">
                                <label for="tipo">Tipo de vehículo:</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="">-- Selecciona tu Vehículo --</option>
                                    <option value="Automóvil">Automóvil</option>
                                    <option value="Camioneta">Camioneta</option>
                                    <option value="SUV">SUV</option>
                                    <option value="Moto">Moto</option>
                                    <option value="Pickup">Pickup</option>
                                    <option value="Furgón">Furgón</option>
                                    <option value="Buseta">Buseta</option>
                                    <option value="Tracto Camión">Tracto Camión</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color" required>
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

        <h2>Listado de Vehículos</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Placa</th>
                        <th>Color</th>
                        <th>Año</th>
                        <th>Tipo</th>
                        <th>Cliente ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehiculos)): ?>
                        <?php foreach ($vehiculos as $v): ?>
                            <tr>
                                <td><?= $v['id'] ?></td>
                                <td><?= $v['marca'] ?></td>
                                <td><?= $v['modelo'] ?></td>
                                <td><?= $v['placa'] ?></td>
                                <td><?= $v['color'] ?></td>
                                <td><?= $v['año'] ?></td>
                                <td><?= $v['tipo'] ?></td>
                                <td><?= $v['cliente_id'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No hay vehículos registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="content">
            <h1>Agendar Nueva Cita</h1>
            
            <?php echo $mensaje; ?>
            
            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Servicio *</label>
                        <select name="servicio_id" required>
                            <option value="">Selecciona un servicio...</option>
                            <?php foreach ($servicios as $servicio): ?>
                                <option value="<?php echo $servicio['id']; ?>">
                                    <?php echo $servicio['nombre']; ?> - $<?php echo number_format($servicio['precio'], 2); ?> (<?php echo $servicio['duracion']; ?> min)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Mecánico (opcional)</label>
                        <select name="mecanico_id">
                            <option value="">El taller asignará un mecánico</option>
                            <?php foreach ($mecanicos as $mecanico): ?>
                                <option value="<?php echo $mecanico['id']; ?>"><?php echo $mecanico['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha *</label>
                            <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Hora *</label>
                            <input type="time" name="hora" required>
                        </div>
                    </div>

                    <h3>Información del Vehículo</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Marca *</label>
                            <input type="text" name="vehiculo_marca" required placeholder="Ej: Toyota, Ford, Honda">
                        </div>
                        <div class="form-group">
                            <label>Modelo *</label>
                            <input type="text" name="vehiculo_modelo" required placeholder="Ej: Corolla, Fiesta, Civic">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Placa *</label>
                        <input type="text" name="vehiculo_placa" required placeholder="Ej: ABC-123">
                    </div>

                    <div class="form-group">
                        <label>Notas adicionales</label>
                        <textarea name="notas" rows="4" placeholder="Describe el problema o solicitud especial..."></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Agendar Cita</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3>Servicios Disponibles</h3>
                <div class="services-grid">
                    <?php foreach ($servicios as $servicio): ?>
                    <div class="service-item">
                        <h4><?php echo $servicio['nombre']; ?></h4>
                        <p><?php echo $servicio['descripcion']; ?></p>
                        <p><strong>Precio:</strong> $<?php echo number_format($servicio['precio'], 2); ?></p>
                        <p><strong>Duración:</strong> <?php echo $servicio['duracion']; ?> minutos</p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    
</body>

</html>