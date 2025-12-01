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
$idiomaModel = new Model('idiomas');
$autorModel = new Model('autores');
$categoriaModel = new Model('categorias');
$tagModel = new Model('tags');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $data = [
            'isbn' => sanitize($_POST['isbn'] ?? ''),
            'codigo_interno' => sanitize($_POST['codigo_interno']),
            'titulo' => sanitize($_POST['titulo']),
            'subtitulo' => sanitize($_POST['subtitulo'] ?? ''),
            'editorial_id' => !empty($_POST['editorial_id']) ? intval($_POST['editorial_id']) : null,
            'anio_publicacion' => !empty($_POST['anio_publicacion']) ? intval($_POST['anio_publicacion']) : null,
            'edicion' => sanitize($_POST['edicion'] ?? ''),
            'lugar_publicacion' => sanitize($_POST['lugar_publicacion'] ?? ''),
            'numero_paginas' => !empty($_POST['numero_paginas']) ? intval($_POST['numero_paginas']) : null,
            'idioma_id' => !empty($_POST['idioma_id']) ? intval($_POST['idioma_id']) : null,
            'formato' => sanitize($_POST['formato'] ?? 'Tapa blanda'),
            'dimensiones' => sanitize($_POST['dimensiones'] ?? ''),
            'sinopsis' => sanitize($_POST['sinopsis'] ?? ''),
            'clasificacion_dewey' => sanitize($_POST['clasificacion_dewey'] ?? ''),
            'estado_fisico' => sanitize($_POST['estado_fisico'] ?? 'Bueno'),
            'estado_disponibilidad' => sanitize($_POST['estado_disponibilidad'] ?? 'Disponible'),
            'ubicacion_fisica' => sanitize($_POST['ubicacion_fisica'] ?? ''),
            'fecha_adquisicion' => !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null,
            'metodo_adquisicion' => sanitize($_POST['metodo_adquisicion'] ?? 'Donación'),
            'precio_compra' => !empty($_POST['precio_compra']) ? floatval($_POST['precio_compra']) : null,
            'donante' => sanitize($_POST['donante'] ?? ''),
            'url_portada' => sanitize($_POST['url_portada'] ?? ''),
            'recomendado_para' => sanitize($_POST['recomendado_para'] ?? ''),
            'destacado' => isset($_POST['destacado']) ? 1 : 0,
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        
        $autores = isset($_POST['autores']) ? array_map('intval', $_POST['autores']) : [];
        $categorias = isset($_POST['categorias']) ? array_map('intval', $_POST['categorias']) : [];
        $tags = isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];
        
        try {
            $libroModel->createWithRelations($data, $autores, $categorias, $tags);
            $message = 'Libro creado exitosamente';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $data = [
            'isbn' => sanitize($_POST['isbn'] ?? ''),
            'codigo_interno' => sanitize($_POST['codigo_interno']),
            'titulo' => sanitize($_POST['titulo']),
            'subtitulo' => sanitize($_POST['subtitulo'] ?? ''),
            'editorial_id' => !empty($_POST['editorial_id']) ? intval($_POST['editorial_id']) : null,
            'anio_publicacion' => !empty($_POST['anio_publicacion']) ? intval($_POST['anio_publicacion']) : null,
            'edicion' => sanitize($_POST['edicion'] ?? ''),
            'lugar_publicacion' => sanitize($_POST['lugar_publicacion'] ?? ''),
            'numero_paginas' => !empty($_POST['numero_paginas']) ? intval($_POST['numero_paginas']) : null,
            'idioma_id' => !empty($_POST['idioma_id']) ? intval($_POST['idioma_id']) : null,
            'formato' => sanitize($_POST['formato'] ?? 'Tapa blanda'),
            'dimensiones' => sanitize($_POST['dimensiones'] ?? ''),
            'sinopsis' => sanitize($_POST['sinopsis'] ?? ''),
            'clasificacion_dewey' => sanitize($_POST['clasificacion_dewey'] ?? ''),
            'estado_fisico' => sanitize($_POST['estado_fisico'] ?? 'Bueno'),
            'estado_disponibilidad' => sanitize($_POST['estado_disponibilidad'] ?? 'Disponible'),
            'ubicacion_fisica' => sanitize($_POST['ubicacion_fisica'] ?? ''),
            'fecha_adquisicion' => !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null,
            'metodo_adquisicion' => sanitize($_POST['metodo_adquisicion'] ?? 'Donación'),
            'precio_compra' => !empty($_POST['precio_compra']) ? floatval($_POST['precio_compra']) : null,
            'donante' => sanitize($_POST['donante'] ?? ''),
            'url_portada' => sanitize($_POST['url_portada'] ?? ''),
            'recomendado_para' => sanitize($_POST['recomendado_para'] ?? ''),
            'destacado' => isset($_POST['destacado']) ? 1 : 0,
            'notas' => sanitize($_POST['notas'] ?? '')
        ];
        
        $autores = isset($_POST['autores']) ? array_map('intval', $_POST['autores']) : [];
        $categorias = isset($_POST['categorias']) ? array_map('intval', $_POST['categorias']) : [];
        $tags = isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];
        
        try {
            $libroModel->updateWithRelations($id, $data, $autores, $categorias, $tags);
            $message = 'Libro actualizado exitosamente';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        if ($libroModel->delete($id)) {
            $message = 'Libro eliminado exitosamente';
            $messageType = 'success';
        } else {
            $message = 'Error al eliminar libro';
            $messageType = 'danger';
        }
    }
}

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
                    // Buscar préstamo activo del ejemplar
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
$params = [];

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
    ");
}

// Enriquecer con información de préstamos y reservas
$libros = [];
$prestamoModel = new Model('prestamos');
$reservaModel = new Model('reservas');
$usuarioModel = new Model('usuarios');

foreach ($librosRaw as $libro) {
    // Contar ejemplares
    $totalEjemplares = $ejemplarModel->contarTotal($libro['id']);
    $ejemplaresDisponibles = $ejemplarModel->contarDisponibles($libro['id']);
    
    // Obtener todos los ejemplares con su estado
    $ejemplares = $ejemplarModel->getByLibro($libro['id']);
    
    // Obtener préstamos activos de este libro
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
    
    // Determinar estado general del libro basado en ejemplares
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

$editoriales = $editorialModel->getAll();
$idiomas = $idiomaModel->getAll();
$autores = $autorModel->getAll();
$categorias = $categoriaModel->getAll();
$tags = $tagModel->getAll();
$usuarios = (new Model('usuarios'))->getAll();

$editing = null;
if (isset($_GET['edit'])) {
    $editing = $libroModel->getLibroCompleto(intval($_GET['edit']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libros - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'includes/sidebar.php'; ?>
    <style>
        .input-group select[multiple] {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            flex: 1;
        }
        .input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .input-group {
            align-items: flex-start;
        }
        .input-group select[multiple] + .btn {
            align-self: stretch;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
        <h1 class="h2 mb-4">Gestión de Libros</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo $editing ? 'Editar' : 'Nuevo'; ?> Libro</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="libroForm">
                            <?php if ($editing): ?>
                                <input type="hidden" name="id" value="<?php echo $editing['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Código Interno *</label>
                                        <input type="text" class="form-control" name="codigo_interno" value="<?php echo $editing ? htmlspecialchars($editing['codigo_interno']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">ISBN</label>
                                        <input type="text" class="form-control" name="isbn" value="<?php echo $editing ? htmlspecialchars($editing['isbn'] ?? '') : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" name="titulo" value="<?php echo $editing ? htmlspecialchars($editing['titulo']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Subtítulo</label>
                                <input type="text" class="form-control" name="subtitulo" value="<?php echo $editing ? htmlspecialchars($editing['subtitulo'] ?? '') : ''; ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Editorial</label>
                                        <div class="input-group">
                                            <select class="form-select" name="editorial_id" id="editorial_id">
                                                <option value="">Seleccionar editorial</option>
                                                <?php foreach ($editoriales as $editorial): ?>
                                                    <option value="<?php echo $editorial['id']; ?>" <?php echo $editing && $editing['editorial_id'] == $editorial['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($editorial['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditorial">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Idioma</label>
                                        <select class="form-select" name="idioma_id">
                                            <option value="">Seleccionar idioma</option>
                                            <?php foreach ($idiomas as $idioma): ?>
                                                <option value="<?php echo $idioma['id']; ?>" <?php echo $editing && $editing['idioma_id'] == $idioma['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($idioma['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Autores</label>
                                <div class="input-group">
                                    <select class="form-select" name="autores[]" id="autores" multiple size="5">
                                        <?php foreach ($autores as $autor): ?>
                                            <option value="<?php echo $autor['id']; ?>" <?php echo $editing && in_array($autor['id'], array_column($editing['autores'] ?? [], 'id')) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($autor['nombre_completo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAutor">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Mantenga presionado Ctrl (Cmd en Mac) para seleccionar múltiples</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Categorías</label>
                                <div class="input-group">
                                    <select class="form-select" name="categorias[]" id="categorias" multiple size="5">
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?php echo $categoria['id']; ?>" <?php echo $editing && in_array($categoria['id'], array_column($editing['categorias'] ?? [], 'id')) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Mantenga presionado Ctrl (Cmd en Mac) para seleccionar múltiples</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tags</label>
                                <div class="input-group">
                                    <select class="form-select" name="tags[]" id="tags" multiple size="4">
                                        <?php foreach ($tags as $tag): ?>
                                            <option value="<?php echo $tag['id']; ?>" <?php echo $editing && in_array($tag['id'], array_column($editing['tags'] ?? [], 'id')) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tag['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTag">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Mantenga presionado Ctrl (Cmd en Mac) para seleccionar múltiples</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Año de Publicación</label>
                                        <input type="number" class="form-control" name="anio_publicacion" value="<?php echo $editing ? $editing['anio_publicacion'] : ''; ?>" min="1000" max="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Número de Páginas</label>
                                        <input type="number" class="form-control" name="numero_paginas" value="<?php echo $editing ? $editing['numero_paginas'] : ''; ?>" min="1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Formato</label>
                                        <select class="form-select" name="formato">
                                            <option value="Tapa blanda" <?php echo $editing && $editing['formato'] == 'Tapa blanda' ? 'selected' : ''; ?>>Tapa blanda</option>
                                            <option value="Tapa dura" <?php echo $editing && $editing['formato'] == 'Tapa dura' ? 'selected' : ''; ?>>Tapa dura</option>
                                            <option value="Rústica" <?php echo $editing && $editing['formato'] == 'Rústica' ? 'selected' : ''; ?>>Rústica</option>
                                            <option value="Espiral" <?php echo $editing && $editing['formato'] == 'Espiral' ? 'selected' : ''; ?>>Espiral</option>
                                            <option value="Otro" <?php echo $editing && $editing['formato'] == 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
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
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
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
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ubicación Física</label>
                                        <input type="text" class="form-control" name="ubicacion_fisica" value="<?php echo $editing ? htmlspecialchars($editing['ubicacion_fisica'] ?? '') : ''; ?>" placeholder="Ej: Estante A-3, Fila 2">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sinopsis</label>
                                <textarea class="form-control" name="sinopsis" rows="4"><?php echo $editing ? htmlspecialchars($editing['sinopsis'] ?? '') : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Clasificación Dewey</label>
                                        <input type="text" class="form-control" name="clasificacion_dewey" value="<?php echo $editing ? htmlspecialchars($editing['clasificacion_dewey'] ?? '') : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Edición</label>
                                        <input type="text" class="form-control" name="edicion" value="<?php echo $editing ? htmlspecialchars($editing['edicion'] ?? '') : ''; ?>" placeholder="Ej: 1ra edición">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Lugar de Publicación</label>
                                        <input type="text" class="form-control" name="lugar_publicacion" value="<?php echo $editing ? htmlspecialchars($editing['lugar_publicacion'] ?? '') : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de Adquisición</label>
                                        <input type="date" class="form-control" name="fecha_adquisicion" value="<?php echo $editing && $editing['fecha_adquisicion'] ? $editing['fecha_adquisicion'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Método de Adquisición</label>
                                        <select class="form-select" name="metodo_adquisicion">
                                            <option value="Donación" <?php echo $editing && ($editing['metodo_adquisicion'] == 'Donación' || !$editing) ? 'selected' : ''; ?>>Donación</option>
                                            <option value="Compra" <?php echo $editing && $editing['metodo_adquisicion'] == 'Compra' ? 'selected' : ''; ?>>Compra</option>
                                            <option value="Intercambio" <?php echo $editing && $editing['metodo_adquisicion'] == 'Intercambio' ? 'selected' : ''; ?>>Intercambio</option>
                                            <option value="Préstamo permanente" <?php echo $editing && $editing['metodo_adquisicion'] == 'Préstamo permanente' ? 'selected' : ''; ?>>Préstamo permanente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Precio de Compra</label>
                                        <input type="number" step="0.01" class="form-control" name="precio_compra" value="<?php echo $editing ? $editing['precio_compra'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Donante</label>
                                        <input type="text" class="form-control" name="donante" value="<?php echo $editing ? htmlspecialchars($editing['donante'] ?? '') : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Recomendado para</label>
                                        <input type="text" class="form-control" name="recomendado_para" value="<?php echo $editing ? htmlspecialchars($editing['recomendado_para'] ?? '') : ''; ?>" placeholder="Ej: Adultos, Jóvenes">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL de Portada</label>
                                <input type="url" class="form-control" name="url_portada" value="<?php echo $editing ? htmlspecialchars($editing['url_portada'] ?? '') : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dimensiones</label>
                                <input type="text" class="form-control" name="dimensiones" value="<?php echo $editing ? htmlspecialchars($editing['dimensiones'] ?? '') : ''; ?>" placeholder="Ej: 23 x 15 cm">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notas</label>
                                <textarea class="form-control" name="notas" rows="3"><?php echo $editing ? htmlspecialchars($editing['notas'] ?? '') : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="destacado" id="destacado" <?php echo $editing && $editing['destacado'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="destacado">Destacado</label>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="<?php echo $editing ? 'update' : 'create'; ?>" class="btn btn-primary">
                                    <?php echo $editing ? 'Actualizar' : 'Crear'; ?> Libro
                                </button>
                                <?php if ($editing): ?>
                                    <a href="ejemplares.php?libro_id=<?php echo $editing['id']; ?>" class="btn btn-info">
                                        <i class="bi bi-stack"></i> Gestionar Ejemplares
                                    </a>
                                    <a href="libros.php" class="btn btn-secondary">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
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
                                <a href="libros.php" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x"></i> Limpiar
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                                            <small class="text-muted">Ej: <?php echo htmlspecialchars($prestamo['codigo_ejemplar']); ?></small><br>
                                                            <small class="text-muted">Vence: <?php echo $prestamo['fecha_vencimiento']; ?></small>
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
                                                    <a href="ejemplares.php?libro_id=<?php echo $libro['id']; ?>" class="btn btn-sm btn-info mb-1">
                                                        <i class="bi bi-stack"></i> Ejemplares (<?php echo $libro['total_ejemplares']; ?>)
                                                    </a>
                                                    
                                                    <?php if ($libro['ejemplares_disponibles'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-primary mb-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalPrestar<?php echo $libro['id']; ?>">
                                                            <i class="bi bi-arrow-right-circle"></i> Prestar (<?php echo $libro['ejemplares_disponibles']; ?> disp.)
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
                                                    
                                                    <a href="?edit=<?php echo $libro['id']; ?>" class="btn btn-sm btn-secondary">
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
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal Editorial -->
    <div class="modal fade" id="modalEditorial" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Editorial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditorial">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" name="pais">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sitio Web</label>
                            <input type="url" class="form-control" name="sitio_web">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Autor -->
    <div class="modal fade" id="modalAutor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Autor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAutor">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" name="apellido">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control" name="nacionalidad">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Fallecimiento</label>
                            <input type="date" class="form-control" name="fecha_fallecimiento">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Biografía</label>
                            <textarea class="form-control" name="biografia" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Categoría -->
    <div class="modal fade" id="modalCategoria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCategoria">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría Padre</label>
                            <select class="form-select" name="categoria_padre_id">
                                <option value="">Ninguna (Categoría principal)</option>
                                <?php 
                                $categoriasPadre = array_filter($categorias, function($cat) {
                                    return $cat['categoria_padre_id'] === null;
                                });
                                foreach ($categoriasPadre as $cat): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tag -->
    <div class="modal fade" id="modalTag" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTag">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre" required placeholder="Ej: novela, historia, ciencia">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Función para agregar opción a un select
    function agregarOpcion(selectId, value, text, isMultiple = false) {
        const select = document.getElementById(selectId);
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        option.selected = true;
        select.appendChild(option);
        
        // Si es múltiple, mantener seleccionado
        if (isMultiple) {
            // Scroll al final del select
            select.scrollTop = select.scrollHeight;
        }
    }

    // Manejar formulario de Editorial
    document.getElementById('formEditorial').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('tipo', 'editorial');
        
        fetch('ajax_create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                agregarOpcion('editorial_id', data.id, data.nombre);
                bootstrap.Modal.getInstance(document.getElementById('modalEditorial')).hide();
                this.reset();
                // Mostrar mensaje de éxito
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = 'Editorial creada exitosamente <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al crear la editorial');
        });
    });

    // Manejar formulario de Autor
    document.getElementById('formAutor').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('tipo', 'autor');
        
        fetch('ajax_create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                agregarOpcion('autores', data.id, data.nombre, true);
                bootstrap.Modal.getInstance(document.getElementById('modalAutor')).hide();
                this.reset();
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = 'Autor creado exitosamente <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al crear el autor');
        });
    });

    // Manejar formulario de Categoría
    document.getElementById('formCategoria').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('tipo', 'categoria');
        
        fetch('ajax_create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                agregarOpcion('categorias', data.id, data.nombre, true);
                bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
                this.reset();
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = 'Categoría creada exitosamente <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al crear la categoría');
        });
    });

    // Manejar formulario de Tag
    document.getElementById('formTag').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('tipo', 'tag');
        
        fetch('ajax_create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                agregarOpcion('tags', data.id, data.nombre, true);
                bootstrap.Modal.getInstance(document.getElementById('modalTag')).hide();
                this.reset();
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = 'Tag creado exitosamente <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al crear el tag');
        });
    });
    </script>
</body>
</html>

