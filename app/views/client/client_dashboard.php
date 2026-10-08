<?php
// Cargar configuración central
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/funtions.php';
requireRole('cliente');

$mensaje = '';
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$userName = $_SESSION['user_nombre'] ?? $_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Invitado';

// Detectar columnas relacionales en citas
$has_cliente_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'cliente_id'")) > 0);
$has_mecanico_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'mecanico_id'")) > 0);
$has_servicio_id = ($conexion && mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM citas LIKE 'servicio_id'")) > 0);

// Construir consulta para obtener citas del cliente
$misCitas = [];
if ($conexion) {
    $select = "SELECT c.*";
    $joins = "";
    if ($has_mecanico_id) {
        $select .= ", m.nombre AS mecanico_nombre";
        $joins .= " LEFT JOIN `user` m ON c.mecanico_id = m.id";
    }
    if ($has_servicio_id) {
        $select .= ", s.nombre AS servicio_nombre, s.precio";
        $joins .= " LEFT JOIN servicios s ON c.servicio_id = s.id";
    } else {
        $select .= ", s.nombre AS servicio_nombre, s.precio";
        $joins .= " LEFT JOIN servicios s ON s.nombre = c.servicio";
    }

    $where = "";
    $params = [];
    if ($has_cliente_id && $userId) {
        $where = "WHERE c.cliente_id = ?";
        $params[] = $userId;
    } else {
        $where = "WHERE c.cliente = ?";
        $params[] = $userName;
    }

    $sql = "$select FROM citas c $joins $where ORDER BY c.fecha DESC, c.hora DESC";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        if (!empty($params)) {
            if (is_int($params[0])) mysqli_stmt_bind_param($stmt, 'i', $params[0]);
            else mysqli_stmt_bind_param($stmt, 's', $params[0]);
        }
        if (mysqli_stmt_execute($stmt)) {
            $res = mysqli_stmt_get_result($stmt);
            if ($res) while ($row = mysqli_fetch_assoc($res)) $misCitas[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// Estadísticas
$totalCitas = count($misCitas);
$citasPendientes = count(array_filter($misCitas, function($c) { 
    return in_array(strtolower($c['estado'] ?? ''), ['pendiente', 'confirmada']); 
}));
$citasCompletadas = count(array_filter($misCitas, function($c) { 
    return strtolower($c['estado'] ?? '') === 'completada'; 
}));

// Próxima cita
$proximaCita = null;
foreach ($misCitas as $cita) {
    if (in_array(strtolower($cita['estado'] ?? ''), ['pendiente', 'confirmada'])) {
        if (!$proximaCita || strtotime("{$cita['fecha']} {$cita['hora']}") < strtotime("{$proximaCita['fecha']} {$proximaCita['hora']}")) {
            $proximaCita = $cita;
        }
    }
}

// Servicios más solicitados (para la gráfica)
$serviciosStats = [];
foreach ($misCitas as $cita) {
    $servicio = $cita['servicio_nombre'] ?? $cita['servicio'] ?? 'Otro';
    if (!isset($serviciosStats[$servicio])) $serviciosStats[$servicio] = 0;
    $serviciosStats[$servicio]++;
}
arsort($serviciosStats);
$serviciosStats = array_slice($serviciosStats, 0, 4, true);
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_client.php'; ?>
    <main>
        <!-- Cards -->
        <div class="stats-cards separator">
            <!-- Card:1 Próxima cita -->
            <div class="stat-card <?php echo $proximaCita ? 'active' : ''; ?>">
                <div class="stat-info">
                    <p>Próxima cita</p>
                    <?php if ($proximaCita): ?>
                        <h3><?php echo formatDate($proximaCita['fecha']); ?>, <?php echo formatTime($proximaCita['hora']); ?></h3>
                        <p>Servicio: <?php echo htmlspecialchars($proximaCita['servicio_nombre'] ?? $proximaCita['servicio']); ?></p>
                    <?php else: ?>
                        <h3>No hay citas programadas</h3>
                        <p>Agenda tu próxima visita</p>
                    <?php endif; ?>
                </div>
                <a href="/Self-Management/app/views/client/client_my_appointments.php" class="button-box">
                    <img src="/Self-Management/public/images/icon/icon_groups.svg" alt="Ver citas">
                </a>
            </div>

            <!-- Card:2 Citas pendientes -->
            <div class="stat-card active">
                <div class="stat-info">
                    <p>Citas Pendientes</p>
                    <h3><?php echo $citasPendientes; ?></h3>
                    <p><?php echo $citasPendientes === 1 ? 'cita por atender' : 'citas por atender'; ?></p>
                </div>
                <a href="/Self-Management/app/views/client/client_my_appointments.php" class="button-box">
                    <img src="/Self-Management/public/images/icon/icon-person.svg" alt="Ver pendientes" class="iconbox">
                </a>
            </div>

            <!-- Card:3 Historial -->
            <div class="stat-card active">
                <div class="stat-info">
                    <p>Historial de servicios</p>
                    <h3><?php echo $citasCompletadas; ?></h3>
                    <p><?php echo $citasCompletadas === 1 ? 'servicio completado' : 'servicios completados'; ?></p>
                </div>
                <a href="/Self-Management/app/views/client/client_my_appointments.php?filter=completadas" class="button-box">
                    <img src="/Self-Management/public/images/icon/icon_money.svg" alt="Ver historial" class="iconbox">
                </a>
            </div>
        </div>

        <div class="container">
            <h3>Actividad Reciente</h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Servicio</th>
                        <th>Vehículo</th>
                        <th>Estado</th>
                        <th>Mecánico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $actividadReciente = array_slice($misCitas, 0, 5); // Mostrar últimas 5 citas
                    if (!empty($actividadReciente)): 
                        foreach ($actividadReciente as $cita): 
                    ?>
                        <tr>
                            <td><?php echo formatDate($cita['fecha']); ?> <?php echo formatTime($cita['hora']); ?></td>
                            <td><?php echo htmlspecialchars($cita['servicio_nombre'] ?? $cita['servicio'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($cita['vehiculo'] ?? '—'); ?></td>
                            <td><?php echo getEstadoBadge($cita['estado'] ?? 'pendiente'); ?></td>
                            <td><?php echo htmlspecialchars($cita['mecanico_nombre'] ?? '—'); ?></td>
                        </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay actividad reciente</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Charts -->
        <div class="separator">
            <div class="container">
                <h3>Servicios más solicitados</h3>
                <p>Consulta el historial de citas y servicios realizados.</p>
                <canvas id="mostRequestedServicesChart"></canvas>
            </div>

            <div class="separator">
                <div class="container">
                    <h3>Notificaciones</h3>
                    <p>Alertas y recordatorios sobre tus citas</p>
                    <?php if ($proximaCita): ?>
                    <div class="alert alert-info">
                        <strong>Próxima cita:</strong>
                        <p>
                            Fecha: <?php echo formatDate($proximaCita['fecha']); ?> a las <?php echo formatTime($proximaCita['hora']); ?><br>
                            Servicio: <?php echo htmlspecialchars($proximaCita['servicio_nombre'] ?? $proximaCita['servicio']); ?><br>
                            Vehículo: <?php echo htmlspecialchars($proximaCita['vehiculo']); ?><br>
                            Estado: <?php echo getEstadoBadge($proximaCita['estado']); ?>
                        </p>
                        <a href="/Self-Management/app/views/client/client_my_appointments.php" class="btn btn-primary">Ver todas mis citas</a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <p>No tienes citas programadas. ¿Necesitas agendar un servicio?</p>
                        <a href="/Self-Management/app/views/client/client_my_appointments.php" class="btn btn-primary">Agendar cita</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Datos para la gráfica de servicios más solicitados
        const ctx = document.getElementById('mostRequestedServicesChart').getContext('2d');
        const mostRequestedServicesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($serviciosStats)); ?>,
                datasets: [{
                    label: 'Servicios solicitados',
                    data: <?php echo json_encode(array_values($serviciosStats)); ?>,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Frecuencia de servicios solicitados'
                    }
                }
            }
        });
    </script>
</body>

</html>