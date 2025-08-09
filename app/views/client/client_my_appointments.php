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
                    <form id="popupForm" action="/self-management/index.php?controller=appointment&action=create" method="POST">
                        <!-- Servicio -->
                        <div class="mb-3">
                            <label for="servicio">Servicio:</label>
                            <select id="servicio" name="servicio" required>
                                <option value="">-- Selecciona un servicio --</option>
                                <option value="aceite">Cambio de aceite - 30min</option>
                                <option value="frenos">Revisión de frenos - 45min</option>
                                <option value="motor">Diagnóstico de motor - 60min</option>
                            </select>
                        </div>

                        <!-- Vehículo -->
                        <div class="mb-3">
                            <label for="vehiculo">Vehículo:</label>
                            <input type="text" id="vehiculo" name="vehiculo" placeholder="Ej: Toyota Corolla 2020" required />
                        </div>

                        <!-- Fecha -->
                        <div class="mb-3">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" required min="<?= date('Y-m-d'); ?>" />
                        </div>

                        <!-- Hora -->
                        <div class="mb-3">
                            <label for="hora">Hora:</label>
                            <input type="time" id="hora" name="hora" required />
                        </div>

                        <!-- Comentario -->
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario adicional (opcional):</label>
                            <textarea id="comentario" name="comentario" rows="3" placeholder="Ej: Quiero revisar un ruido al frenar."></textarea>
                        </div>

                        <!-- Cliente oculto -->
                        <input type="hidden" name="cliente" value="<?= $_SESSION['nombre'] ?? 'Invitado'; ?>" />

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </div>
                    </form>
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
    </main>
</body>

</html>