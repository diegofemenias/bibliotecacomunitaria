<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/EjemplarModel.php';

header('Content-Type: application/json');

$libroId = intval($_GET['libro_id'] ?? 0);

if ($libroId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de libro inválido']);
    exit;
}

$ejemplarModel = new EjemplarModel();
$ejemplares = $ejemplarModel->getDisponiblesByLibro($libroId);

echo json_encode([
    'success' => true,
    'ejemplares' => $ejemplares
]);


