<?php
// Inicia la sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS Styles -->
    <link rel="stylesheet" href="/Self-Management/public/css/styles.css">
    <link rel="stylesheet" href="/Self-Management/public/css/normalize.css">
    <!-- Libreries -->
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <nav id="sidebar">
        <ul>
            <!--Item menu-->
            <li>
                <span class="logo">
                    Bienvenido, <?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Usuario'; ?>
                </span>
                <button onclick=toggleSidebar() id="toggle-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e8eaed">
                            <path
                                d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z" />
                        </svg>
                    </button>
            </li>
            <!--Item 1-->
            <li>
                <a href="/Self-Management/app/views/client/client_home.php">
                    <img src="/Self-Management/public/images/icon/icon-home.svg" alt="Home" height="24px" width="24px">
                    <span>Home</span>
                </a>
            </li>
            <!--Item 2-->
            <li class="active">
                <a href="/Self-Management/app/views/client/client_dashboard.php">
                    <img src="/Self-Management/public/images/icon/icon_dashboard.svg" alt="Home" height="24px" width="24px">
                    <span>Dashboard</span>
                </a>
            </li>

            <!--Item 3 Multiples-->
            <li>
                <button onclick=toggleSubMenu(this) class="dropdown-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                        <path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h207q16 0 30.5 6t25.5 17l57 57h320q33 0 56.5 23.5T880-640v400q0 33-23.5 56.5T800-160H160Zm0-80h640v-400H447l-80-80H160v480Zm0 0v-480 480Zm400-160v40q0 17 11.5 28.5T600-320q17 0 28.5-11.5T640-360v-40h40q17 0 28.5-11.5T720-440q0-17-11.5-28.5T680-480h-40v-40q0-17-11.5-28.5T600-560q-17 0-28.5 11.5T560-520v40h-40q-17 0-28.5 11.5T480-440q0 17 11.5 28.5T520-400h40Z" />
                    </svg>
                    <span>Servicios</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                        <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z" />
                    </svg>
                </button>
                <ul class="sub-menu">
                    <div>
                        <li>
                            <a href="/Self-Management/app/views/client/client_my_appointments.php">
                                <img src="/Self-Management/public/images/icon/icon-table.svg" alt="Home" height="24px" width="24px">
                                <span>Mis Citas</span>
                            </a>
                        </li>
                        <li>
                            <a href="/Self-Management/app/views/client/client_appointment_history.php">
                                <img src="/Self-Management/public/images/icon/icon-table.svg" alt="Home" height="24px" width="24px">
                                <span>Historial de servicios</span>
                            </a>
                        </li>
                    </div>
                </ul>
            </li>

            <!--Item 4-->
            <li>
                <a href="/Self-Management/index.php?controller=vehicle&action=listarTodos">
                    <img src="/Self-Management/public/images/icon/icon-table.svg" alt="Home" height="24px" width="24px">
                    <span>Mis Vehiculos</span>
                </a>
            </li>

            <!--Item 4-->
            <li>
                <a href="/Self-Management/app/views/client/client_settings.php">
                    <img src="/Self-Management/public/images/icon/icon-table.svg" alt="Home" height="24px" width="24px">
                    <span>Configuraciones</span>
                </a>
            </li>
            <!--Item 8: /Self-Management/app/controllers/AuthController.php?action=logout-->
              <li>
                    <a href="#" id="logout-btn">
                        <img src="/Self-Management/public/images/icon/icon-logout.svg" alt="Home" height="24px" width="24px">
                        <span>Cerrar sesion</span>
                    </a>
                </li>
        </ul>

    </nav>
    <script>
        const toggleButton = document.getElementById('toggle-btn')
        const sidebar = document.getElementById('sidebar')

        function toggleSidebar() {
            sidebar.classList.toggle('close')
            toggleButton.classList.toggle('rotate')

            closeAllSubMenus()
        }

        function toggleSubMenu(button) {

            if (!button.nextElementSibling.classList.contains('show')) {
                closeAllSubMenus()
            }

            button.nextElementSibling.classList.toggle('show')
            button.classList.toggle('rotate')

            if (sidebar.classList.contains('close')) {
                sidebar.classList.toggle('close')
                toggleButton.classList.toggle('rotate')
            }
        }

        function closeAllSubMenus() {
            Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
                ul.classList.remove('show')
                ul.previousElementSibling.classList.remove('rotate')
            })
        }
    </script>
    <script src="/Self-Management/public/js/logout.js"></script>
</body>
</html>