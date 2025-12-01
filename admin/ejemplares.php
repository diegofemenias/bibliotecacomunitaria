<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/EjemplarModel.php';
require_once '../models/Model.php';

$ejemplarModel = new EjemplarModel();
$libroModel = new Model('libros');
$message = '';
$messageType = '';

$libroId = isset($_GET['libro_id']) ? intval($_GET['libro_id']) : 0;
$libro = $libroId > 0 ? $libroModel->getById($libroId) : null;

if (!$libro) {
    redirect('libros.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'libro_id' => $libroId,
            'codigo_ejemplar' => sanitize($_POST['codigo_ejemplar']),
            'estado_fisico' => sanitize($_POST['estado_fisico'] ?? 'Bueno'),
            'estado_disponibilidad' => sanitize($_POST['estado_disponibilidad'] ?? 'Disponible'),
            'ubicacion_fisica' => sanitize($_POST['ubicacion_fisica'] ?? ''),
            'fecha_adquisicion' => !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null,
            'precio_compra' => !empty($_POST['precio_compra']) ? floatval($_POST['precio_compra']) : null,
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        
        if ($ejemplarModel->create($data)) {
            $message = 'Ejemplar creado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear ejemplar';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'codigo_ejemplar' => sanitize($_POST['codigo_ejemplar']),
            'estado_fisico' => sanitize($_POST['estado_fisico'] ?? 'Bueno'),
            'estado_disponibilidad' => sanitize($_POST['estado_disponibilidad'] ?? 'Disponible'),
            'ubicacion_fisica' => sanitize($_POST['ubicacion_fisica'] ?? ''),
            'fecha_adquisicion' => !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null,
            'precio_compra' => !empty($_POST['precio_compra']) ? floatval($_POST['precio_compra']) : null,
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        
        if ($ejemplarModel->update($id, $data)) {
            $message = 'Ejemplar actualizado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar ejemplar';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($ejemplarModel->delete($id)) {
            $message = 'Ejemplar eliminado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar ejemplar';
            $messageType = 'danger';
        }
    }
}

$ejemplares = $ejemplarModel->getByLibro($libroId);
$editing = null;
if (isset($_GET['edit'])) {
    $editing = $ejemplarModel->getById(intval($_GET['edit']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplares - <?php echo htmlspecialchars($libro['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2">Ejemplares</h1>
                <p class="text-muted mb-0">Libro: <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong></p>
            </div>
            <a href="libros.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Libros
            </a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $editing ? 'Editar' : 'Nuevo'; ?> Ejemplar</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editing): ?>
                                <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Código del Ejemplar *</label>
                                <input type="text" class="form-control" name="codigo_ejemplar" 
                                       value="<?php echo $editing ? htmlspecialchars($editing['codigo_ejemplar']) : ''; ?>" 
                                       required placeholder="Ej: <?php echo htmlspecialchars($libro['codigo_interno']); ?>-E1">
                                <small class="form-text text-muted">Código único para este ejemplar</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado Físico</label>
                                <select class="form-select" name="estado_fisico">
                                    <option value="Excelente" <?php echo $editing && $editing['estado_fisico'] == 'Excelente' ? 'selected' : ''; ?>>Excelente</option>
                                    <option value="Bueno" <?php echo $editing && ($editing['estado_fisico'] == 'Bueno' || !$editing) ? 'selected' : ''; ?>>Bueno</option>
                                    <option value="Regular" <?php echo $editing && $editing['estado_fisico'] == 'Regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="Malo" <?php echo $editing && $editing['estado_fisico'] == 'Malo' ? 'selected' : ''; ?>>Malo</option>
                                    <option value="Requiere reparación" <?php echo $editing && $editing['estado_fisico'] == 'Requiere reparación' ? 'selected' : ''; ?>>Requiere reparación</option>
                                    <option value="Perdido" <?php echo $editing && $editing['estado_fisico'] == 'Perdido' ? 'selected' : ''; ?>>Perdido</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado de Disponibilidad</label>
                                <select class="form-select" name="estado_disponibilidad">
                                    <option value="Disponible" <?php echo $editing && ($editing['estado_disponibilidad'] == 'Disponible' || !$editing) ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="Prestado" <?php echo $editing && $editing['estado_disponibilidad'] == 'Prestado' ? 'selected' : ''; ?>>Prestado</option>
                                    <option value="Reservado" <?php echo $editing && $editing['estado_disponibilidad'] == 'Reservado' ? 'selected' : ''; ?>>Reservado</option>
                                    <option value="En reparación" <?php echo $editing && $editing['estado_disponibilidad'] == 'En reparación' ? 'selected' : ''; ?>>En reparación</option>
                                    <option value="No disponible" <?php echo $editing && $editing['estado_disponibilidad'] == 'No disponible' ? 'selected' : ''; ?>>No disponible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ubicación Física</label>
                                <input type="text" class="form-control" name="ubicacion_fisica" 
                                       value="<?php echo $editing ? htmlspecialchars($editing['ubicacion_fisica'] ?? '') : ''; ?>" 
                                       placeholder="Ej: Estante A-3, Fila 2">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Adquisición</label>
                                <input type="date" class="form-control" name="fecha_adquisicion" 
                                       value="<?php echo $editing && $editing['fecha_adquisicion'] ? $editing['fecha_adquisicion'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Precio de Compra</label>
                                <input type="number" step="0.01" class="form-control" name="precio_compra" 
                                       value="<?php echo $editing ? $editing['precio_compra'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notas</label>
                                <textarea class="form-control" name="notas" rows="3"><?php echo $editing ? htmlspecialchars($editing['notas'] ?? '') : ''; ?></textarea>
                            </div>
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?> Ejemplar
                            </button>
                            <?php if ($editing): ?>
                                <a href="?libro_id=<?php echo $libroId; ?>" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Ejemplares (<?php echo count($ejemplares); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Estado Físico</th>
                                        <th>Disponibilidad</th>
                                        <th>Ubicación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ejemplares as $ejemplar): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($ejemplar['codigo_ejemplar']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $ejemplar['estado_fisico'] == 'Excelente' ? 'success' : 
                                                        ($ejemplar['estado_fisico'] == 'Bueno' ? 'primary' : 
                                                        ($ejemplar['estado_fisico'] == 'Regular' ? 'warning' : 'danger')); 
                                                ?>">
                                                    <?php echo htmlspecialchars($ejemplar['estado_fisico']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $ejemplar['estado_disponibilidad'] == 'Disponible' ? 'success' : 
                                                        ($ejemplar['estado_disponibilidad'] == 'Prestado' ? 'danger' : 
                                                        ($ejemplar['estado_disponibilidad'] == 'Reservado' ? 'warning' : 'secondary')); 
                                                ?>">
                                                    <?php echo htmlspecialchars($ejemplar['estado_disponibilidad']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($ejemplar['ubicacion_fisica'] ?? '-'); ?></td>
                                            <td>
                                                <a href="?libro_id=<?php echo $libroId; ?>&edit=<?php echo $ejemplar['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este ejemplar?');">
                                                    <input type="hidden" name="id" value="<?php echo $ejemplar['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>


