<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$tipo = sanitize($_POST['tipo'] ?? '');
$response = ['success' => false, 'message' => 'Tipo no válido'];

try {
    switch ($tipo) {
        case 'editorial':
            $model = new Model('editoriales');
            $data = [
                'nombre' => sanitize($_POST['nombre'] ?? ''),
                'pais' => sanitize($_POST['pais'] ?? ''),
                'sitio_web' => sanitize($_POST['sitio_web'] ?? '')
            ];
            if (empty($data['nombre'])) {
                throw new Exception('El nombre es requerido');
            }
            $id = $model->create($data);
            if ($id) {
                $nuevo = $model->getById($id);
                $response = ['success' => true, 'id' => $id, 'nombre' => $nuevo['nombre'], 'data' => $nuevo];
            }
            break;
            
        case 'autor':
            $model = new Model('autores');
            $data = [
                'nombre' => sanitize($_POST['nombre'] ?? ''),
                'apellido' => sanitize($_POST['apellido'] ?? ''),
                'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                'fecha_fallecimiento' => !empty($_POST['fecha_fallecimiento']) ? $_POST['fecha_fallecimiento'] : null,
                'nacionalidad' => sanitize($_POST['nacionalidad'] ?? ''),
                'biografia' => sanitize($_POST['biografia'] ?? '')
            ];
            if (empty($data['nombre'])) {
                throw new Exception('El nombre es requerido');
            }
            $id = $model->create($data);
            if ($id) {
                $nuevo = $model->getById($id);
                $response = ['success' => true, 'id' => $id, 'nombre' => $nuevo['nombre_completo'], 'data' => $nuevo];
            }
            break;
            
        case 'categoria':
            $model = new Model('categorias');
            $data = [
                'nombre' => sanitize($_POST['nombre'] ?? ''),
                'descripcion' => sanitize($_POST['descripcion'] ?? ''),
                'categoria_padre_id' => !empty($_POST['categoria_padre_id']) ? intval($_POST['categoria_padre_id']) : null
            ];
            if (empty($data['nombre'])) {
                throw new Exception('El nombre es requerido');
            }
            $id = $model->create($data);
            if ($id) {
                $nuevo = $model->getById($id);
                $response = ['success' => true, 'id' => $id, 'nombre' => $nuevo['nombre'], 'data' => $nuevo];
            }
            break;
            
        case 'tag':
            $model = new Model('tags');
            $data = [
                'nombre' => sanitize($_POST['nombre'] ?? '')
            ];
            if (empty($data['nombre'])) {
                throw new Exception('El nombre es requerido');
            }
            $id = $model->create($data);
            if ($id) {
                $nuevo = $model->getById($id);
                $response = ['success' => true, 'id' => $id, 'nombre' => $nuevo['nombre'], 'data' => $nuevo];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Tipo no reconocido'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);


