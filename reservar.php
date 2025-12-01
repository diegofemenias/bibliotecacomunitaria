<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/ReservaModel.php';
require_once 'models/LibroModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$libroId = intval($_POST['libro_id'] ?? 0);
$cedula = sanitize($_POST['cedula'] ?? '');

if (empty($libroId) || empty($cedula)) {
    $_SESSION['error'] = 'Datos incompletos';
    redirect('index.php');
}

try {
    require_once 'models/EjemplarModel.php';
    
    $reservaModel = new ReservaModel();
    $libroModel = new LibroModel();
    $ejemplarModel = new EjemplarModel();
    
    // Verificar que el libro existe
    $libro = $libroModel->getById($libroId);
    if (!$libro) {
        $_SESSION['error'] = 'El libro no existe';
        redirect('index.php');
    }
    
    // Verificar que hay ejemplares disponibles
    $ejemplaresDisponibles = $ejemplarModel->contarDisponibles($libroId);
    if ($ejemplaresDisponibles == 0) {
        $_SESSION['error'] = 'No hay ejemplares disponibles para reserva';
        redirect('index.php');
    }
    
    // Obtener la IP del cliente para validación
    $ipAddress = getClientIp();
    
    $reservaId = $reservaModel->crearReserva($libroId, $cedula, $ipAddress);
    $_SESSION['success'] = 'Reserva realizada exitosamente. Tiene ' . DIAS_VALIDEZ_RESERVA . ' días para retirar el libro.';
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

redirect('index.php');

