<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

$model = new Model('resenas');
$libroModel = new Model('libros');
$usuarioModel = new Model('usuarios');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'aprobada' => isset($_POST['aprobada']) ? 1 : 0
        ];
        if ($model->update($id, $data)) {
            $message = 'Reseña actualizada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar reseña';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Reseña eliminada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar reseña';
            $messageType = 'danger';
        }
    }
}

$resenas = $model->query("
    SELECT r.*, l.titulo AS libro_titulo, 
           u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
    FROM resenas r
    INNER JOIN libros l ON r.libro_id = l.id
    INNER JOIN usuarios u ON r.usuario_id = u.id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseñas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Reseñas</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5>Lista de Reseñas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libro</th>
                                <th>Usuario</th>
                                <th>Calificación</th>
                                <th>Comentario</th>
                                <th>Aprobada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resenas as $resena): ?>
                                <tr>
                                    <td><?php echo $resena['id']; ?></td>
                                    <td><?php echo htmlspecialchars($resena['libro_titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($resena['usuario_nombre'] . ' ' . $resena['usuario_apellido']); ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $resena['calificacion'] ? '-fill text-warning' : ''; ?>"></i>
                                        <?php endfor; ?>
                                        (<?php echo $resena['calificacion']; ?>/5)
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($resena['comentario'] ?? '', 0, 50)) . (strlen($resena['comentario'] ?? '') > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $resena['aprobada'] ? 'success' : 'warning'; ?>">
                                            <?php echo $resena['aprobada'] ? 'Aprobada' : 'Pendiente'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $resena['id']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta reseña?');">
                                            <input type="hidden" name="id" value="<?php echo $resena['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal de Edición -->
                                <div class="modal fade" id="editModal<?php echo $resena['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Reseña</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $resena['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Libro</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($resena['libro_titulo']); ?>" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Usuario</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($resena['usuario_nombre'] . ' ' . $resena['usuario_apellido']); ?>" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Calificación</label>
                                                        <input type="text" class="form-control" value="<?php echo $resena['calificacion']; ?>/5" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Comentario</label>
                                                        <textarea class="form-control" rows="4" readonly><?php echo htmlspecialchars($resena['comentario'] ?? ''); ?></textarea>
                                                    </div>
                                                    <div class="mb-3 form-check">
                                                        <input type="checkbox" class="form-check-input" name="aprobada" id="aprobada<?php echo $resena['id']; ?>" <?php echo $resena['aprobada'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="aprobada<?php echo $resena['id']; ?>">Aprobada</label>
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


