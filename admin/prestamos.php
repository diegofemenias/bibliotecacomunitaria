<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

$model = new Model('prestamos');
$libroModel = new Model('libros');
$usuarioModel = new Model('usuarios');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $libroId = intval($_POST['libro_id']);
        $ejemplarId = intval($_POST['ejemplar_id']);
        $usuarioId = intval($_POST['usuario_id']);
        $diasPrestamo = intval($_POST['dias_prestamo'] ?? 14);
        
        $data = [
            'libro_id' => $libroId,
            'ejemplar_id' => $ejemplarId,
            'usuario_id' => $usuarioId,
            'fecha_prestamo' => date('Y-m-d'),
            'fecha_vencimiento' => date('Y-m-d', strtotime("+{$diasPrestamo} days")),
            'estado' => 'Activo'
        ];
        
        if ($model->create($data)) {
            $ejemplarModel = new Model('ejemplares');
            $ejemplarModel->update($ejemplarId, ['estado_disponibilidad' => 'Prestado']);
            $message = 'Préstamo creado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear préstamo';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        
        // Obtener el préstamo original para verificar el estado anterior
        $prestamoOriginal = $model->getById($id);
        
        $data = [
            'fecha_devolucion' => !empty($_POST['fecha_devolucion']) ? $_POST['fecha_devolucion'] : null,
            'estado' => sanitize($_POST['estado']),
            'multa' => !empty($_POST['multa']) ? floatval($_POST['multa']) : 0.00,
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        
        // Si se establece fecha de devolución, el estado debe ser Devuelto
        if (!empty($data['fecha_devolucion'])) {
            $data['estado'] = 'Devuelto';
        }
        
        // Actualizar estado del ejemplar si el préstamo se marca como Devuelto
        if ($data['estado'] == 'Devuelto' && $prestamoOriginal && $prestamoOriginal['ejemplar_id']) {
            $ejemplarModel = new Model('ejemplares');
            $ejemplarModel->update($prestamoOriginal['ejemplar_id'], ['estado_disponibilidad' => 'Disponible']);
        }
        
        if ($model->update($id, $data)) {
            $message = 'Préstamo actualizado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar préstamo';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Préstamo eliminado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar préstamo';
            $messageType = 'danger';
        }
    }
}

$prestamos = $model->query("
    SELECT p.*, l.titulo AS libro_titulo, l.codigo_interno, 
           e.codigo_ejemplar,
           u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.numero_cedula
    FROM prestamos p
    INNER JOIN libros l ON p.libro_id = l.id
    INNER JOIN ejemplares e ON p.ejemplar_id = e.id
    INNER JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.estado != 'Devuelto' OR p.estado IS NULL
    ORDER BY p.fecha_prestamo DESC
");

// Obtener préstamos históricos (devueltos)
$prestamosHistoricos = $model->query("
    SELECT p.*, l.titulo AS libro_titulo, l.codigo_interno, 
           e.codigo_ejemplar,
           u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.numero_cedula
    FROM prestamos p
    INNER JOIN libros l ON p.libro_id = l.id
    INNER JOIN ejemplares e ON p.ejemplar_id = e.id
    INNER JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.estado = 'Devuelto'
    ORDER BY p.fecha_prestamo DESC
");

// Obtener libros con ejemplares disponibles
$libros = $libroModel->query("
    SELECT DISTINCT l.*
    FROM libros l
    INNER JOIN ejemplares e ON l.id = e.libro_id
    WHERE e.estado_disponibilidad = 'Disponible' 
    AND l.deleted_at IS NULL
    AND e.deleted_at IS NULL
    ORDER BY l.titulo ASC
");
$usuarios = $usuarioModel->getAll();

$editing = null;
if (isset($_GET['edit'])) {
    $editing = $model->getById(intval($_GET['edit']));
    if ($editing) {
        $editing['libro'] = $libroModel->getById($editing['libro_id']);
        $editing['usuario'] = $usuarioModel->getById($editing['usuario_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Préstamos</h1>
        
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
                        <h5><?php echo $editing ? 'Editar' : 'Nuevo'; ?> Préstamo</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editing): ?>
                                <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Libro</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editing['libro']['titulo']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editing['usuario']['nombre'] . ' ' . $editing['usuario']['apellido']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Devolución</label>
                                    <input type="date" class="form-control" name="fecha_devolucion" value="<?php echo $editing['fecha_devolucion'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="estado">
                                        <option value="Activo" <?php echo $editing['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="Devuelto" <?php echo $editing['estado'] == 'Devuelto' ? 'selected' : ''; ?>>Devuelto</option>
                                        <option value="Vencido" <?php echo $editing['estado'] == 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                                        <option value="Perdido" <?php echo $editing['estado'] == 'Perdido' ? 'selected' : ''; ?>>Perdido</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Multa</label>
                                    <input type="number" step="0.01" class="form-control" name="multa" value="<?php echo $editing['multa'] ?? 0; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notas</label>
                                    <textarea class="form-control" name="notas" rows="3"><?php echo htmlspecialchars($editing['notas'] ?? ''); ?></textarea>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label">Libro *</label>
                                    <select class="form-select" name="libro_id" id="libro_id" required onchange="cargarEjemplares(this.value)">
                                        <option value="">Seleccionar libro</option>
                                        <?php if (empty($libros)): ?>
                                            <option value="" disabled>No hay libros disponibles</option>
                                        <?php else: ?>
                                            <?php foreach ($libros as $libro): ?>
                                                <option value="<?php echo $libro['id']; ?>">
                                                    <?php echo htmlspecialchars($libro['codigo_interno'] . ' - ' . $libro['titulo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (empty($libros)): ?>
                                        <small class="form-text text-danger">No hay libros con ejemplares disponibles para préstamo.</small>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ejemplar *</label>
                                    <select class="form-select" name="ejemplar_id" id="ejemplar_id" required disabled>
                                        <option value="">Primero seleccione un libro</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuario *</label>
                                    <select class="form-select" name="usuario_id" required>
                                        <option value="">Seleccionar usuario</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['id']; ?>">
                                                <?php echo htmlspecialchars($usuario['numero_cedula'] . ' - ' . $usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Días de Préstamo</label>
                                    <input type="number" class="form-control" name="dias_prestamo" value="14" min="1" max="90">
                                </div>
                            <?php endif; ?>
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="prestamos.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Préstamos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libro</th>
                                        <th>Ejemplar</th>
                                        <th>Usuario</th>
                                        <th>Fecha Préstamo</th>
                                        <th>Vencimiento</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prestamos as $prestamo): ?>
                                        <?php
                                        $diasVencido = 0;
                                        if ($prestamo['fecha_vencimiento'] && $prestamo['estado'] == 'Activo') {
                                            $diasVencido = (strtotime(date('Y-m-d')) - strtotime($prestamo['fecha_vencimiento'])) / 86400;
                                        }
                                        ?>
                                        <tr class="<?php echo $diasVencido > 0 ? 'table-warning' : ''; ?>">
                                            <td><?php echo $prestamo['id']; ?></td>
                                            <td><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($prestamo['codigo_ejemplar'] ?? '-'); ?></strong></td>
                                            <td><?php echo htmlspecialchars($prestamo['usuario_nombre'] . ' ' . $prestamo['usuario_apellido']); ?></td>
                                            <td><?php echo $prestamo['fecha_prestamo']; ?></td>
                                            <td><?php echo $prestamo['fecha_vencimiento']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $prestamo['estado'] == 'Activo' ? 'primary' : 
                                                        ($prestamo['estado'] == 'Devuelto' ? 'success' : 'danger'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($prestamo['estado']); ?>
                                                </span>
                                                <?php if ($diasVencido > 0): ?>
                                                    <span class="badge bg-warning"><?php echo round($diasVencido); ?> días vencido</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $prestamo['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este préstamo?');">
                                                    <input type="hidden" name="id" value="<?php echo $prestamo['id']; ?>">
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
        
        <!-- Lista de Préstamos Histórico -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Préstamos Histórico</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libro</th>
                                        <th>Ejemplar</th>
                                        <th>Usuario</th>
                                        <th>Fecha Préstamo</th>
                                        <th>Vencimiento</th>
                                        <th>Fecha Devolución</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($prestamosHistoricos)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No hay préstamos históricos</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($prestamosHistoricos as $prestamo): ?>
                                            <tr>
                                                <td><?php echo $prestamo['id']; ?></td>
                                                <td><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></td>
                                                <td><strong><?php echo htmlspecialchars($prestamo['codigo_ejemplar'] ?? '-'); ?></strong></td>
                                                <td><?php echo htmlspecialchars($prestamo['usuario_nombre'] . ' ' . $prestamo['usuario_apellido']); ?></td>
                                                <td><?php echo $prestamo['fecha_prestamo']; ?></td>
                                                <td><?php echo $prestamo['fecha_vencimiento']; ?></td>
                                                <td><?php echo $prestamo['fecha_devolucion'] ?? '-'; ?></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo htmlspecialchars($prestamo['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?edit=<?php echo $prestamo['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este préstamo?');">
                                                        <input type="hidden" name="id" value="<?php echo $prestamo['id']; ?>">
                                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    
    <script>
    function cargarEjemplares(libroId) {
        const ejemplarSelect = document.getElementById('ejemplar_id');
        ejemplarSelect.innerHTML = '<option value="">Cargando...</option>';
        ejemplarSelect.disabled = true;
        
        if (!libroId) {
            ejemplarSelect.innerHTML = '<option value="">Primero seleccione un libro</option>';
            return;
        }
        
        fetch(`ajax_ejemplares.php?libro_id=${libroId}`)
            .then(response => response.json())
            .then(data => {
                ejemplarSelect.innerHTML = '<option value="">Seleccionar ejemplar</option>';
                if (data.success && data.ejemplares.length > 0) {
                    data.ejemplares.forEach(ejemplar => {
                        const option = document.createElement('option');
                        option.value = ejemplar.id;
                        option.textContent = `${ejemplar.codigo_ejemplar} - ${ejemplar.estado_fisico}`;
                        ejemplarSelect.appendChild(option);
                    });
                    ejemplarSelect.disabled = false;
                } else {
                    ejemplarSelect.innerHTML = '<option value="">No hay ejemplares disponibles</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ejemplarSelect.innerHTML = '<option value="">Error al cargar ejemplares</option>';
            });
    }
    </script>
</body>
</html>

