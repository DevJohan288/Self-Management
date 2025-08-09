<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios solicitados</title>
    <!-- Libraries -->
    <!-- Bootstrap 5 JS y CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Para gráficas -->
    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/login_style.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
    <!-- Favicon/images -->
    <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png">
    <!-- JS Scripts -->
    <script src="/Self-Management/public/js/icon-theme.js"></script>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_client.php'; ?>
    <main>
        <div class="text-content">
            <h2>Vehiculos registrados</h2>
            <div class="button-container">
                <button type="button" class="button button1" data-bs-toggle="modal" data-bs-target="#formVehicle" id="abrirModal">Agregar Vehiculo
                </button>
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

        <div class="container">
            <h2>Listado de Vehículos</h2>
            <table class="user-table">
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
                        <th>Acciones</th>
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
                                <!-- Botón para descargar factura -->
                                <td>
                                    <button class="btn btn-primary btn-sm">Descargar factura</button>
                                </td>
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
    <script src="/Self-Management/public/js/modal-loader.js"></script>
</body>

</html>