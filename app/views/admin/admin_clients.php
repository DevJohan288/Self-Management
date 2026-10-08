<?php
// Cargar configuración central (ruta absoluta desde document root)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';

// Validar rol de administrador
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
requireRole('admin');
$mensaje = '';

// Manejo de acciones (agregar / eliminar / desactivar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'agregar') {
        $nombre   = trim($_POST['nombre'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        $telefono = trim($_POST['telefono'] ?? '');
        $rol      = intval($_POST['rol'] ?? 3);

        $stmt = mysqli_prepare($conexion, "INSERT INTO user (nombre, correo, password, telefono, rol) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $correo, $password, $telefono, $rol);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $ok = false;
        }

        $mensaje = $ok ? '✅ Usuario agregado correctamente' : '❌ Error al agregar usuario';

    } elseif ($action === 'eliminar' || $action === 'desactivar') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = mysqli_prepare($conexion, "DELETE FROM user WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $ok = false;
            }
            $mensaje = $ok ? '🗑️ Usuario eliminado correctamente' : '❌ Error al eliminar usuario';
        }
    }
}

// Obtener lista de usuarios para mostrar
$usuarios = [];
$res = mysqli_query($conexion, "SELECT id, nombre, correo AS email, telefono, rol, created_at AS fecha_registro FROM user ORDER BY created_at DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $usuarios[] = $row;
    }
    mysqli_free_result($res);
} else {
    // En desarrollo registrar error si es necesario
    // error_log('Error al obtener usuarios: ' . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de clientes</title>
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_admin.php'; ?>
    <main>
        <div class="text-content">
            <h2>Gestion de clientes</h2>
            <div class="button-container">
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">Agregar cliente
                </button>
            </div>
        </div>

        <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? ($_SESSION['user_nombre'] ?? 'Administrador')); ?> (Admin)</span>
            </div>
        </div>
        <!-- Modal con formulario [Cleintes]-->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <form id="popupForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalLabel">Formulario de contacto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefono</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Placa de Vehiculo</label>
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
                        <h5 class="modal-title" id="detalleModalLabel">Detalles del Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Nombre:</strong> Juan Pérez</p>
                        <p><strong>Teléfono:</strong> 555-1234</p>
                        <p><strong>Correo:</strong> juan@example.com</p>
                        <p><strong>Placa de Vehículo:</strong> XYZ-123</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

             <?php echo $mensaje; ?>

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
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Maria López</td>
                        <td>555-1234</td>
                        <td>maria.lopez@example.com</td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detalleModal">Ver detalles</button>
                            <button class="btn btn-warning btn-sm">Editar</button>
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

              <div class="card">
                <h2>Agregar Nuevo Usuario</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="agregar">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" required>
                        </div>
                        <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="correo" required>
                            </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono">
                        </div>
                    </div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select name="rol" required>
                            <option value="3">Cliente</option>
                            <option value="2">Mecánico</option>
                            <option value="1">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar Usuario</button>
                </form>
            </div>

            <div class="card">
                <h2>Lista de Usuarios</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                <td><span class="badge badge-info"><?php
                                    $roleNames = [1 => 'Administrador', 2 => 'Mecánico', 3 => 'Cliente'];
                                    echo htmlspecialchars($roleNames[intval($usuario['rol'])] ?? 'Desconocido');
                                ?></span></td>
                                <td><?php echo formatDate($usuario['fecha_registro']); ?></td>
                                <td>
                                    <?php if ($usuario['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="desactivar">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desactivar usuario?')">Desactivar</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </main>
</body>
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

</html>