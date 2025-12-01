<?php
require_once '../config/config.php';
requireAdmin();

require_once '../config/database.php';
require_once '../models/LibroModel.php';
require_once '../models/EjemplarModel.php';
require_once '../models/Model.php';

$libroModel = new LibroModel();
$ejemplarModel = new EjemplarModel();
$editorialModel = new Model('editoriales');
$stats = [
    'total_libros' => count($libroModel->getAll()),
    'libros_disponibles' => count($libroModel->query("SELECT * FROM libros WHERE estado_disponibilidad = 'Disponible' AND deleted_at IS NULL")),
    'libros_prestados' => count($libroModel->query("SELECT * FROM libros WHERE estado_disponibilidad = 'Prestado' AND deleted_at IS NULL")),
    'total_usuarios' => count($libroModel->query("SELECT * FROM usuarios WHERE deleted_at IS NULL")),
    'prestamos_activos' => count($libroModel->query("SELECT * FROM prestamos WHERE estado = 'Activo'")),
    'reservas_pendientes' => count($libroModel->query("SELECT * FROM reservas WHERE estado = 'Pendiente'"))
];

$message = '';
$messageType = '';

// Manejar acciones rápidas
if (isset($_POST['accion_rapida'])) {
    $libroId = intval($_POST['libro_id']);
    $accion = sanitize($_POST['accion_rapida']);
    
    $prestamoModel = new Model('prestamos');
    $reservaModel = new Model('reservas');
    
    try {
        switch ($accion) {
            case 'prestar':
                $usuarioId = intval($_POST['usuario_id'] ?? 0);
                $ejemplarId = intval($_POST['ejemplar_id'] ?? 0);
                $diasPrestamo = intval($_POST['dias_prestamo'] ?? 14);
                
                if ($usuarioId > 0 && $ejemplarId > 0) {
                    $data = [
                        'libro_id' => $libroId,
                        'ejemplar_id' => $ejemplarId,
                        'usuario_id' => $usuarioId,
                        'fecha_prestamo' => date('Y-m-d'),
                        'fecha_vencimiento' => date('Y-m-d', strtotime("+{$diasPrestamo} days")),
                        'estado' => 'Activo'
                    ];
                    $prestamoModel->create($data);
                    $ejemplarModel->update($ejemplarId, ['estado_disponibilidad' => 'Prestado']);
                    $message = 'Ejemplar prestado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'devolver':
                $ejemplarId = intval($_POST['ejemplar_id'] ?? 0);
                
                if ($ejemplarId > 0) {
                    $prestamo = $prestamoModel->queryOne("
                        SELECT * FROM prestamos 
                        WHERE ejemplar_id = ? AND estado = 'Activo' 
                        ORDER BY fecha_prestamo DESC LIMIT 1
                    ", [$ejemplarId]);
                    
                    if ($prestamo) {
                        $prestamoModel->update($prestamo['id'], [
                            'fecha_devolucion' => date('Y-m-d'),
                            'estado' => 'Devuelto'
                        ]);
                        $ejemplarModel->update($ejemplarId, ['estado_disponibilidad' => 'Disponible']);
                        $message = 'Ejemplar devuelto exitosamente';
                        $messageType = 'success';
                    }
                }
                break;
                
            case 'reservar':
                $usuarioId = intval($_POST['usuario_id'] ?? 0);
                
                if ($usuarioId > 0) {
                    $fechaVencimiento = date('Y-m-d', strtotime('+' . DIAS_VALIDEZ_RESERVA . ' days'));
                    $data = [
                        'libro_id' => $libroId,
                        'usuario_id' => $usuarioId,
                        'fecha_reserva' => date('Y-m-d'),
                        'fecha_vencimiento' => $fechaVencimiento,
                        'estado' => 'Pendiente'
                    ];
                    $reservaModel->create($data);
                    $libroModel->update($libroId, ['estado_disponibilidad' => 'Reservado']);
                    $message = 'Libro reservado exitosamente';
                    $messageType = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Búsqueda
$terminoBusqueda = sanitize($_GET['buscar'] ?? '');

if (!empty($terminoBusqueda)) {
    $librosRaw = $libroModel->query("
        SELECT DISTINCT l.*
        FROM libros l
        LEFT JOIN libro_autor la ON l.id = la.libro_id
        LEFT JOIN autores a ON la.autor_id = a.id
        WHERE l.deleted_at IS NULL
        AND (l.titulo LIKE ? 
            OR l.codigo_interno LIKE ? 
            OR a.nombre LIKE ? 
            OR a.apellido LIKE ?
            OR a.nombre_completo LIKE ?)
        ORDER BY l.titulo ASC
    ", [
        "%{$terminoBusqueda}%", 
        "%{$terminoBusqueda}%", 
        "%{$terminoBusqueda}%", 
        "%{$terminoBusqueda}%",
        "%{$terminoBusqueda}%"
    ]);
} else {
    $librosRaw = $libroModel->query("
        SELECT l.*
        FROM libros l
        WHERE l.deleted_at IS NULL
        ORDER BY l.titulo ASC
        LIMIT 20
    ");
}

// Enriquecer con información de préstamos y reservas
$libros = [];
$prestamoModel = new Model('prestamos');
$reservaModel = new Model('reservas');

foreach ($librosRaw as $libro) {
    // Contar ejemplares
    $totalEjemplares = $ejemplarModel->contarTotal($libro['id']);
    $ejemplaresDisponibles = $ejemplarModel->contarDisponibles($libro['id']);
    
    // Obtener todos los ejemplares con su estado
    $ejemplares = $ejemplarModel->getByLibro($libro['id']);
    
    // Obtener préstamos activos
    // Primero intentar con ejemplares, si no hay, buscar préstamos sin ejemplar_id (para compatibilidad)
    $prestamos = $prestamoModel->query("
        SELECT p.*, u.nombre, u.apellido, u.numero_cedula, 
               COALESCE(e.codigo_ejemplar, CONCAT('LIB-', p.id)) AS codigo_ejemplar, 
               COALESCE(e.id, p.ejemplar_id, p.id) AS ejemplar_id
        FROM prestamos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN ejemplares e ON p.ejemplar_id = e.id AND e.deleted_at IS NULL
        WHERE p.libro_id = ? AND p.estado = 'Activo'
        ORDER BY p.fecha_prestamo DESC
    ", [$libro['id']]);
    
    // Si no hay préstamos con ejemplares, verificar si hay préstamos sin ejemplar_id (datos antiguos)
    if (empty($prestamos)) {
        $prestamos = $prestamoModel->query("
            SELECT p.*, u.nombre, u.apellido, u.numero_cedula, 
                   CONCAT('LIB-', p.id) AS codigo_ejemplar, 
                   p.id AS ejemplar_id
            FROM prestamos p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.libro_id = ? AND p.estado = 'Activo' AND (p.ejemplar_id IS NULL OR p.ejemplar_id = 0)
            ORDER BY p.fecha_prestamo DESC
        ", [$libro['id']]);
    }
    
    // Obtener ejemplares reservados
    $ejemplaresReservados = array_filter($ejemplares, function($e) {
        return $e['estado_disponibilidad'] == 'Reservado';
    });
    
    $reserva = $reservaModel->queryOne("
        SELECT r.*, u.nombre, u.apellido
        FROM reservas r
        INNER JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.libro_id = ? AND r.estado = 'Pendiente'
        ORDER BY r.fecha_reserva DESC
        LIMIT 1
    ", [$libro['id']]);
    
    $libro['total_ejemplares'] = $totalEjemplares;
    $libro['ejemplares_disponibles'] = $ejemplaresDisponibles;
    $libro['ejemplares'] = $ejemplares;
    $libro['prestamos'] = $prestamos;
    $libro['ejemplares_reservados'] = $ejemplaresReservados;
    $libro['prestamo_id'] = !empty($prestamos) ? $prestamos[0]['id'] : null;
    $libro['prestamo_vencimiento'] = !empty($prestamos) ? $prestamos[0]['fecha_vencimiento'] : null;
    $libro['usuario_nombre'] = !empty($prestamos) ? $prestamos[0]['nombre'] : null;
    $libro['usuario_apellido'] = !empty($prestamos) ? $prestamos[0]['apellido'] : null;
    $libro['usuario_cedula'] = !empty($prestamos) ? $prestamos[0]['numero_cedula'] : null;
    $libro['reserva_id'] = $reserva['id'] ?? null;
    $libro['reserva_usuario_nombre'] = $reserva['nombre'] ?? null;
    $libro['reserva_usuario_apellido'] = $reserva['apellido'] ?? null;
    
    // Determinar estado general
    if ($ejemplaresDisponibles > 0) {
        $libro['estado_disponibilidad'] = 'Disponible';
    } elseif ($totalEjemplares == 0) {
        $libro['estado_disponibilidad'] = 'No disponible';
    } elseif (count($prestamos) > 0) {
        $libro['estado_disponibilidad'] = 'Prestado';
    } else {
        $libro['estado_disponibilidad'] = 'No disponible';
    }
    
    $libros[] = $libro;
}

$usuarios = (new Model('usuarios'))->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-3">
                <h4 class="text-white mb-4"><i class="bi bi-book"></i> Admin</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros.php">
                            <i class="bi bi-book"></i> Libros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="autores.php">
                            <i class="bi bi-person"></i> Autores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="editoriales.php">
                            <i class="bi bi-building"></i> Editoriales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categorias.php">
                            <i class="bi bi-tags"></i> Categorías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="idiomas.php">
                            <i class="bi bi-translate"></i> Idiomas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prestamos.php">
                            <i class="bi bi-arrow-left-right"></i> Préstamos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservas.php">
                            <i class="bi bi-bookmark"></i> Reservas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tags.php">
                            <i class="bi bi-hash"></i> Tags
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="resenas.php">
                            <i class="bi bi-star"></i> Reseñas
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house"></i> Ver Sitio Público
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Salir
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <h1 class="h2 mb-4">Dashboard</h1>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><i class="bi bi-book"></i> Total Libros</h5>
                                <h2 class="mb-0"><?php echo $stats['total_libros']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success"><i class="bi bi-check-circle"></i> Disponibles</h5>
                                <h2 class="mb-0"><?php echo $stats['libros_disponibles']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><i class="bi bi-arrow-right-circle"></i> Prestados</h5>
                                <h2 class="mb-0"><?php echo $stats['libros_prestados']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info"><i class="bi bi-people"></i> Usuarios</h5>
                                <h2 class="mb-0"><?php echo $stats['total_usuarios']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-danger">
                            <div class="card-body">
                                <h5 class="card-title text-danger"><i class="bi bi-arrow-left-right"></i> Préstamos Activos</h5>
                                <h2 class="mb-0"><?php echo $stats['prestamos_activos']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-secondary">
                            <div class="card-body">
                                <h5 class="card-title text-secondary"><i class="bi bi-bookmark"></i> Reservas Pendientes</h5>
                                <h2 class="mb-0"><?php echo $stats['reservas_pendientes']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Libros -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5>Lista de Libros</h5>
                                <form method="GET" class="d-flex gap-2">
                                    <input type="text" class="form-control form-control-sm" name="buscar" 
                                           placeholder="Buscar por código, título o autor..." 
                                           value="<?php echo htmlspecialchars($terminoBusqueda); ?>" 
                                           style="width: 300px;">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                    <?php if (!empty($terminoBusqueda)): ?>
                                        <a href="index.php" class="btn btn-sm btn-secondary">
                                            <i class="bi bi-x"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Título</th>
                                                <th>Autor</th>
                                                <th>Estado</th>
                                                <th>Prestado a</th>
                                                <th>Reservado por</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($libros as $libro): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($libro['codigo_interno']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                                                    <td>
                                                        <?php
                                                        $autores = $prestamoModel->query("
                                                            SELECT a.nombre_completo
                                                            FROM autores a
                                                            INNER JOIN libro_autor la ON a.id = la.autor_id
                                                            WHERE la.libro_id = ?
                                                            ORDER BY la.orden
                                                            LIMIT 3
                                                        ", [$libro['id']]);
                                                        if (!empty($autores)) {
                                                            $nombresAutores = array_column($autores, 'nombre_completo');
                                                            echo htmlspecialchars(implode(', ', $nombresAutores));
                                                            if (count($autores) >= 3) {
                                                                echo '...';
                                                            }
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $libro['estado_disponibilidad'] == 'Disponible' ? 'success' : 
                                                                ($libro['estado_disponibilidad'] == 'Prestado' ? 'danger' : 
                                                                ($libro['estado_disponibilidad'] == 'Reservado' ? 'warning' : 'secondary')); 
                                                        ?>">
                                                            <?php echo htmlspecialchars($libro['estado_disponibilidad']); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo $libro['ejemplares_disponibles']; ?>/<?php echo $libro['total_ejemplares']; ?> disponibles
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($libro['prestamos'])): ?>
                                                            <?php foreach ($libro['prestamos'] as $prestamo): ?>
                                                                <div class="mb-2">
                                                                    <strong><?php echo htmlspecialchars($prestamo['nombre'] . ' ' . $prestamo['apellido']); ?></strong><br>
                                                                    <small class="text-muted">Ej: <?php echo htmlspecialchars($prestamo['codigo_ejemplar']); ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($libro['reserva_id']): ?>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($libro['reserva_usuario_nombre'] . ' ' . $libro['reserva_usuario_apellido']); ?></strong>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                                            <?php if ($libro['ejemplares_disponibles'] > 0): ?>
                                                                <button type="button" class="btn btn-sm btn-primary mb-1" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modalPrestar<?php echo $libro['id']; ?>">
                                                                    <i class="bi bi-arrow-right-circle"></i> Prestar (<?php echo $libro['ejemplares_disponibles']; ?>)
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-warning mb-1" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modalReservar<?php echo $libro['id']; ?>">
                                                                    <i class="bi bi-bookmark"></i> Reservar
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($libro['prestamos'])): ?>
                                                                <button type="button" class="btn btn-sm btn-success mb-1" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modalDevolver<?php echo $libro['id']; ?>">
                                                                    <i class="bi bi-arrow-left-circle"></i> Devolver (<?php echo count($libro['prestamos']); ?>)
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($libro['ejemplares_reservados'])): ?>
                                                                <button type="button" class="btn btn-sm btn-primary mb-1" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modalPrestarReserva<?php echo $libro['id']; ?>">
                                                                    <i class="bi bi-arrow-right-circle"></i> Prestar Reservado
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <a href="libros.php?edit=<?php echo $libro['id']; ?>" class="btn btn-sm btn-secondary">
                                                                <i class="bi bi-pencil"></i> Editar
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Modales para cada libro -->
                                <?php foreach ($libros as $libro): ?>
                                                <!-- Modal Prestar -->
                                                <div class="modal fade" id="modalPrestar<?php echo $libro['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Prestar: <?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="libro_id" value="<?php echo $libro['id']; ?>">
                                                                    <input type="hidden" name="accion_rapida" value="prestar">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Ejemplar *</label>
                                                                        <select class="form-select" name="ejemplar_id" required>
                                                                            <option value="">Seleccionar ejemplar</option>
                                                                            <?php 
                                                                            $ejemplaresDisponibles = $ejemplarModel->getDisponiblesByLibro($libro['id']);
                                                                            foreach ($ejemplaresDisponibles as $ejemplar): 
                                                                            ?>
                                                                                <option value="<?php echo $ejemplar['id']; ?>">
                                                                                    <?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] . ' - ' . $ejemplar['estado_fisico']); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                        <?php if (empty($ejemplaresDisponibles)): ?>
                                                                            <small class="form-text text-danger">No hay ejemplares disponibles. <a href="ejemplares.php?libro_id=<?php echo $libro['id']; ?>">Crear ejemplar</a></small>
                                                                        <?php endif; ?>
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
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary" <?php echo empty($ejemplaresDisponibles) ? 'disabled' : ''; ?>>Confirmar Préstamo</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Modal Reservar -->
                                                <div class="modal fade" id="modalReservar<?php echo $libro['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reservar: <?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="libro_id" value="<?php echo $libro['id']; ?>">
                                                                    <input type="hidden" name="accion_rapida" value="reservar">
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
                                                                    <div class="alert alert-info">
                                                                        La reserva será válida por <?php echo DIAS_VALIDEZ_RESERVA; ?> días.
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-warning">Confirmar Reserva</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Modal Devolver -->
                                                <div class="modal fade" id="modalDevolver<?php echo $libro['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Devolver Ejemplares: <?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="libro_id" value="<?php echo $libro['id']; ?>">
                                                                    <input type="hidden" name="accion_rapida" value="devolver">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Ejemplar(es) a Devolver</label>
                                                                        <?php if (!empty($libro['prestamos']) && is_array($libro['prestamos'])): ?>
                                                                            <div class="table-responsive">
                                                                                <table class="table table-sm">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Ejemplar</th>
                                                                                            <th>Prestado a</th>
                                                                                            <th>Vencimiento</th>
                                                                                            <th>Acción</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php foreach ($libro['prestamos'] as $prestamo): ?>
                                                                                            <tr>
                                                                                                <td><strong><?php echo htmlspecialchars($prestamo['codigo_ejemplar'] ?? 'N/A'); ?></strong></td>
                                                                                                <td>
                                                                                                    <?php echo htmlspecialchars(($prestamo['nombre'] ?? '') . ' ' . ($prestamo['apellido'] ?? '')); ?><br>
                                                                                                    <small class="text-muted"><?php echo htmlspecialchars($prestamo['numero_cedula'] ?? ''); ?></small>
                                                                                                </td>
                                                                                                <td><?php echo $prestamo['fecha_vencimiento'] ?? 'N/A'; ?></td>
                                                                                                <td>
                                                                                                    <button type="submit" name="ejemplar_id" value="<?php echo $prestamo['ejemplar_id'] ?? $prestamo['id']; ?>" 
                                                                                                            class="btn btn-sm btn-success" 
                                                                                                            onclick="return confirm('¿Confirmar devolución del ejemplar <?php echo htmlspecialchars($prestamo['codigo_ejemplar'] ?? 'este'); ?>?');">
                                                                                                        <i class="bi bi-arrow-left-circle"></i> Devolver
                                                                                                    </button>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php endforeach; ?>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="alert alert-warning">
                                                                                <i class="bi bi-exclamation-triangle"></i> No hay ejemplares prestados para este libro.
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Modal Prestar (para reservas) -->
                                                <div class="modal fade" id="modalPrestarReserva<?php echo $libro['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Prestar Reservado: <?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="libro_id" value="<?php echo $libro['id']; ?>">
                                                                    <input type="hidden" name="accion_rapida" value="prestar">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Ejemplar Reservado *</label>
                                                                        <select class="form-select" name="ejemplar_id" required>
                                                                            <option value="">Seleccionar ejemplar</option>
                                                                            <?php foreach ($libro['ejemplares_reservados'] as $ejemplar): ?>
                                                                                <option value="<?php echo $ejemplar['id']; ?>">
                                                                                    <?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] . ' - ' . $ejemplar['estado_fisico']); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <?php if ($libro['reserva_id']): ?>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Reservado por</label>
                                                                            <input type="text" class="form-control" 
                                                                                   value="<?php echo htmlspecialchars($libro['reserva_usuario_nombre'] . ' ' . $libro['reserva_usuario_apellido']); ?>" 
                                                                                   readonly>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Días de Préstamo</label>
                                                                        <input type="number" class="form-control" name="dias_prestamo" value="14" min="1" max="90">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary">Confirmar Préstamo</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($terminoBusqueda) && count($libros) >= 20): ?>
                                    <div class="text-center mt-3">
                                        <a href="libros.php" class="btn btn-sm btn-outline-primary">Ver todos los libros</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

