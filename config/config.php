<?php

// Configuración general
define('APP_NAME', 'Biblioteca Comunitaria');
define('APP_URL', 'http://localhost:8888/biblio');

// Configuración de sesión
session_start();

// Zona horaria
date_default_timezone_set('America/Montevideo');

// Configuración de administrador (password hardcodeada)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Cambiar en producción

// Configuración de reservas
define('DIAS_VALIDEZ_RESERVA', 7); // Días que dura una reserva

// Helper para redirecciones
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Helper para verificar si es admin
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Helper para requerir autenticación admin
function requireAdmin() {
    if (!isAdmin()) {
        redirect('login.php');
    }
}

// Helper para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

