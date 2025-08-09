<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuraciones</title>

    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/styles.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Para gráficas -->

    <!-- Favicon/images -->
    <link id="favicon" rel="icon" type="image/png" href="/Self-Management/public/images/short_lg-dark.png">
    <!-- JS Scripts -->
    <script src="/Self-Management/public/js/icon-theme.js"></script>
</head>

<body>
    <!--Include componenet: Sidebar -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Self-Management/app/views/shared/sidebar_client.php'; ?>
    <main class="config-main">
        <h3>Configuraciones</h3>
        <div class="separator">
            <div class="container">
                <h3>Información Personal</h3>
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" placeholder="Tu nombre completo">

                <label for="telefono">Número telefónico</label>
                <input type="tel" id="telefono" placeholder="+34 600 000 000">

                <label for="email">Correo electrónico</label>
                <input type="email" id="email" placeholder="tuemail@ejemplo.com">
            </div>
        </div>

        <div class="separator">
            <h3>Seguridad</h3>
            <div class="container">

                <label for="nueva-contrasena">Nueva contraseña</label>
                <input type="password" id="nueva-contrasena" placeholder="**********">

                <label for="confirmar-contrasena">Confirmar nueva contraseña</label>
                <input type="password" id="confirmar-contrasena" placeholder="**********">
            </div>
        </div>

        <div class="separator">
            <h3>Preferencias Generales</h3>
            <div class="container">

                <label for="idioma">Idioma preferido</label>
                <select id="idioma">
                    <option value="es">Español</option>
                    <option value="en">Inglés</option>
                </select>

                <label for="zona-horaria">Zona horaria</label>
                <select id="zona-horaria">
                    <option value="Europe/Madrid">Europa/Madrid (GMT+1)</option>
                    <option value="America/Mexico_City">América/México (GMT-6)</option>
                    <option value="America/Bogota">América/Bogotá (GMT-5)</option>
                    <option value="America/Argentina/Buenos_Aires">Buenos Aires (GMT-3)</option>
                </select>
            </div>
        </div>

        <div class="separator">
            <h3>Notificaciones</h3>
            <div class="container">

                <label for="contacto-preferido">Método preferido de contacto</label>
                <select id="contacto-preferido">
                    <option value="email">Correo electrónico</option>
                    <option value="telefono">Llamada telefónica</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>

                <div class="checkbox-group">
                    <input type="checkbox" id="notif-citas" checked>
                    <label for="notif-citas">Recibir recordatorios de citas</label><br>

                    <input type="checkbox" id="notif-promos">
                    <label for="notif-promos">Recibir promociones y novedades</label>
                </div>
            </div>
        </div>

        <div class="separator">
            <div class="container">
                <button class="button success" id="guardar-cambios">Guardar Cambios</button>
                <button class="button cancel" id="cancelar-cambios">Cancelar</button>
            </div>
        </div>
    </main>
</body>

</html>