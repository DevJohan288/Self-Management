<?php
// Cargar configuración central (ruta absoluta desde document root)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/config/init.php';

// Validar rol de administrador
require_once $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/includes/auth.php';
requireRole('admin');
// ==========================================
// Consultas estadísticas
// ==========================================
// Ejecutar consultas con comprobación de errores y valores por defecto
$res = mysqli_query($conexion, "SELECT COUNT(*) AS cnt FROM citas");
$totalCitas = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['cnt'] : 0;
$res = mysqli_query($conexion, "SELECT COUNT(*) AS cnt FROM user WHERE rol = 3");
$totalClientes = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['cnt'] : 0;
$res = mysqli_query($conexion, "SELECT COUNT(*) AS cnt FROM user WHERE rol = 2");
$totalEmpleados = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['cnt'] : 0;
$res = mysqli_query($conexion, "SELECT COUNT(*) AS cnt FROM citas WHERE estado = 'Pendiente'");
$citasPendientes = ($res && $row = mysqli_fetch_assoc($res)) ? (int)$row['cnt'] : 0;

// ==========================================
// Citas recientes (últimas 10)
// ==========================================
$sqlCitas = "
    SELECT c.*, u.nombre AS cliente_nombre
    FROM citas c
    LEFT JOIN user u ON c.cliente = u.nombre
    ORDER BY c.created_at DESC
    LIMIT 10
";
$resultCitas = mysqli_query($conexion, $sqlCitas);
// Asegurarse que $resultCitas es válido antes de usarlo en la tabla
if ($resultCitas === false) {
    $resultCitas = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inicio</title>
    <!-- Libraries -->
    <!-- Para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="container">
               <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? ($_SESSION['user_nombre'] ?? 'Administrador')); ?> (Admin)</span>
            </div>
            <h2>Panel de adminstracion</h2>
            <p>Bienvenido al panel de control. Aquí puedes visualizar y gestionar los datos clave en tiempo real, monitorear el rendimiento y tomar decisiones informadas para optimizar la gestión.</p>

            <!-- Cards -->
            <div class="stats-cards separator">
                <!-- Card:1 -->
                <div class="stat-card active">
                    <div class="stat-info">
                        <p>Técnicos</p>
                        <h3><?php echo htmlspecialchars((string)$totalEmpleados); ?></h3>
                        <p>Registrados</p>
                    </div>
                    <a href="#" class="button-box">
                        <img src="/Self-Management/public/images/icon/icon_groups.svg" alt="Usuarios">
                    </a>
                </div>

                <!-- Card:2 -->
                <div class="stat-card active">
                    <div class="stat-info">
                        <p>Citas</p>
                        <h3><?php echo htmlspecialchars((string)$totalCitas); ?></h3>
                        <p>Totales</p>
                    </div>
                    <a href="#" class="button-box">
                        <img src="/Self-Management/public/images/icon/icon-person.svg" alt="Más vendidos" class="iconbox">
                    </a>
                </div>

                <!-- Card:3 -->
                <div class="stat-card active">
                    <div class="stat-info">
                        <p>Citas pendientes</p>
                        <h3><?php echo htmlspecialchars((string)$citasPendientes); ?></h3>
                        <p>En espera</p>
                    </div>
                    <a href="#" class="button-box">
                        <img src="/Self-Management/public/images/icon/icon_money.svg" alt="Ventas" class="iconbox">
                    </a>
                </div>


            </div>
            <!-- Gráfica -->
            <div class="container">
                <div class="chart-container">
                    <canvas id="appointmentsChart"></canvas>
                </div>
            </div>

            <!-- Cards -->
            <!-- Sección de 'Flujos de Servicios' comentada porque el contenido se muestra en 'Citas Recientes' abajo.
                 Si necesitas una tabla separada, reactivala y conecta la consulta adecuada. -->
            <!--
            <div class="separator">
                <h3>Flujos de Servicios</h3>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Ciente</th>
                            <th>Vehiculo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>05/05/2025</td>
                            <td>admin01</td>
                            <td>Toyota</td>
                            <td>Finalizado</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            -->

            <div class="card">
                <h2>Citas Recientes</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Vehículo</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultCitas && mysqli_num_rows($resultCitas) > 0): ?>
                                <?php while ($cita = mysqli_fetch_assoc($resultCitas)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cita['id']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['cliente']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['servicio'] ?: 'No especificado'); ?></td>
                                        <td><?php echo htmlspecialchars($cita['vehiculo'] ?: 'No registrado'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($cita['fecha']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('H:i', strtotime($cita['hora']))); ?></td>
                                        <td>
                                            <span class="badge <?php echo htmlspecialchars(strtolower($cita['estado'])); ?>">
                                                <?php echo htmlspecialchars($cita['estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7">No hay citas registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>
        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        const appointmentsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Citas por Día',
                    data: [5, 8, 6, 9, 7, 4, 3],
                    backgroundColor: 'rgba(245, 235, 235, 0.2)',
                    borderColor: '#3498db',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>