<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

$model = new Model('tags');
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'nombre' => sanitize($_POST['nombre'])
        ];
        if ($model->create($data)) {
            $message = 'Tag creado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear tag';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'nombre' => sanitize($_POST['nombre'])
        ];
        if ($model->update($id, $data)) {
            $message = 'Tag actualizado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar tag';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Tag eliminado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar tag';
            $messageType = 'danger';
        }
    }
}

$tags = $model->getAll();
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
    <title>Tags - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Tags</h1>
        
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
                        <h5><?php echo $editing ? 'Editar' : 'Nuevo'; ?> Tag</h5>
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
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="tags.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Tags</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tags as $tag): ?>
                                        <tr>
                                            <td><?php echo $tag['id']; ?></td>
                                            <td><span class="badge bg-secondary">#<?php echo htmlspecialchars($tag['nombre']); ?></span></td>
                                            <td>
                                                <a href="?edit=<?php echo $tag['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este tag?');">
                                                    <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
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


