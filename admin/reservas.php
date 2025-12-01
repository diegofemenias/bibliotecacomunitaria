<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';
require_once '../models/EjemplarModel.php';

$model = new Model('reservas');
$libroModel = new Model('libros');
$usuarioModel = new Model('usuarios');
$prestamoModel = new Model('prestamos');
$ejemplarModel = new EjemplarModel();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $nuevoEstado = sanitize($_POST['estado']);
        
        // Obtener la reserva original para verificar el estado anterior
        $reservaOriginal = $model->getById($id);
        
        if (!$reservaOriginal) {
            $message = 'Reserva no encontrada';
            $messageType = 'danger';
        } else {
            $estadoAnterior = $reservaOriginal['estado'];
            $data = [
                'estado' => $nuevoEstado,
                'fecha_notificacion' => !empty($_POST['fecha_notificacion']) ? $_POST['fecha_notificacion'] : null
            ];
            
            // Si cambia de Pendiente a Completada, crear préstamo automáticamente
            if ($estadoAnterior == 'Pendiente' && $nuevoEstado == 'Completada') {
                try {
                    // Obtener la conexión de base de datos
                    $db = Database::getInstance()->getConnection();
                    $db->beginTransaction();
                    
                    // Obtener un ejemplar disponible del libro
                    $ejemplaresDisponibles = $ejemplarModel->getDisponiblesByLibro($reservaOriginal['libro_id']);
                    
                    if (empty($ejemplaresDisponibles)) {
                        throw new Exception('No hay ejemplares disponibles para este libro');
                    }
                    
                    // Tomar el primer ejemplar disponible
                    $ejemplar = $ejemplaresDisponibles[0];
                    $ejemplarId = $ejemplar['id'];
                    
                    // Crear el préstamo
                    $diasPrestamo = 14; // Días por defecto para préstamos
                    $prestamoData = [
                        'libro_id' => $reservaOriginal['libro_id'],
                        'ejemplar_id' => $ejemplarId,
                        'usuario_id' => $reservaOriginal['usuario_id'],
                        'fecha_prestamo' => date('Y-m-d'),
                        'fecha_vencimiento' => date('Y-m-d', strtotime("+{$diasPrestamo} days")),
                        'estado' => 'Activo'
                    ];
                    
                    $prestamoId = $prestamoModel->create($prestamoData);
                    
                    if (!$prestamoId) {
                        throw new Exception('Error al crear el préstamo');
                    }
                    
                    // Actualizar el ejemplar a "Prestado"
                    $ejemplarModel->update($ejemplarId, ['estado_disponibilidad' => 'Prestado']);
                    
                    // Actualizar la reserva
                    if ($model->update($id, $data)) {
                        $db->commit();
                        $message = 'Reserva completada y préstamo creado exitosamente';
                        $messageType = 'success';
                    } else {
                        throw new Exception('Error al actualizar la reserva');
                    }
                } catch (Exception $e) {
                    if (isset($db)) {
                        $db->rollBack();
                    }
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            } else {
                // Actualización normal sin crear préstamo
                if ($model->update($id, $data)) {
                    $message = 'Reserva actualizada exitosamente';
                    $messageType = 'success';
                } else {
                    $message = 'Error al actualizar reserva';
                    $messageType = 'danger';
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Reserva eliminada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar reserva';
            $messageType = 'danger';
        }
    }
}

$reservas = $model->query("
    SELECT r.*, l.titulo AS libro_titulo, l.codigo_interno, 
           u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.numero_cedula
    FROM reservas r
    INNER JOIN libros l ON r.libro_id = l.id
    INNER JOIN usuarios u ON r.usuario_id = u.id
    ORDER BY r.fecha_reserva DESC
");

$editing = null;
if (isset($_GET['edit'])) {
    $editing = $model->getById(intval($_GET['edit']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Reservas</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5>Lista de Reservas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libro</th>
                                <th>Usuario</th>
                                <th>Fecha Reserva</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <?php
                                $diasVencido = 0;
                                if ($reserva['fecha_vencimiento'] && $reserva['estado'] == 'Pendiente') {
                                    $diasVencido = (strtotime(date('Y-m-d')) - strtotime($reserva['fecha_vencimiento'])) / 86400;
                                }
                                ?>
                                <tr class="<?php echo $diasVencido > 0 ? 'table-warning' : ''; ?>">
                                    <td><?php echo $reserva['id']; ?></td>
                                    <td><?php echo htmlspecialchars($reserva['libro_titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['usuario_nombre'] . ' ' . $reserva['usuario_apellido'] . ' (' . $reserva['numero_cedula'] . ')'); ?></td>
                                    <td><?php echo $reserva['fecha_reserva']; ?></td>
                                    <td><?php echo $reserva['fecha_vencimiento']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $reserva['estado'] == 'Pendiente' ? 'warning' : 
                                                ($reserva['estado'] == 'Completada' ? 'success' : 'secondary'); 
                                        ?>">
                                            <?php echo htmlspecialchars($reserva['estado']); ?>
                                        </span>
                                        <?php if ($diasVencido > 0): ?>
                                            <span class="badge bg-danger">Vencida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $reserva['id']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta reserva?');">
                                            <input type="hidden" name="id" value="<?php echo $reserva['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal de Edición -->
                                <div class="modal fade" id="editModal<?php echo $reserva['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Reserva</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $reserva['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Estado</label>
                                                        <select class="form-select" name="estado">
                                                            <option value="Pendiente" <?php echo $reserva['estado'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                            <option value="Completada" <?php echo $reserva['estado'] == 'Completada' ? 'selected' : ''; ?>>Completada</option>
                                                            <option value="Cancelada" <?php echo $reserva['estado'] == 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                                            <option value="Vencida" <?php echo $reserva['estado'] == 'Vencida' ? 'selected' : ''; ?>>Vencida</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha de Notificación</label>
                                                        <input type="date" class="form-control" name="fecha_notificacion" value="<?php echo $reserva['fecha_notificacion'] ?? ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" name="update" class="btn btn-primary">Actualizar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>


