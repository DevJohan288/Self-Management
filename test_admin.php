<?php
session_start();

$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Administrador';
$_SESSION['user_role'] = 1;

header('Location: /Self-Management/app/views/admin/admin_dashboard.php');
exit;
