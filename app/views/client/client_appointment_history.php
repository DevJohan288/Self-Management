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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_client.php'; ?>
    <main>
        <div class="text-content">
            <h2>Historial de servicios</h2>
            <a href="/Self-Management/index.php?controller=appointment&action=create">Nueva Cita</a>
            <div class="button-container">
                <button type="submit" class="button button1" data-bs-toggle="modal" data-bs-target="#formModal">Agendar cita
                </button>
            </div>
        </div>

        <!-- Modal con formulario -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">
                    <!-- FORMULARIO -->
                    <form method="POST" id="popupForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalLabel">Agendar una Cita</h5>
                            <!-- Mensaje de error -->
                            <?php if (isset($error)): ?>
                                <p style="color:red;"><?= $error ?></p>
                            <?php endif; ?>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="servicio">Servicio:</label>
                                <select id="servicio" name="servicio" required>
                                    <option value="">-- Selecciona un servicio --</option>
                                    <option value="Cambio de aceite">Cambio de aceite - 30min</option>
                                    <option value="Revisión de frenos">Revisión de frenos - 45min</option>
                                    <option value="Diagnóstico de motor">Diagnóstico de motor - 60min</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="vehiculo">Vehículo:</label>
                                <select id="vehiculo" name="vehiculo" required>
                                    <option value="">-- Selecciona tu Vehículo --</option>
                                    <option value="Chevrolet Spark">Chevrolet Spark</option>
                                    <option value="Renault Logan">Renault Logan</option>
                                    <option value="Ford Fiesta">Ford Fiesta</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="fecha">Fecha:</label>
                                <input type="date" id="fecha" name="fecha" required />
                            </div>
                            <div class="mb-3">
                                <label for="hora">Hora:</label>
                                <input type="time" id="hora" name="hora" required />
                            </div>
                            <div class="mb-3">
                                <label for="comentario">Comentario:</label>
                                <textarea id="comentario" name="comentario" rows="3" placeholder="Ej: Revisar ruido en motor..."></textarea>
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
                        <th>Fecha</th>
                        <th>Vehiculo</th>
                        <th>Servicio</th>
                        <th>Tecnico</th>
                        <th>Estado</th>
                        <th>Costo</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <form method="POST" action="/Self-Management/index.php?controller=appointment&action=create">
                        <label>Fecha: <input type="date" name="fecha" required></label><br><br>
                        <label>Hora: <input type="time" name="hora" required></label><br><br>
                        <label>Cliente: <input type="text" name="cliente" required></label><br><br>
                        <button type="submit">Guardar</button>
                    </form>

                    <!-- Mostrar error si existe -->
                    <?php if (isset($error)): ?>
                        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <?php if (isset($appointments) && $appointments->num_rows > 0): ?>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['fecha']) ?></td>
                                <td><?= htmlspecialchars($row['hora']) ?></td>
                                <td><?= htmlspecialchars($row['cliente']) ?></td>
                                <td>
                                    <form method="POST" action="/Self-Management/index.php?controller=appointment&action=delete" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta cita?');">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay citas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="separator">
            <div class="container">
                <h2>Tabla de facturación</h2>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th># Factura</th>
                            <th>Fecha</th>
                            <th>Vehículo</th>
                            <th>Servicio</th>
                            <th>Total (COP)</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                            <th>Subir comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#001</td>
                            <td>2023-05-01</td>
                            <td>Chevrolet Spark</td>
                            <td>Cambio de aceite</td>
                            <td>$150,000</td>
                            <td>Finalizado</td>
                            <td>
                                <button class="btn btn-primary btn-sm">Descargar factura</button>
                            </td>
                            <td>
                                <button class="btn btn-secondary btn-sm">Subir comprobante</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#002</td>
                            <td>2023-05-03</td>
                            <td>Renault Logan</td>
                            <td>Revisión de frenos</td>
                            <td>$200,000</td>
                            <td>Pendiente</td>
                            <td>
                                <button class="btn btn-primary btn-sm">Reagendar</button>
                            </td>
                            <td>
                                <button class="btn btn-secondary btn-sm">Subir comprobante</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#003</td>
                            <td>2023-05-05</td>
                            <td>Ford Fiesta</td>
                            <td>Diagnóstico de motor</td>
                            <td>$300,000</td>
                            <td>En proceso</td>
                            <td>
                                <button class="btn btn-primary btn-sm">Descargar comprobante</button>
                            </td>
                            <td>
                                <button class="btn btn-secondary btn-sm">Subir comprobante</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Reagendar en caso del estado del servicio
                 Descargar el comprobante #002
                 Descargar factura del servicio
                 Subir el comprobante si el pago fue manual -->
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