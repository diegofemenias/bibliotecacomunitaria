<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar p-3">
            <h4 class="text-white mb-4"><i class="bi bi-book"></i> Admin</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'libros.php' ? 'active' : ''; ?>" href="libros.php">
                        <i class="bi bi-book"></i> Libros
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'ejemplares.php' ? 'active' : ''; ?>" href="#" style="display:none;">
                        <i class="bi bi-stack"></i> Ejemplares
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'autores.php' ? 'active' : ''; ?>" href="autores.php">
                        <i class="bi bi-person"></i> Autores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'editoriales.php' ? 'active' : ''; ?>" href="editoriales.php">
                        <i class="bi bi-building"></i> Editoriales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categorias.php' ? 'active' : ''; ?>" href="categorias.php">
                        <i class="bi bi-tags"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'idiomas.php' ? 'active' : ''; ?>" href="idiomas.php">
                        <i class="bi bi-translate"></i> Idiomas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : ''; ?>" href="usuarios.php">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'prestamos.php' ? 'active' : ''; ?>" href="prestamos.php">
                        <i class="bi bi-arrow-left-right"></i> Préstamos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reservas.php' ? 'active' : ''; ?>" href="reservas.php">
                        <i class="bi bi-bookmark"></i> Reservas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'tags.php' ? 'active' : ''; ?>" href="tags.php">
                        <i class="bi bi-hash"></i> Tags
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'resenas.php' ? 'active' : ''; ?>" href="resenas.php">
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

