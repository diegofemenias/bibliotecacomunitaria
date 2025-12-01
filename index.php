<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/LibroModel.php';

$libroModel = new LibroModel();
$resultados = [];
$termino = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $termino = sanitize($_POST['termino'] ?? '');
    if (!empty($termino)) {
        $resultados = $libroModel->buscar($termino);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .book-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-disponible {
            background-color: #28a745;
        }
        .badge-reservado {
            background-color: #ffc107;
        }
        .badge-prestado {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h1 class="display-4 mb-4"><i class="bi bi-book"></i> <?php echo APP_NAME; ?></h1>
                    <p class="lead">Busca y reserva libros de nuestra biblioteca comunitaria</p>
                    <form method="POST" class="mt-4">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="termino" placeholder="Buscar por título, autor, ISBN o código..." value="<?php echo htmlspecialchars($termino); ?>" required>
                            <button class="btn btn-light" type="submit" name="buscar">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($resultados)): ?>
            <h3 class="mb-4">Resultados de búsqueda (<?php echo count($resultados); ?>)</h3>
            <div class="row">
                <?php foreach ($resultados as $libro): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card book-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                <?php if ($libro['autores']): ?>
                                    <p class="text-muted mb-2"><i class="bi bi-person"></i> <?php echo htmlspecialchars($libro['autores']); ?></p>
                                <?php endif; ?>
                                <?php if ($libro['editorial']): ?>
                                    <p class="text-muted small mb-2"><i class="bi bi-building"></i> <?php echo htmlspecialchars($libro['editorial']); ?></p>
                                <?php endif; ?>
                                <?php if ($libro['isbn']): ?>
                                    <p class="text-muted small mb-2"><strong>ISBN:</strong> <?php echo htmlspecialchars($libro['isbn']); ?></p>
                                <?php endif; ?>
                                <p class="text-muted small mb-2"><strong>Código:</strong> <?php echo htmlspecialchars($libro['codigo_interno']); ?></p>
                                
                                <?php
                                $ejemplaresDisponibles = intval($libro['ejemplares_disponibles'] ?? 0);
                                $totalEjemplares = intval($libro['total_ejemplares'] ?? 0);
                                $estadoClass = 'badge-disponible';
                                $estadoText = 'Disponible';
                                if ($ejemplaresDisponibles == 0 && $totalEjemplares > 0) {
                                    $estadoClass = 'badge-prestado';
                                    $estadoText = 'No disponible';
                                } elseif ($ejemplaresDisponibles == 0) {
                                    $estadoClass = 'badge-prestado';
                                    $estadoText = 'Sin ejemplares';
                                }
                                ?>
                                <span class="badge <?php echo $estadoClass; ?> mb-2"><?php echo $estadoText; ?></span>
                                <br>
                                <small class="text-muted">
                                    <?php echo $ejemplaresDisponibles; ?> de <?php echo $totalEjemplares; ?> ejemplares disponibles
                                </small>
                                
                                <?php if ($libro['sinopsis']): ?>
                                    <p class="card-text small mt-2"><?php echo htmlspecialchars(substr($libro['sinopsis'], 0, 100)) . '...'; ?></p>
                                <?php endif; ?>
                                
                                <?php if ($ejemplaresDisponibles > 0): ?>
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#reservaModal<?php echo $libro['id']; ?>">
                                        <i class="bi bi-bookmark-plus"></i> Reservar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Reserva -->
                    <div class="modal fade" id="reservaModal<?php echo $libro['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reservar: <?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="reservar.php">
                                    <div class="modal-body">
                                        <input type="hidden" name="libro_id" value="<?php echo $libro['id']; ?>">
                                        <div class="mb-3">
                                            <label for="cedula<?php echo $libro['id']; ?>" class="form-label">Número de Cédula</label>
                                            <input type="text" class="form-control" id="cedula<?php echo $libro['id']; ?>" name="cedula" required placeholder="Ingrese su número de cédula">
                                            <small class="form-text text-muted">Ingrese su número de cédula para realizar la reserva</small>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> La reserva será válida por <?php echo DIAS_VALIDEZ_RESERVA; ?> días. Debe retirar el libro en persona en la biblioteca.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Confirmar Reserva</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No se encontraron resultados para "<?php echo htmlspecialchars($termino); ?>"
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3 text-muted">Busca un libro en nuestro catálogo</h3>
                <p class="text-muted">Ingresa el título, autor, ISBN o código del libro que deseas encontrar</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
            <a href="login.php" class="text-decoration-none text-muted">Acceso Administrador</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

