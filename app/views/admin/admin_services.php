<?php
// Cargar configuración central (ruta absoluta desde document root)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';

// Validar rol de administrador
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
requireRole('admin');
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/funtions.php';

$mensaje = '';

// Asegurarse de que la tabla `servicios` tenga las columnas que usamos (duracion, activo)
// Si faltan, las añadimos automáticamente.
$check = mysqli_query($conexion, "SHOW COLUMNS FROM servicios LIKE 'duracion'");
if ($check && mysqli_num_rows($check) === 0) {
    $ok = mysqli_query($conexion, "ALTER TABLE servicios ADD COLUMN duracion INT NULL AFTER descripcion");
    if ($ok) {
        $mensaje .= showSuccess('Columna `duracion` añadida a la tabla `servicios`. ');
    } else {
        $mensaje .= showError('No se pudo añadir columna `duracion`: ' . mysqli_error($conexion));
    }
}
$check2 = mysqli_query($conexion, "SHOW COLUMNS FROM servicios LIKE 'activo'");
if ($check2 && mysqli_num_rows($check2) === 0) {
    $ok2 = mysqli_query($conexion, "ALTER TABLE servicios ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER duracion");
    if ($ok2) {
        $mensaje .= showSuccess('Columna `activo` añadida a la tabla `servicios`. ');
    } else {
        $mensaje .= showError('No se pudo añadir columna `activo`: ' . mysqli_error($conexion));
    }
}

// Manejo de acciones (agregar / editar / eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'agregar') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $duracion = intval($_POST['duracion'] ?? 0);

        $stmt = mysqli_prepare($conexion, "INSERT INTO servicios (nombre, descripcion, precio, duracion) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssdi', $nombre, $descripcion, $precio, $duracion);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($ok) $mensaje = showSuccess('Servicio agregado correctamente');
            else $mensaje = showError('Error al agregar servicio: ' . mysqli_error($conexion));
        } else {
            $mensaje = showError('Error al preparar la consulta: ' . mysqli_error($conexion));
        }

    } elseif ($action === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $duracion = intval($_POST['duracion'] ?? 0);

        $stmt = mysqli_prepare($conexion, "UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssdii', $nombre, $descripcion, $precio, $duracion, $id);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($ok) $mensaje = showSuccess('Servicio actualizado correctamente');
            else $mensaje = showError('Error al actualizar servicio: ' . mysqli_error($conexion));
        } else {
            $mensaje = showError('Error al preparar la consulta: ' . mysqli_error($conexion));
        }

    } elseif ($action === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($conexion, "UPDATE servicios SET activo = 0 WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($ok) $mensaje = showSuccess('Servicio desactivado correctamente');
            else $mensaje = showError('Error al desactivar servicio: ' . mysqli_error($conexion));
        } else {
            $mensaje = showError('Error al preparar la consulta: ' . mysqli_error($conexion));
        }
    }
}

// Obtener lista de servicios activos
$servicios = [];
$res = mysqli_query($conexion, "SELECT id, nombre, descripcion, precio, duracion, created_at FROM servicios WHERE activo = 1 ORDER BY nombre");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $servicios[] = $row;
    }
    mysqli_free_result($res);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de servicios</title>

    <!-- Bootstrap 5 JS y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/styles.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">

    <!-- Favicon -->
    <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png">

    <!-- Custom JS -->
    <script src="/Self-Management/public/js/icon-theme.js"></script>
</head>

<body>
    <!-- Sidebar component -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_admin.php'; ?>

    <main>
        <div class="text-content">
            <h2>Gestión de servicios</h2>
            <div class="button-container">
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">
                    Crear nuevo servicio
                </button>
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">
                    Asignar servicio pendientes
                </button>
            </div>
        </div>

        <!-- Modal: Nuevo servicio -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <form id="popupForm" method="POST">
                        <input type="hidden" name="action" value="agregar">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalLabel">Nuevo servicio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del servicio</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="comentario" class="form-label">Descripción</label>
                                <textarea id="comentario" name="descripcion" rows="3" class="form-control" placeholder="Ej: Quiero revisar un ruido al frenar."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="duracion" class="form-label">Duración (minutos)</label>
                                <input type="number" class="form-control" id="duracion" name="duracion" required>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio ($)</label>
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
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

        <!-- Filtro y tabla -->
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
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Duración</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($servicios)): ?>
                        <?php foreach ($servicios as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($s['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($s['duracion'] ? $s['duracion'] . ' min' : '—'); ?></td>
                            <td>$<?php echo htmlspecialchars($s['precio']); ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="editar">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($s['id']); ?>">
                                    <button class="btn btn-warning btn-sm">Editar</button>
                                </form>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($s['id']); ?>">
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Desactivar servicio?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No hay servicios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="separator">
            <div class="container">
                <h4>Servicios pendientes</h4>
                <p>Estos son los servicios pendientes de asignar a un técnico.</p>
                <div class="asignacion">
                    <label for="cita">Cita pendiente:</label>
                    <select id="cita">
                        <option value="1">Cita #1 - 10/05/2025 - 09:00 AM</option>
                        <option value="2">Cita #2 - 10/05/2025 - 10:00 AM</option>
                    </select>

                    <label for="mecanico">Mecánico:</label>
                    <select id="mecanico">
                        <option value="1">Juan Pérez</option>
                        <option value="2">Luis Gómez</option>
                    </select>

                    <button class="button button1">Asignar</button>
                </div>
            </div>
        </div>
    </main>
</body>

</html>