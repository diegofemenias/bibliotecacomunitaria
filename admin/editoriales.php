<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

$model = new Model('editoriales');
$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'pais' => sanitize($_POST['pais'] ?? ''),
            'sitio_web' => sanitize($_POST['sitio_web'] ?? '')
        ];
        if ($model->create($data)) {
            $message = 'Editorial creada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear editorial';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'pais' => sanitize($_POST['pais'] ?? ''),
            'sitio_web' => sanitize($_POST['sitio_web'] ?? '')
        ];
        if ($model->update($id, $data)) {
            $message = 'Editorial actualizada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar editorial';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Editorial eliminada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar editorial';
            $messageType = 'danger';
        }
    }
}

$editoriales = $model->getAll();
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
    <title>Editoriales - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Editoriales</h1>
        
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
                        <h5><?php echo $editing ? 'Editar' : 'Nueva'; ?> Editorial</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editing): ?>
                                <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="<?php echo $editing ? htmlspecialchars($editing['nombre']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">País</label>
                                <input type="text" class="form-control" name="pais" value="<?php echo $editing ? htmlspecialchars($editing['pais'] ?? '') : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sitio Web</label>
                                <input type="url" class="form-control" name="sitio_web" value="<?php echo $editing ? htmlspecialchars($editing['sitio_web'] ?? '') : ''; ?>">
                            </div>
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="editoriales.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Editoriales</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>País</th>
                                        <th>Sitio Web</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($editoriales as $editorial): ?>
                                        <tr>
                                            <td><?php echo $editorial['id']; ?></td>
                                            <td><?php echo htmlspecialchars($editorial['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($editorial['pais'] ?? '-'); ?></td>
                                            <td><?php echo $editorial['sitio_web'] ? '<a href="' . htmlspecialchars($editorial['sitio_web']) . '" target="_blank">Ver</a>' : '-'; ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $editorial['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta editorial?');">
                                                    <input type="hidden" name="id" value="<?php echo $editorial['id']; ?>">
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


