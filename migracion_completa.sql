-- ============================================
-- MIGRACIÓN COMPLETA
-- Sistema de Gestión de Biblioteca Comunitaria
-- Incluye estructura base, cambios y datos de muestra
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS biblio;
USE biblio;

-- ============================================
-- TABLA: editoriales
-- ============================================
CREATE TABLE IF NOT EXISTS editoriales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    pais VARCHAR(100),
    sitio_web VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: autores
-- ============================================
CREATE TABLE IF NOT EXISTS autores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255),
    nombre_completo VARCHAR(500) GENERATED ALWAYS AS (CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) STORED,
    fecha_nacimiento DATE,
    fecha_fallecimiento DATE,
    nacionalidad VARCHAR(100),
    biografia TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_apellido (apellido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: categorias
-- ============================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    categoria_padre_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_padre_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: idiomas
-- ============================================
CREATE TABLE IF NOT EXISTS idiomas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: libros
-- ============================================
CREATE TABLE IF NOT EXISTS libros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    codigo_interno VARCHAR(50) UNIQUE NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    subtitulo VARCHAR(255),
    editorial_id INT,
    anio_publicacion INT,
    edicion VARCHAR(50),
    lugar_publicacion VARCHAR(100),
    numero_paginas INT,
    idioma_id INT,
    formato ENUM('Tapa dura', 'Tapa blanda', 'Rústica', 'Espiral', 'Otro') DEFAULT 'Tapa blanda',
    dimensiones VARCHAR(50),
    sinopsis TEXT,
    clasificacion_dewey VARCHAR(20),
    estado_fisico ENUM('Excelente', 'Bueno', 'Regular', 'Malo', 'Requiere reparación', 'Perdido') NOT NULL DEFAULT 'Bueno',
    estado_disponibilidad ENUM('Disponible', 'Prestado', 'Reservado', 'En reparación', 'No disponible') NOT NULL DEFAULT 'Disponible',
    ubicacion_fisica VARCHAR(100),
    fecha_adquisicion DATE,
    metodo_adquisicion ENUM('Compra', 'Donación', 'Intercambio', 'Préstamo permanente') DEFAULT 'Donación',
    precio_compra DECIMAL(10,2),
    donante VARCHAR(100),
    numero_prestamos INT DEFAULT 0,
    ultima_fecha_prestamo DATE,
    ultima_fecha_devolucion DATE,
    url_portada VARCHAR(255),
    recomendado_para VARCHAR(50),
    valoracion_promedio DECIMAL(3,2) DEFAULT 0.00,
    numero_resenas INT DEFAULT 0,
    destacado BOOLEAN DEFAULT FALSE,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (editorial_id) REFERENCES editoriales(id) ON DELETE SET NULL,
    FOREIGN KEY (idioma_id) REFERENCES idiomas(id) ON DELETE SET NULL,
    INDEX idx_titulo (titulo),
    INDEX idx_isbn (isbn),
    INDEX idx_codigo_interno (codigo_interno),
    INDEX idx_estado_disponibilidad (estado_disponibilidad),
    INDEX idx_destacado (destacado),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: libro_autor (Relación Muchos a Muchos)
-- ============================================
CREATE TABLE IF NOT EXISTS libro_autor (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    autor_id INT NOT NULL,
    orden INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_libro_autor (libro_id, autor_id),
    INDEX idx_libro (libro_id),
    INDEX idx_autor (autor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: ejemplares (Copias físicas de libros)
-- ============================================
CREATE TABLE IF NOT EXISTS ejemplares (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    codigo_ejemplar VARCHAR(50) UNIQUE NOT NULL,
    estado_fisico ENUM('Excelente', 'Bueno', 'Regular', 'Malo', 'Requiere reparación', 'Perdido') NOT NULL DEFAULT 'Bueno',
    estado_disponibilidad ENUM('Disponible', 'Prestado', 'Reservado', 'En reparación', 'No disponible') NOT NULL DEFAULT 'Disponible',
    ubicacion_fisica VARCHAR(100),
    fecha_adquisicion DATE,
    metodo_adquisicion ENUM('Compra', 'Donación', 'Intercambio', 'Préstamo permanente') DEFAULT 'Donación',
    precio_compra DECIMAL(10,2),
    donante VARCHAR(100),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    INDEX idx_libro (libro_id),
    INDEX idx_codigo_ejemplar (codigo_ejemplar),
    INDEX idx_estado_disponibilidad (estado_disponibilidad),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: libro_categoria (Relación Muchos a Muchos)
-- ============================================
CREATE TABLE IF NOT EXISTS libro_categoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    categoria_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_libro_categoria (libro_id, categoria_id),
    INDEX idx_libro (libro_id),
    INDEX idx_categoria (categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: usuarios (Usuarios de la biblioteca)
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_cedula VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_nacimiento DATE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_nombre (nombre, apellido),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: prestamos
-- ============================================
CREATE TABLE IF NOT EXISTS prestamos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    ejemplar_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_prestamo DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    fecha_devolucion DATE NULL,
    estado ENUM('Activo', 'Devuelto', 'Vencido', 'Perdido') DEFAULT 'Activo',
    multa DECIMAL(10,2) DEFAULT 0.00,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE RESTRICT,
    FOREIGN KEY (ejemplar_id) REFERENCES ejemplares(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_libro (libro_id),
    INDEX idx_ejemplar (ejemplar_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: reservas
-- ============================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado ENUM('Pendiente', 'Completada', 'Cancelada', 'Vencida') DEFAULT 'Pendiente',
    fecha_notificacion DATE NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_libro (libro_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento),
    INDEX idx_ip_fecha (ip_address, fecha_reserva)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: tags (Etiquetas/Palabras clave)
-- ============================================
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: libro_tag (Relación Muchos a Muchos)
-- ============================================
CREATE TABLE IF NOT EXISTS libro_tag (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_libro_tag (libro_id, tag_id),
    INDEX idx_libro (libro_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: resenas (Reseñas de usuarios)
-- ============================================
CREATE TABLE IF NOT EXISTS resenas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    libro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    calificacion INT NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    comentario TEXT,
    fecha_resena DATE NOT NULL,
    aprobada BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_libro_usuario (libro_id, usuario_id),
    INDEX idx_libro (libro_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_calificacion (calificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Insertar idiomas comunes
INSERT INTO idiomas (codigo, nombre) VALUES
('es', 'Español'),
('en', 'Inglés'),
('fr', 'Francés'),
('pt', 'Portugués'),
('it', 'Italiano')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ============================================
-- DATOS DE MUESTRA: EDITORIALES (3)
-- ============================================
INSERT INTO editoriales (nombre, pais, sitio_web) VALUES
('Editorial Planeta', 'España', 'https://www.planeta.es'),
('Alfaguara', 'España', 'https://www.alfaguara.com'),
('Editorial Sudamericana', 'Argentina', 'https://www.sudamericana.com')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ============================================
-- DATOS DE MUESTRA: AUTORES (5)
-- ============================================
INSERT INTO autores (nombre, apellido, fecha_nacimiento, nacionalidad, biografia) VALUES
('Gabriel', 'García Márquez', '1927-03-06', 'Colombiana', 'Escritor, periodista y guionista colombiano. Premio Nobel de Literatura en 1982.'),
('Isabel', 'Allende', '1942-08-02', 'Chilena', 'Escritora chilena, una de las novelistas más leídas del mundo.'),
('Mario', 'Vargas Llosa', '1936-03-28', 'Peruana', 'Escritor peruano, Premio Nobel de Literatura en 2010.'),
('Julio', 'Cortázar', '1914-08-26', 'Argentina', 'Escritor, traductor e intelectual argentino.'),
('Pablo', 'Neruda', '1904-07-12', 'Chilena', 'Poeta chileno, Premio Nobel de Literatura en 1971.')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ============================================
-- DATOS DE MUESTRA: CATEGORÍAS (5)
-- ============================================
INSERT INTO categorias (nombre, descripcion) VALUES
('Literatura', 'Obras literarias de ficción y no ficción'),
('Historia', 'Libros sobre eventos históricos y biografías'),
('Ciencia', 'Libros de ciencias naturales y exactas'),
('Arte', 'Libros sobre arte, música y cultura'),
('Infantil', 'Libros para niños y jóvenes')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ============================================
-- DATOS DE MUESTRA: USUARIOS (5)
-- ============================================
INSERT INTO usuarios (numero_cedula, nombre, apellido, email, telefono, direccion, fecha_nacimiento, estado) VALUES
('1234567890', 'María', 'González', 'maria.gonzalez@email.com', '555-0101', 'Calle Principal 123', '1990-05-15', 'Activo'),
('2345678901', 'Juan', 'Pérez', 'juan.perez@email.com', '555-0102', 'Avenida Central 456', '1985-08-20', 'Activo'),
('3456789012', 'Ana', 'Martínez', 'ana.martinez@email.com', '555-0103', 'Boulevard Norte 789', '1992-11-10', 'Activo'),
('4567890123', 'Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '555-0104', 'Calle Sur 321', '1988-03-25', 'Activo'),
('5678901234', 'Laura', 'Sánchez', 'laura.sanchez@email.com', '555-0105', 'Avenida Este 654', '1995-07-30', 'Activo')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ============================================
-- DATOS DE MUESTRA: LIBROS (20)
-- ============================================
INSERT INTO libros (isbn, codigo_interno, titulo, subtitulo, editorial_id, anio_publicacion, edicion, lugar_publicacion, numero_paginas, idioma_id, formato, sinopsis, clasificacion_dewey, estado_fisico, estado_disponibilidad, ubicacion_fisica, fecha_adquisicion, metodo_adquisicion, destacado) VALUES
('978-84-08-00123-4', 'LIB001', 'Cien años de soledad', 'La historia de los Buendía', 1, 1967, '1ª', 'Buenos Aires', 471, 1, 'Tapa blanda', 'La novela narra la historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo.', '863', 'Bueno', 'Disponible', 'Estantería A-1', '2020-01-15', 'Donación', TRUE),
('978-84-08-00124-5', 'LIB002', 'El amor en los tiempos del cólera', NULL, 1, 1985, '1ª', 'Bogotá', 464, 1, 'Tapa blanda', 'Historia de amor entre Fermina Daza y Florentino Ariza que se desarrolla a lo largo de más de cincuenta años.', '863', 'Excelente', 'Disponible', 'Estantería A-2', '2020-02-10', 'Compra', FALSE),
('978-84-08-00125-6', 'LIB003', 'La casa de los espíritus', NULL, 2, 1982, '1ª', 'Barcelona', 499, 1, 'Tapa dura', 'Primera novela de Isabel Allende que narra la saga de la familia Trueba a lo largo de cuatro generaciones.', '863', 'Bueno', 'Disponible', 'Estantería A-3', '2020-03-05', 'Donación', TRUE),
('978-84-08-00126-7', 'LIB004', 'Eva Luna', NULL, 2, 1987, '1ª', 'Barcelona', 320, 1, 'Tapa blanda', 'Historia de Eva Luna, una joven que cuenta historias para sobrevivir en un mundo lleno de injusticias.', '863', 'Bueno', 'Disponible', 'Estantería A-4', '2020-04-12', 'Donación', FALSE),
('978-84-08-00127-8', 'LIB005', 'La ciudad y los perros', NULL, 3, 1963, '1ª', 'Lima', 408, 1, 'Tapa blanda', 'Primera novela de Vargas Llosa que narra la vida de los cadetes en un colegio militar de Lima.', '863', 'Regular', 'Disponible', 'Estantería A-5', '2020-05-20', 'Compra', TRUE),
('978-84-08-00128-9', 'LIB006', 'Conversación en la Catedral', NULL, 3, 1969, '1ª', 'Barcelona', 704, 1, 'Tapa dura', 'Novela que explora la corrupción política y social del Perú durante la dictadura de Odría.', '863', 'Bueno', 'Disponible', 'Estantería B-1', '2020-06-15', 'Donación', FALSE),
('978-84-08-00129-0', 'LIB007', 'Rayuela', NULL, 3, 1963, '1ª', 'Buenos Aires', 736, 1, 'Tapa blanda', 'Novela experimental que puede leerse de múltiples formas, contando la historia de Horacio Oliveira en París y Buenos Aires.', '863', 'Excelente', 'Disponible', 'Estantería B-2', '2020-07-10', 'Compra', TRUE),
('978-84-08-00130-1', 'LIB008', 'Bestiario', NULL, 3, 1951, '1ª', 'Buenos Aires', 192, 1, 'Tapa blanda', 'Primer libro de cuentos de Cortázar que incluye ocho relatos fantásticos.', '863', 'Bueno', 'Disponible', 'Estantería B-3', '2020-08-05', 'Donación', FALSE),
('978-84-08-00131-2', 'LIB009', 'Veinte poemas de amor y una canción desesperada', NULL, 2, 1924, '1ª', 'Santiago', 96, 1, 'Tapa blanda', 'Uno de los libros de poesía más leídos de la historia, escrito por Pablo Neruda a los 20 años.', '861', 'Excelente', 'Disponible', 'Estantería C-1', '2020-09-12', 'Donación', TRUE),
('978-84-08-00132-3', 'LIB010', 'Canto General', NULL, 2, 1950, '1ª', 'México', 352, 1, 'Tapa dura', 'Poema épico que narra la historia de América Latina desde la perspectiva de Neruda.', '861', 'Bueno', 'Disponible', 'Estantería C-2', '2020-10-20', 'Compra', FALSE),
('978-84-08-00133-4', 'LIB011', 'Crónica de una muerte anunciada', NULL, 1, 1981, '1ª', 'Bogotá', 120, 1, 'Tapa blanda', 'Novela corta que narra el asesinato de Santiago Nasar, un crimen que todos sabían que iba a ocurrir.', '863', 'Bueno', 'Disponible', 'Estantería C-3', '2020-11-15', 'Donación', FALSE),
('978-84-08-00134-5', 'LIB012', 'El otoño del patriarca', NULL, 1, 1975, '1ª', 'Barcelona', 256, 1, 'Tapa blanda', 'Novela que explora el poder absoluto a través de la figura de un dictador latinoamericano.', '863', 'Regular', 'Disponible', 'Estantería C-4', '2020-12-10', 'Compra', FALSE),
('978-84-08-00135-6', 'LIB013', 'De amor y de sombra', NULL, 2, 1984, '1ª', 'Barcelona', 320, 1, 'Tapa blanda', 'Historia de amor en medio de la dictadura chilena, donde dos jóvenes descubren una fosa común.', '863', 'Bueno', 'Disponible', 'Estantería D-1', '2021-01-05', 'Donación', FALSE),
('978-84-08-00136-7', 'LIB014', 'Paula', NULL, 2, 1994, '1ª', 'Barcelona', 330, 1, 'Tapa dura', 'Memoria escrita por Isabel Allende para su hija Paula, quien cayó en coma porfiria.', '863', 'Excelente', 'Disponible', 'Estantería D-2', '2021-02-12', 'Compra', TRUE),
('978-84-08-00137-8', 'LIB015', 'La fiesta del chivo', NULL, 3, 2000, '1ª', 'Madrid', 480, 1, 'Tapa blanda', 'Novela sobre la dictadura de Rafael Trujillo en República Dominicana.', '863', 'Bueno', 'Disponible', 'Estantería D-3', '2021-03-20', 'Donación', FALSE),
('978-84-08-00138-9', 'LIB016', 'El sueño del celta', NULL, 3, 2010, '1ª', 'Madrid', 448, 1, 'Tapa dura', 'Novela histórica sobre Roger Casement y su lucha contra la explotación colonial.', '863', 'Bueno', 'Disponible', 'Estantería D-4', '2021-04-15', 'Compra', FALSE),
('978-84-08-00139-0', 'LIB017', 'Historias de cronopios y de famas', NULL, 3, 1962, '1ª', 'Buenos Aires', 160, 1, 'Tapa blanda', 'Libro de cuentos y relatos cortos de Cortázar con personajes fantásticos.', '863', 'Excelente', 'Disponible', 'Estantería E-1', '2021-05-10', 'Donación', TRUE),
('978-84-08-00140-1', 'LIB018', 'Final del juego', NULL, 3, 1956, '1ª', 'Buenos Aires', 192, 1, 'Tapa blanda', 'Colección de cuentos de Cortázar que incluye "La noche boca arriba" y otros relatos.', '863', 'Bueno', 'Disponible', 'Estantería E-2', '2021-06-05', 'Compra', FALSE),
('978-84-08-00141-2', 'LIB019', 'Residencia en la tierra', NULL, 2, 1933, '1ª', 'Santiago', 128, 1, 'Tapa blanda', 'Libro de poesía de Neruda que marca su etapa surrealista.', '861', 'Regular', 'Disponible', 'Estantería E-3', '2021-07-12', 'Donación', FALSE),
('978-84-08-00142-3', 'LIB020', 'Memorial de Isla Negra', NULL, 2, 1964, '1ª', 'Buenos Aires', 320, 1, 'Tapa dura', 'Autobiografía poética de Neruda dividida en cinco partes que corresponden a las etapas de su vida.', '861', 'Bueno', 'Disponible', 'Estantería E-4', '2021-08-20', 'Compra', FALSE)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

-- ============================================
-- RELACIONES: LIBRO_AUTOR
-- ============================================
INSERT INTO libro_autor (libro_id, autor_id, orden) VALUES
(1, 1, 1),  -- Cien años de soledad - García Márquez
(2, 1, 1),  -- El amor en los tiempos del cólera - García Márquez
(3, 2, 1),  -- La casa de los espíritus - Allende
(4, 2, 1),  -- Eva Luna - Allende
(5, 3, 1),  -- La ciudad y los perros - Vargas Llosa
(6, 3, 1),  -- Conversación en la Catedral - Vargas Llosa
(7, 4, 1),  -- Rayuela - Cortázar
(8, 4, 1),  -- Bestiario - Cortázar
(9, 5, 1),  -- Veinte poemas de amor - Neruda
(10, 5, 1), -- Canto General - Neruda
(11, 1, 1), -- Crónica de una muerte anunciada - García Márquez
(12, 1, 1), -- El otoño del patriarca - García Márquez
(13, 2, 1), -- De amor y de sombra - Allende
(14, 2, 1), -- Paula - Allende
(15, 3, 1), -- La fiesta del chivo - Vargas Llosa
(16, 3, 1), -- El sueño del celta - Vargas Llosa
(17, 4, 1), -- Historias de cronopios y de famas - Cortázar
(18, 4, 1), -- Final del juego - Cortázar
(19, 5, 1), -- Residencia en la tierra - Neruda
(20, 5, 1)  -- Memorial de Isla Negra - Neruda
ON DUPLICATE KEY UPDATE orden = VALUES(orden);

-- ============================================
-- RELACIONES: LIBRO_CATEGORIA
-- ============================================
INSERT INTO libro_categoria (libro_id, categoria_id) VALUES
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1),  -- Literatura
(6, 1), (7, 1), (8, 1), (11, 1), (12, 1), -- Literatura
(13, 1), (15, 1), (16, 1), (17, 1), (18, 1), -- Literatura
(9, 1), (10, 1), (19, 1), (20, 1), -- Literatura (poesía)
(14, 2), -- Historia (biografía)
(15, 2)  -- Historia
ON DUPLICATE KEY UPDATE libro_id = VALUES(libro_id);

-- ============================================
-- DATOS DE MUESTRA: EJEMPLARES
-- Crear 2-3 ejemplares por libro (algunos libros con más copias)
-- ============================================
INSERT INTO ejemplares (libro_id, codigo_ejemplar, estado_fisico, estado_disponibilidad, ubicacion_fisica, fecha_adquisicion, metodo_adquisicion) VALUES
-- Libro 1 (Cien años de soledad) - 3 ejemplares
(1, 'LIB001-E1', 'Excelente', 'Disponible', 'Estantería A-1', '2020-01-15', 'Donación'),
(1, 'LIB001-E2', 'Bueno', 'Disponible', 'Estantería A-1', '2020-01-20', 'Compra'),
(1, 'LIB001-E3', 'Bueno', 'Disponible', 'Estantería A-1', '2020-02-01', 'Donación'),
-- Libro 2 - 2 ejemplares
(2, 'LIB002-E1', 'Excelente', 'Disponible', 'Estantería A-2', '2020-02-10', 'Compra'),
(2, 'LIB002-E2', 'Bueno', 'Disponible', 'Estantería A-2', '2020-02-15', 'Donación'),
-- Libro 3 - 2 ejemplares
(3, 'LIB003-E1', 'Bueno', 'Disponible', 'Estantería A-3', '2020-03-05', 'Donación'),
(3, 'LIB003-E2', 'Regular', 'Disponible', 'Estantería A-3', '2020-03-10', 'Donación'),
-- Libro 4 - 1 ejemplar
(4, 'LIB004-E1', 'Bueno', 'Disponible', 'Estantería A-4', '2020-04-12', 'Donación'),
-- Libro 5 - 2 ejemplares
(5, 'LIB005-E1', 'Regular', 'Disponible', 'Estantería A-5', '2020-05-20', 'Compra'),
(5, 'LIB005-E2', 'Bueno', 'Disponible', 'Estantería A-5', '2020-05-25', 'Donación'),
-- Libro 6 - 1 ejemplar
(6, 'LIB006-E1', 'Bueno', 'Disponible', 'Estantería B-1', '2020-06-15', 'Donación'),
-- Libro 7 - 3 ejemplares
(7, 'LIB007-E1', 'Excelente', 'Disponible', 'Estantería B-2', '2020-07-10', 'Compra'),
(7, 'LIB007-E2', 'Excelente', 'Disponible', 'Estantería B-2', '2020-07-15', 'Compra'),
(7, 'LIB007-E3', 'Bueno', 'Disponible', 'Estantería B-2', '2020-07-20', 'Donación'),
-- Libro 8 - 1 ejemplar
(8, 'LIB008-E1', 'Bueno', 'Disponible', 'Estantería B-3', '2020-08-05', 'Donación'),
-- Libro 9 - 2 ejemplares
(9, 'LIB009-E1', 'Excelente', 'Disponible', 'Estantería C-1', '2020-09-12', 'Donación'),
(9, 'LIB009-E2', 'Bueno', 'Disponible', 'Estantería C-1', '2020-09-15', 'Donación'),
-- Libro 10 - 1 ejemplar
(10, 'LIB010-E1', 'Bueno', 'Disponible', 'Estantería C-2', '2020-10-20', 'Compra'),
-- Libro 11 - 2 ejemplares
(11, 'LIB011-E1', 'Bueno', 'Disponible', 'Estantería C-3', '2020-11-15', 'Donación'),
(11, 'LIB011-E2', 'Bueno', 'Disponible', 'Estantería C-3', '2020-11-20', 'Donación'),
-- Libro 12 - 1 ejemplar
(12, 'LIB012-E1', 'Regular', 'Disponible', 'Estantería C-4', '2020-12-10', 'Compra'),
-- Libro 13 - 1 ejemplar
(13, 'LIB013-E1', 'Bueno', 'Disponible', 'Estantería D-1', '2021-01-05', 'Donación'),
-- Libro 14 - 2 ejemplares
(14, 'LIB014-E1', 'Excelente', 'Disponible', 'Estantería D-2', '2021-02-12', 'Compra'),
(14, 'LIB014-E2', 'Bueno', 'Disponible', 'Estantería D-2', '2021-02-15', 'Donación'),
-- Libro 15 - 1 ejemplar
(15, 'LIB015-E1', 'Bueno', 'Disponible', 'Estantería D-3', '2021-03-20', 'Donación'),
-- Libro 16 - 1 ejemplar
(16, 'LIB016-E1', 'Bueno', 'Disponible', 'Estantería D-4', '2021-04-15', 'Compra'),
-- Libro 17 - 2 ejemplares
(17, 'LIB017-E1', 'Excelente', 'Disponible', 'Estantería E-1', '2021-05-10', 'Donación'),
(17, 'LIB017-E2', 'Bueno', 'Disponible', 'Estantería E-1', '2021-05-12', 'Donación'),
-- Libro 18 - 1 ejemplar
(18, 'LIB018-E1', 'Bueno', 'Disponible', 'Estantería E-2', '2021-06-05', 'Compra'),
-- Libro 19 - 1 ejemplar
(19, 'LIB019-E1', 'Regular', 'Disponible', 'Estantería E-3', '2021-07-12', 'Donación'),
-- Libro 20 - 1 ejemplar
(20, 'LIB020-E1', 'Bueno', 'Disponible', 'Estantería E-4', '2021-08-20', 'Compra')
ON DUPLICATE KEY UPDATE codigo_ejemplar = VALUES(codigo_ejemplar);

-- ============================================
-- TRIGGERS ÚTILES
-- ============================================

-- Eliminar triggers si existen
DROP TRIGGER IF EXISTS actualizar_estado_ejemplar_prestamo;
DROP TRIGGER IF EXISTS actualizar_estado_ejemplar_devolucion;
DROP TRIGGER IF EXISTS actualizar_valoracion_libro;

-- Trigger para actualizar estado del ejemplar cuando se crea un préstamo
DELIMITER //
CREATE TRIGGER actualizar_estado_ejemplar_prestamo
AFTER INSERT ON prestamos
FOR EACH ROW
BEGIN
    UPDATE ejemplares 
    SET estado_disponibilidad = 'Prestado'
    WHERE id = NEW.ejemplar_id;
    
    UPDATE libros 
    SET numero_prestamos = numero_prestamos + 1,
        ultima_fecha_prestamo = NEW.fecha_prestamo
    WHERE id = NEW.libro_id;
END//
DELIMITER ;

-- Trigger para actualizar estado del ejemplar cuando se devuelve
DELIMITER //
CREATE TRIGGER actualizar_estado_ejemplar_devolucion
AFTER UPDATE ON prestamos
FOR EACH ROW
BEGIN
    IF NEW.fecha_devolucion IS NOT NULL AND OLD.fecha_devolucion IS NULL THEN
        UPDATE ejemplares 
        SET estado_disponibilidad = 'Disponible'
        WHERE id = NEW.ejemplar_id;
        
        UPDATE libros 
        SET ultima_fecha_devolucion = NEW.fecha_devolucion
        WHERE id = NEW.libro_id;
    END IF;
END//
DELIMITER ;

-- Trigger para actualizar valoración promedio cuando se agrega una reseña
DELIMITER //
CREATE TRIGGER actualizar_valoracion_libro
AFTER INSERT ON resenas
FOR EACH ROW
BEGIN
    UPDATE libros 
    SET valoracion_promedio = (
        SELECT AVG(calificacion) 
        FROM resenas 
        WHERE libro_id = NEW.libro_id AND aprobada = TRUE
    ),
    numero_resenas = (
        SELECT COUNT(*) 
        FROM resenas 
        WHERE libro_id = NEW.libro_id AND aprobada = TRUE
    )
    WHERE id = NEW.libro_id;
END//
DELIMITER ;

-- ============================================
-- VISTAS ÚTILES
-- ============================================

DROP VIEW IF EXISTS vista_libros_disponibles;
CREATE VIEW vista_libros_disponibles AS
SELECT 
    l.id,
    l.codigo_interno,
    l.titulo,
    l.isbn,
    GROUP_CONCAT(DISTINCT a.nombre_completo SEPARATOR ', ') AS autores,
    e.nombre AS editorial,
    l.anio_publicacion,
    COUNT(DISTINCT CASE WHEN ej.estado_disponibilidad = 'Disponible' THEN ej.id END) AS ejemplares_disponibles,
    COUNT(DISTINCT ej.id) AS total_ejemplares,
    l.valoracion_promedio,
    l.numero_prestamos
FROM libros l
LEFT JOIN libro_autor la ON l.id = la.libro_id
LEFT JOIN autores a ON la.autor_id = a.id
LEFT JOIN editoriales e ON l.editorial_id = e.id
LEFT JOIN ejemplares ej ON l.id = ej.libro_id AND ej.deleted_at IS NULL
WHERE l.deleted_at IS NULL
GROUP BY l.id;

DROP VIEW IF EXISTS vista_prestamos_activos;
CREATE VIEW vista_prestamos_activos AS
SELECT 
    p.id,
    p.fecha_prestamo,
    p.fecha_vencimiento,
    DATEDIFF(CURDATE(), p.fecha_vencimiento) AS dias_vencido,
    l.titulo AS libro,
    l.codigo_interno,
    ej.codigo_ejemplar,
    CONCAT(u.nombre, ' ', u.apellido) AS usuario,
    u.numero_cedula,
    u.telefono,
    p.multa
FROM prestamos p
INNER JOIN libros l ON p.libro_id = l.id
INNER JOIN ejemplares ej ON p.ejemplar_id = ej.id
INNER JOIN usuarios u ON p.usuario_id = u.id
WHERE p.estado = 'Activo'
ORDER BY p.fecha_vencimiento ASC;

-- ============================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- ============================================

DROP PROCEDURE IF EXISTS buscar_libros;
DELIMITER //
CREATE PROCEDURE buscar_libros(
    IN p_termino VARCHAR(255)
)
BEGIN
    SELECT DISTINCT
        l.id,
        l.titulo,
        l.codigo_interno,
        l.isbn,
        GROUP_CONCAT(DISTINCT a.nombre_completo SEPARATOR ', ') AS autores,
        e.nombre AS editorial,
        COUNT(DISTINCT CASE WHEN ej.estado_disponibilidad = 'Disponible' THEN ej.id END) AS ejemplares_disponibles,
        COUNT(DISTINCT ej.id) AS total_ejemplares
    FROM libros l
    LEFT JOIN libro_autor la ON l.id = la.libro_id
    LEFT JOIN autores a ON la.autor_id = a.id
    LEFT JOIN editoriales e ON l.editorial_id = e.id
    LEFT JOIN ejemplares ej ON l.id = ej.libro_id AND ej.deleted_at IS NULL
    WHERE (l.titulo LIKE CONCAT('%', p_termino, '%')
        OR l.codigo_interno LIKE CONCAT('%', p_termino, '%')
        OR l.isbn LIKE CONCAT('%', p_termino, '%')
        OR a.nombre LIKE CONCAT('%', p_termino, '%')
        OR a.apellido LIKE CONCAT('%', p_termino, '%'))
        AND l.deleted_at IS NULL
    GROUP BY l.id
    ORDER BY l.titulo;
END//
DELIMITER ;

-- ============================================
-- FIN DEL SCRIPT DE MIGRACIÓN
-- ============================================

