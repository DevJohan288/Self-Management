<?php
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function getEstadoBadge($estado) {
    $badges = [
        'pendiente' => '<span class="badge badge-warning">Pendiente</span>',
        'confirmada' => '<span class="badge badge-info">Confirmada</span>',
        'en_proceso' => '<span class="badge badge-primary">En Proceso</span>',
        'completada' => '<span class="badge badge-success">Completada</span>',
        'cancelada' => '<span class="badge badge-danger">Cancelada</span>'
    ];
    return $badges[$estado] ?? $estado;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function showError($message) {
    return "<div class='alert alert-error'>$message</div>";
}

function showSuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}
?>
