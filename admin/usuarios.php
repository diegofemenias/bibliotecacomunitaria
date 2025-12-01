<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/UsuarioModel.php';

$model = new UsuarioModel();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'numero_cedula' => sanitize($_POST['numero_cedula']),
            'nombre' => sanitize($_POST['nombre']),
            'apellido' => sanitize($_POST['apellido']),
            'email' => sanitize($_POST['email'] ?? ''),
            'telefono' => sanitize($_POST['telefono'] ?? ''),
            'direccion' => sanitize($_POST['direccion'] ?? ''),
            'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
            'estado' => sanitize($_POST['estado'] ?? 'Activo'),
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        if ($model->create($data)) {
            $message = 'Usuario creado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al crear usuario';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'numero_cedula' => sanitize($_POST['numero_cedula']),
            'nombre' => sanitize($_POST['nombre']),
            'apellido' => sanitize($_POST['apellido']),
            'email' => sanitize($_POST['email'] ?? ''),
            'telefono' => sanitize($_POST['telefono'] ?? ''),
            'direccion' => sanitize($_POST['direccion'] ?? ''),
            'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
            'estado' => sanitize($_POST['estado'] ?? 'Activo'),
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        if ($model->update($id, $data)) {
            $message = 'Usuario actualizado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al actualizar usuario';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($model->delete($id)) {
            $message = 'Usuario eliminado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar usuario';
            $messageType = 'danger';
        }
    }
}

$usuarios = $model->getAll();
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
    <title>Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Usuarios</h1>
        
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
                        <h5><?php echo $editing ? 'Editar' : 'Nuevo'; ?> Usuario</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editing): ?>
                                <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Número de Cédula *</label>
                                <input type="text" class="form-control" name="numero_cedula" value="<?php echo $editing ? htmlspecialchars($editing['numero_cedula']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="<?php echo $editing ? htmlspecialchars($editing['nombre']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Apellido *</label>
                                <input type="text" class="form-control" name="apellido" value="<?php echo $editing ? htmlspecialchars($editing['apellido']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $editing ? htmlspecialchars($editing['email'] ?? '') : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" value="<?php echo $editing ? htmlspecialchars($editing['telefono'] ?? '') : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control" name="direccion" rows="2"><?php echo $editing ? htmlspecialchars($editing['direccion'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nacimiento" value="<?php echo $editing && $editing['fecha_nacimiento'] ? $editing['fecha_nacimiento'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="Activo" <?php echo $editing && ($editing['estado'] == 'Activo' || !$editing) ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo $editing && $editing['estado'] == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    <option value="Suspendido" <?php echo $editing && $editing['estado'] == 'Suspendido' ? 'selected' : ''; ?>>Suspendido</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notas</label>
                                <textarea class="form-control" name="notas" rows="3"><?php echo $editing ? htmlspecialchars($editing['notas'] ?? '') : ''; ?></textarea>
                            </div>
                            <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                <?php echo $editing ? 'Actualizar' : 'Crear'; ?>
                            </button>
                            <?php if ($editing): ?>
                                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Lista de Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cédula</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo $usuario['id']; ?></td>
                                            <td><?php echo htmlspecialchars($usuario['numero_cedula']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['telefono'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $usuario['estado'] == 'Activo' ? 'success' : 
                                                        ($usuario['estado'] == 'Suspendido' ? 'danger' : 'secondary'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($usuario['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
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


