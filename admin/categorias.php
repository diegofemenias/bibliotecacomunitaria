<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/Model.php';

$model = new Model('categorias');
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'descripcion' => sanitize($_POST['descripcion'] ?? ''),
            'categoria_padre_id' => !empty($_POST['categoria_padre_id']) ? intval($_POST['categoria_padre_id']) : null
        ];
        if ($model->create($data)) {
            $message = 'Categoría creada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear categoría';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'descripcion' => sanitize($_POST['descripcion'] ?? ''),
            'categoria_padre_id' => !empty($_POST['categoria_padre_id']) ? intval($_POST['categoria_padre_id']) : null
        ];
        if ($model->update($id, $data)) {
            $message = 'Categoría actualizada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar categoría';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Categoría eliminada exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar categoría';
            $messageType = 'danger';
        }
    }
}

$categorias = $model->getAll();
$categoriasPadre = array_filter($categorias, function($cat) {
    return $cat['categoria_padre_id'] === null;
});
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
    <title>Categorías - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Categorías</h1>
        
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
                        <h5><?php echo $editing ? 'Editar' : 'Nueva'; ?> Categoría</h5>
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
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3"><?php echo $editing ? htmlspecialchars($editing['descripcion'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Categoría Padre</label>
                                <select class="form-select" name="categoria_padre_id">
                                    <option value="">Ninguna (Categoría principal)</option>
                                    <?php foreach ($categoriasPadre as $cat): ?>
                                        <?php if (!$editing || $editing['id'] != $cat['id']): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $editing && $editing['categoria_padre_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['nombre']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Categorías</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Categoría Padre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <?php
                                        $padreNombre = '-';
                                        if ($categoria['categoria_padre_id']) {
                                            $padre = $model->getById($categoria['categoria_padre_id']);
                                            $padreNombre = $padre ? $padre['nombre'] : '-';
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $categoria['id']; ?></td>
                                            <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($categoria['descripcion'] ?? '', 0, 50)) . (strlen($categoria['descripcion'] ?? '') > 50 ? '...' : ''); ?></td>
                                            <td><?php echo htmlspecialchars($padreNombre); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                                    <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
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


