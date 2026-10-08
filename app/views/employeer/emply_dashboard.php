<?php
// Cargar configuración central
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/funtions.php';
requireRole('empleado');

$mensaje = '';
$mecanicoId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$mecanicoNombre = $_SESSION['user_nombre'] ?? $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Empleado';

// Detectar si la tabla citas tiene columnas relacionales
$has_cliente_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'cliente_id'")) > 0);
$has_mecanico_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'mecanico_id'")) > 0);
$has_servicio_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'servicio_id'")) > 0);

// Manejar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'actualizar_estado' && $conexion) {
        $citaId = intval($_POST['cita_id'] ?? 0);
        $nuevoEstado = sanitize($_POST['estado'] ?? '');
        
        if ($has_mecanico_id) {
            // Usar mecanico_id
            $stmt = mysqli_prepare($conexion, "UPDATE citas SET estado = ? WHERE id = ? AND mecanico_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sii', $nuevoEstado, $citaId, $mecanicoId);
                if (mysqli_stmt_execute($stmt)) {
                    $mensaje = showSuccess('Estado actualizado correctamente');
                } else {
                    $mensaje = showError('Error al actualizar: ' . mysqli_error($conexion));
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // Fallback: actualizar por nombre del mecánico
            $stmt = mysqli_prepare($conexion, "UPDATE citas SET estado = ? WHERE id = ? AND mecanico = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sis', $nuevoEstado, $citaId, $mecanicoNombre);
                if (mysqli_stmt_execute($stmt)) {
                    $mensaje = showSuccess('Estado actualizado correctamente');
                } else {
                    $mensaje = showError('Error al actualizar: ' . mysqli_error($conexion));
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Estadísticas
$totalCitas = 0;
$citasPendientes = 0;
$citasCompletadas = 0;
$misCitas = [];

if ($conexion) {
    // Construir consulta base
    $whereClause = $has_mecanico_id ? 
        "WHERE c.mecanico_id = " . intval($mecanicoId) :
        "WHERE c.mecanico = '" . mysqli_real_escape_string($conexion, $mecanicoNombre) . "'";
    
    // Total de citas
    $rs = mysqli_query($conexion, "SELECT COUNT(*) as total FROM citas c $whereClause");
    if ($rs && $row = mysqli_fetch_assoc($rs)) $totalCitas = $row['total'];

    // Citas pendientes
    $rs = mysqli_query($conexion, "SELECT COUNT(*) as total FROM citas c " . $whereClause . " AND c.estado IN ('pendiente', 'Pendiente', 'confirmada', 'Confirmada')");
    if ($rs && $row = mysqli_fetch_assoc($rs)) $citasPendientes = $row['total'];

    // Citas completadas
    $rs = mysqli_query($conexion, "SELECT COUNT(*) as total FROM citas c " . $whereClause . " AND LOWER(c.estado) = 'completada'");
    if ($rs && $row = mysqli_fetch_assoc($rs)) $citasCompletadas = $row['total'];

    // Obtener lista de citas con información relacionada
    $select = "SELECT c.*";
    $joins = "";

    if ($has_cliente_id) {
        $select .= ", u.nombre as cliente_nombre, u.telefono as cliente_telefono";
        $joins .= " LEFT JOIN user u ON c.cliente_id = u.id";
    }

    if ($has_servicio_id) {
        $select .= ", s.nombre as servicio_nombre, s.precio";
        $joins .= " LEFT JOIN servicios s ON c.servicio_id = s.id";
    } else {
        $select .= ", s.nombre as servicio_nombre, s.precio";
        $joins .= " LEFT JOIN servicios s ON s.nombre = c.servicio";
    }

    $sql = "$select FROM citas c $joins $whereClause ORDER BY c.fecha DESC, c.hora DESC";
    
    $rs = mysqli_query($conexion, $sql);
    if ($rs) {
        while ($row = mysqli_fetch_assoc($rs)) {
            // Si no hay joins, usar los campos directos
            if (!$has_cliente_id) {
                $row['cliente_nombre'] = $row['cliente'];
            }
            if (!$has_servicio_id) {
                $row['servicio_nombre'] = $row['servicio'];
            }
            $misCitas[] = $row;
        }
    }
}

// Obtener citas del día actual
$citasHoy = array_filter($misCitas, function($cita) {
    return $cita['fecha'] === date('Y-m-d');
});

// Próximas citas (futuras)
$citasFuturas = array_filter($misCitas, function($cita) {
    return $cita['fecha'] > date('Y-m-d') || 
           ($cita['fecha'] === date('Y-m-d') && $cita['hora'] > date('H:i:s'));
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Inicio</title>
  <!-- Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Para gráficas -->
  <!-- CSS Styles -->
  <link rel="stylesheet" href="/Self-Management/public/css/style.css">
  <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
  <!-- Favicon/images -->
  <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png">
  <!-- JS Scripts -->
  <script src="/Self-Management/public/js/icon-theme.js"></script>
</head>

<body>
  <!--Include componenet: Sidebar -->
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_employ.php'; ?>
  <main>
    <div class="container">
      <div class="header">
        <h1>Dashboard</h1>
        <p>Bienvenido a tu panel de control</p>
      </div>
      <div class="content">
        <div class="separator">
          <div class="stat-card active">
            <div class="stat-info">
              <p>Servicios</p>
              <h3><?php echo $totalCitas; ?></h3>
              <p>Total Asignados</p>
            </div>
            <a href="#" class="button-box">
              <img src="/Self-Management/public/images/icon/icon_groups.svg" alt="Total servicios">
            </a>
          </div>

          <!-- Card:2 -->
          <div class="stat-card active">
            <div class="stat-info">
              <p>En progreso</p>
              <h3><?php echo $citasPendientes; ?></h3>
              <p>Pendientes/Confirmados</p>
            </div>
            <a href="#" class="button-box">
              <img src="/Self-Management/public/images/icon/icon-person.svg" alt="Servicios en progreso" class="iconbox">
            </a>
          </div>

          <!-- Card:3 -->
          <div class="stat-card active">
            <div class="stat-info">
              <p>Finalizados</p>
              <h3><?php echo $citasCompletadas; ?></h3>
              <p>Servicios completados</p>
            </div>
            <a href="#" class="button-box">
              <img src="/Self-Management/public/images/icon/icon_money.svg" alt="Servicios completados" class="iconbox">
            </a>
          </div>
        </div>

        <!-- Cards reported  -->
        <div class="stats-cards separator">
          <!-- Citas de hoy -->
          <?php foreach (array_slice($citasHoy, 0, 3) as $cita): ?>
          <div class="stat-card active">
            <div class="stat-info">
              <h1>Cita #<?php echo $cita['id']; ?></h1>
              <p><?php echo formatTime($cita['hora']); ?> - <?php echo htmlspecialchars($cita['servicio_nombre']); ?></p>
              <h3><?php echo htmlspecialchars($cita['cliente_nombre']); ?></h3>
              <p><?php echo $cita['vehiculo'] ?? 'No especificado'; ?></p>
              <small>Estado: <?php echo getEstadoBadge($cita['estado']); ?></small>
            </div>
            <a href="#" class="button-box">
              <img src="/Self-Management/public/images/icon/icon_groups.svg" alt="Detalles de cita">
            </a>
          </div>
          <?php endforeach; ?>
          
          <?php if (empty($citasHoy)): ?>
          <div class="stat-card">
            <div class="stat-info">
              <h1>Sin citas para hoy</h1>
              <p>No hay citas programadas para el día de hoy.</p>
            </div>
            <a href="#" class="button-box">
              <img src="/Self-Management/public/images/icon/icon-info.svg" alt="Sin citas">
            </a>
          </div>
          <?php endif; ?>
        </div>

        <div class="separator">
          <div class="container">
            <h3>Servicios del dia</h3>
            <?php if (!empty($mensaje)) echo $mensaje; ?>
            
            <table class="user-table">
              <thead>
                <tr>
                  <th>Fecha/Hora</th>
                  <th>Cliente</th>
                  <th>Servicio</th>
                  <th>Vehiculo</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($citasFuturas as $cita): ?>
                <tr>
                  <td><?php echo formatDate($cita['fecha']) . '<br>' . formatTime($cita['hora']); ?></td>
                  <td>
                    <?php echo htmlspecialchars($cita['cliente_nombre']); ?>
                    <?php if (!empty($cita['cliente_telefono'])): ?>
                    <br><small>Tel: <?php echo htmlspecialchars($cita['cliente_telefono']); ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($cita['servicio_nombre']); ?></td>
                  <td><?php echo htmlspecialchars($cita['vehiculo']); ?></td>
                  <td><?php echo getEstadoBadge($cita['estado']); ?></td>
                  <td>
                    <form method="POST">
                      <input type="hidden" name="action" value="actualizar_estado">
                      <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                      <select name="estado" onchange="this.form.submit()" class="select-inline">
                        <option value="">Cambiar...</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                      </select>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($citasFuturas)): ?>
                <tr>
                  <td colspan="6" class="text-center">No hay citas programadas.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
                <h2>Mis Citas Asignadas</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Servicio</th>
                                <th>Vehículo</th>
                                <th>Fecha/Hora</th>
                                <th>Estado</th>
                                <th>Notas</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($misCitas as $cita): ?>
                            <tr>
                                <td><?php echo $cita['id']; ?></td>
                                <td><?php echo $cita['cliente_nombre']; ?></td>
                                <td><?php echo $cita['cliente_telefono']; ?></td>
                                <td><?php echo $cita['servicio_nombre']; ?></td>
                                <td>
                                    <?php echo $cita['vehiculo_marca'] . ' ' . $cita['vehiculo_modelo']; ?><br>
                                    <small>Placa: <?php echo $cita['vehiculo_placa']; ?></small>
                                </td>
                                <td><?php echo formatDate($cita['fecha']) . '<br>' . formatTime($cita['hora']); ?></td>
                                <td><?php echo getEstadoBadge($cita['estado']); ?></td>
                                <td><?php echo $cita['notas'] ?? '-'; ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="actualizar_estado">
                                        <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                                        <select name="estado" onchange="this.form.submit()" class="select-inline">
                                            <option value="">Cambiar...</option>
                                            <option value="confirmada">Confirmada</option>
                                            <option value="en_proceso">En Proceso</option>
                                            <option value="completada">Completada</option>
                                            <option value="cancelada">Cancelada</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
      </div>
  </main>
</body>

</html>