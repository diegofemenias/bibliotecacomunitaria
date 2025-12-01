<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

$model = new Model('autores');
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'apellido' => sanitize($_POST['apellido'] ?? ''),
            'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
            'fecha_fallecimiento' => !empty($_POST['fecha_fallecimiento']) ? $_POST['fecha_fallecimiento'] : null,
            'nacionalidad' => sanitize($_POST['nacionalidad'] ?? ''),
            'biografia' => sanitize($_POST['biografia'] ?? '')
        ];
        if ($model->create($data)) {
            $message = 'Autor creado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear autor';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'apellido' => sanitize($_POST['apellido'] ?? ''),
            'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
            'fecha_fallecimiento' => !empty($_POST['fecha_fallecimiento']) ? $_POST['fecha_fallecimiento'] : null,
            'nacionalidad' => sanitize($_POST['nacionalidad'] ?? ''),
            'biografia' => sanitize($_POST['biografia'] ?? '')
        ];
        if ($model->update($id, $data)) {
            $message = 'Autor actualizado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar autor';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Autor eliminado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar autor';
            $messageType = 'danger';
        }
    }
}

$autores = $model->getAll();
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
    <title>Autores - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Autores</h1>
        
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
                        <h5><?php echo $editing ? 'Editar' : 'Nuevo'; ?> Autor</h5>
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
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" value="<?php echo $editing ? htmlspecialchars($editing['apellido'] ?? '') : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nacimiento" value="<?php echo $editing && $editing['fecha_nacimiento'] ? $editing['fecha_nacimiento'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Fallecimiento</label>
                                <input type="date" class="form-control" name="fecha_fallecimiento" value="<?php echo $editing && $editing['fecha_fallecimiento'] ? $editing['fecha_fallecimiento'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nacionalidad</label>
                                <input type="text" class="form-control" name="nacionalidad" value="<?php echo $editing ? htmlspecialchars($editing['nacionalidad'] ?? '') : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Biografía</label>
                                <textarea class="form-control" name="biografia" rows="4"><?php echo $editing ? htmlspecialchars($editing['biografia'] ?? '') : ''; ?></textarea>
                            </div>
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="autores.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Autores</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Completo</th>
                                        <th>Nacionalidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($autores as $autor): ?>
                                        <tr>
                                            <td><?php echo $autor['id']; ?></td>
                                            <td><?php echo htmlspecialchars($autor['nombre_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($autor['nacionalidad'] ?? '-'); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $autor['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este autor?');">
                                                    <input type="hidden" name="id" value="<?php echo $autor['id']; ?>">
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


