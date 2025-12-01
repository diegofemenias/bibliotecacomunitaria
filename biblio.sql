-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Dec 01, 2025 at 02:10 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `biblio`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `buscar_libros` (IN `p_termino` VARCHAR(255))   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `autores`
--

CREATE TABLE `autores` (
  `id` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_completo` varchar(500) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (concat(coalesce(`nombre`,_utf8mb4''),_utf8mb4' ',coalesce(`apellido`,_utf8mb4''))) STORED,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_fallecimiento` date DEFAULT NULL,
  `nacionalidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biografia` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `autores`
--

INSERT INTO `autores` (`id`, `nombre`, `apellido`, `fecha_nacimiento`, `fecha_fallecimiento`, `nacionalidad`, `biografia`, `created_at`, `updated_at`) VALUES
(1, 'Gabriel', 'García Márquez', '1927-03-06', NULL, 'Colombiana', 'Escritor, periodista y guionista colombiano. Premio Nobel de Literatura en 1982.', '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(2, 'Isabel', 'Allende', '1942-08-02', NULL, 'Chilena', 'Escritora chilena, una de las novelistas más leídas del mundo.', '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(3, 'Mario', 'Vargas Llosa', '1936-03-28', NULL, 'Peruana', 'Escritor peruano, Premio Nobel de Literatura en 2010.', '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(4, 'Julio', 'Cortázar', '1914-08-26', NULL, 'Argentina', 'Escritor, traductor e intelectual argentino.', '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(5, 'Pablo', 'Neruda', '1904-07-12', NULL, 'Chilena', 'Poeta chileno, Premio Nobel de Literatura en 1971.', '2025-11-28 15:21:43', '2025-11-28 15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `categoria_padre_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `categoria_padre_id`, `created_at`, `updated_at`) VALUES
(1, 'Literatura', 'Obras literarias de ficción y no ficción', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(2, 'Historia', 'Libros sobre eventos históricos y biografías', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(3, 'Ciencia', 'Libros de ciencias naturales y exactas', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(4, 'Arte', 'Libros sobre arte, música y cultura', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(5, 'Infantil', 'Libros para niños y jóvenes', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `editoriales`
--

CREATE TABLE `editoriales` (
  `id` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `editoriales`
--

INSERT INTO `editoriales` (`id`, `nombre`, `pais`, `sitio_web`, `created_at`, `updated_at`) VALUES
(1, 'Editorial Planeta', 'España', 'https://www.planeta.es', '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(2, 'Alfaguara', 'España', 'https://www.alfaguara.com', '2025-11-28 15:21:43', '2025-11-28 15:21:43'),
(3, 'Editorial Sudamericana', 'Argentina', 'https://www.sudamericana.com', '2025-11-28 15:21:43', '2025-11-28 15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `ejemplares`
--

CREATE TABLE `ejemplares` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `codigo_ejemplar` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_fisico` enum('Excelente','Bueno','Regular','Malo','Requiere reparación','Perdido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bueno',
  `estado_disponibilidad` enum('Disponible','Prestado','Reservado','En reparación','No disponible') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Disponible',
  `ubicacion_fisica` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `metodo_adquisicion` enum('Compra','Donación','Intercambio','Préstamo permanente') COLLATE utf8mb4_unicode_ci DEFAULT 'Donación',
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `donante` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ejemplares`
--

INSERT INTO `ejemplares` (`id`, `libro_id`, `codigo_ejemplar`, `estado_fisico`, `estado_disponibilidad`, `ubicacion_fisica`, `fecha_adquisicion`, `metodo_adquisicion`, `precio_compra`, `donante`, `notas`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'LIB001-E1', 'Excelente', 'Disponible', 'Estantería A-1', '2020-01-15', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-12-01 13:57:32', NULL),
(2, 1, 'LIB001-E2', 'Excelente', 'Disponible', 'Estantería A-1', '2020-01-20', 'Compra', NULL, NULL, '', '2025-11-28 15:21:43', '2025-12-01 14:06:31', NULL),
(3, 1, 'LIB001-E3', 'Bueno', 'Disponible', 'Estantería A-1', '2020-02-01', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(4, 2, 'LIB002-E1', 'Excelente', 'Disponible', 'Estantería A-2', '2020-02-10', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(5, 2, 'LIB002-E2', 'Bueno', 'Disponible', 'Estantería A-2', '2020-02-15', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(6, 3, 'LIB003-E1', 'Bueno', 'Disponible', 'Estantería A-3', '2020-03-05', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(7, 3, 'LIB003-E2', 'Regular', 'Disponible', 'Estantería A-3', '2020-03-10', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(8, 4, 'LIB004-E1', 'Bueno', 'Disponible', 'Estantería A-4', '2020-04-12', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(9, 5, 'LIB005-E1', 'Regular', 'Disponible', 'Estantería A-5', '2020-05-20', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(10, 5, 'LIB005-E2', 'Bueno', 'Disponible', 'Estantería A-5', '2020-05-25', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(11, 6, 'LIB006-E1', 'Bueno', 'Disponible', 'Estantería B-1', '2020-06-15', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(12, 7, 'LIB007-E1', 'Excelente', 'Disponible', 'Estantería B-2', '2020-07-10', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(13, 7, 'LIB007-E2', 'Excelente', 'Disponible', 'Estantería B-2', '2020-07-15', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(14, 7, 'LIB007-E3', 'Bueno', 'Disponible', 'Estantería B-2', '2020-07-20', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(15, 8, 'LIB008-E1', 'Bueno', 'Disponible', 'Estantería B-3', '2020-08-05', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-12-01 14:08:51', NULL),
(16, 9, 'LIB009-E1', 'Excelente', 'Disponible', 'Estantería C-1', '2020-09-12', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(17, 9, 'LIB009-E2', 'Bueno', 'Disponible', 'Estantería C-1', '2020-09-15', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(18, 10, 'LIB010-E1', 'Bueno', 'Disponible', 'Estantería C-2', '2020-10-20', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(19, 11, 'LIB011-E1', 'Bueno', 'Disponible', 'Estantería C-3', '2020-11-15', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-12-01 13:58:51', NULL),
(20, 11, 'LIB011-E2', 'Bueno', 'Disponible', 'Estantería C-3', '2020-11-20', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(21, 12, 'LIB012-E1', 'Regular', 'Disponible', 'Estantería C-4', '2020-12-10', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(22, 13, 'LIB013-E1', 'Bueno', 'Disponible', 'Estantería D-1', '2021-01-05', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(23, 14, 'LIB014-E1', 'Excelente', 'Disponible', 'Estantería D-2', '2021-02-12', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(24, 14, 'LIB014-E2', 'Bueno', 'Disponible', 'Estantería D-2', '2021-02-15', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(25, 15, 'LIB015-E1', 'Bueno', 'Disponible', 'Estantería D-3', '2021-03-20', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(26, 16, 'LIB016-E1', 'Bueno', 'Disponible', 'Estantería D-4', '2021-04-15', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(27, 17, 'LIB017-E1', 'Excelente', 'Disponible', 'Estantería E-1', '2021-05-10', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(28, 17, 'LIB017-E2', 'Bueno', 'Disponible', 'Estantería E-1', '2021-05-12', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(29, 18, 'LIB018-E1', 'Bueno', 'Disponible', 'Estantería E-2', '2021-06-05', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(30, 19, 'LIB019-E1', 'Regular', 'Disponible', 'Estantería E-3', '2021-07-12', 'Donación', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(31, 20, 'LIB020-E1', 'Bueno', 'Disponible', 'Estantería E-4', '2021-08-20', 'Compra', NULL, NULL, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(32, 8, 'LIB008-E2', 'Excelente', 'Prestado', 'Estantería B-3', '2025-12-01', 'Donación', NULL, NULL, NULL, '2025-12-01 14:08:12', '2025-12-01 14:08:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `idiomas`
--

CREATE TABLE `idiomas` (
  `id` int NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `idiomas`
--

INSERT INTO `idiomas` (`id`, `codigo`, `nombre`, `created_at`) VALUES
(1, 'es', 'Español', '2025-11-28 15:21:43'),
(2, 'en', 'Inglés', '2025-11-28 15:21:43'),
(3, 'fr', 'Francés', '2025-11-28 15:21:43'),
(4, 'pt', 'Portugués', '2025-11-28 15:21:43'),
(5, 'it', 'Italiano', '2025-11-28 15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `libros`
--

CREATE TABLE `libros` (
  `id` int NOT NULL,
  `isbn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_interno` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editorial_id` int DEFAULT NULL,
  `anio_publicacion` int DEFAULT NULL,
  `edicion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lugar_publicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_paginas` int DEFAULT NULL,
  `idioma_id` int DEFAULT NULL,
  `formato` enum('Tapa dura','Tapa blanda','Rústica','Espiral','Otro') COLLATE utf8mb4_unicode_ci DEFAULT 'Tapa blanda',
  `dimensiones` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sinopsis` text COLLATE utf8mb4_unicode_ci,
  `clasificacion_dewey` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_fisico` enum('Excelente','Bueno','Regular','Malo','Requiere reparación','Perdido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bueno',
  `estado_disponibilidad` enum('Disponible','Prestado','Reservado','En reparación','No disponible') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Disponible',
  `ubicacion_fisica` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `metodo_adquisicion` enum('Compra','Donación','Intercambio','Préstamo permanente') COLLATE utf8mb4_unicode_ci DEFAULT 'Donación',
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `donante` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_prestamos` int DEFAULT '0',
  `ultima_fecha_prestamo` date DEFAULT NULL,
  `ultima_fecha_devolucion` date DEFAULT NULL,
  `url_portada` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recomendado_para` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valoracion_promedio` decimal(3,2) DEFAULT '0.00',
  `numero_resenas` int DEFAULT '0',
  `destacado` tinyint(1) DEFAULT '0',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `libros`
--

INSERT INTO `libros` (`id`, `isbn`, `codigo_interno`, `titulo`, `subtitulo`, `editorial_id`, `anio_publicacion`, `edicion`, `lugar_publicacion`, `numero_paginas`, `idioma_id`, `formato`, `dimensiones`, `sinopsis`, `clasificacion_dewey`, `estado_fisico`, `estado_disponibilidad`, `ubicacion_fisica`, `fecha_adquisicion`, `metodo_adquisicion`, `precio_compra`, `donante`, `numero_prestamos`, `ultima_fecha_prestamo`, `ultima_fecha_devolucion`, `url_portada`, `recomendado_para`, `valoracion_promedio`, `numero_resenas`, `destacado`, `notas`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '978-84-08-00123-4', 'LIB001', 'Cien años de soledad', 'La historia de los Buendía', 1, 1967, '1ª', 'Buenos Aires', 471, 1, 'Tapa blanda', NULL, 'La novela narra la historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo.', '863', 'Bueno', 'Disponible', 'Estantería A-1', '2020-01-15', 'Donación', NULL, NULL, 1, '2025-12-01', NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-12-01 13:53:35', NULL),
(2, '978-84-08-00124-5', 'LIB002', 'El amor en los tiempos del cólera', NULL, 1, 1985, '1ª', 'Bogotá', 464, 1, 'Tapa blanda', NULL, 'Historia de amor entre Fermina Daza y Florentino Ariza que se desarrolla a lo largo de más de cincuenta años.', '863', 'Excelente', 'Disponible', 'Estantería A-2', '2020-02-10', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(3, '978-84-08-00125-6', 'LIB003', 'La casa de los espíritus', NULL, 2, 1982, '1ª', 'Barcelona', 499, 1, 'Tapa dura', NULL, 'Primera novela de Isabel Allende que narra la saga de la familia Trueba a lo largo de cuatro generaciones.', '863', 'Bueno', 'Disponible', 'Estantería A-3', '2020-03-05', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(4, '978-84-08-00126-7', 'LIB004', 'Eva Luna', NULL, 2, 1987, '1ª', 'Barcelona', 320, 1, 'Tapa blanda', NULL, 'Historia de Eva Luna, una joven que cuenta historias para sobrevivir en un mundo lleno de injusticias.', '863', 'Bueno', 'Disponible', 'Estantería A-4', '2020-04-12', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(5, '978-84-08-00127-8', 'LIB005', 'La ciudad y los perros', NULL, 3, 1963, '1ª', 'Lima', 408, 1, 'Tapa blanda', NULL, 'Primera novela de Vargas Llosa que narra la vida de los cadetes en un colegio militar de Lima.', '863', 'Regular', 'Disponible', 'Estantería A-5', '2020-05-20', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(6, '978-84-08-00128-9', 'LIB006', 'Conversación en la Catedral', NULL, 3, 1969, '1ª', 'Barcelona', 704, 1, 'Tapa dura', NULL, 'Novela que explora la corrupción política y social del Perú durante la dictadura de Odría.', '863', 'Bueno', 'Disponible', 'Estantería B-1', '2020-06-15', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(7, '978-84-08-00129-0', 'LIB007', 'Rayuela', NULL, 3, 1963, '1ª', 'Buenos Aires', 736, 1, 'Tapa blanda', NULL, 'Novela experimental que puede leerse de múltiples formas, contando la historia de Horacio Oliveira en París y Buenos Aires.', '863', 'Excelente', 'Disponible', 'Estantería B-2', '2020-07-10', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(8, '978-84-08-00130-1', 'LIB008', 'Bestiario', NULL, 3, 1951, '1ª', 'Buenos Aires', 192, 1, 'Tapa blanda', NULL, 'Primer libro de cuentos de Cortázar que incluye ocho relatos fantásticos.', '863', 'Bueno', 'Reservado', 'Estantería B-3', '2020-08-05', 'Donación', NULL, NULL, 3, '2025-12-01', '2025-12-01', NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-12-01 14:08:51', NULL),
(9, '978-84-08-00131-2', 'LIB009', 'Veinte poemas de amor y una canción desesperada', NULL, 2, 1924, '1ª', 'Santiago', 96, 1, 'Tapa blanda', NULL, 'Uno de los libros de poesía más leídos de la historia, escrito por Pablo Neruda a los 20 años.', '861', 'Excelente', 'Disponible', 'Estantería C-1', '2020-09-12', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(10, '978-84-08-00132-3', 'LIB010', 'Canto General', NULL, 2, 1950, '1ª', 'México', 352, 1, 'Tapa dura', NULL, 'Poema épico que narra la historia de América Latina desde la perspectiva de Neruda.', '861', 'Bueno', 'Disponible', 'Estantería C-2', '2020-10-20', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(11, '978-84-08-00133-4', 'LIB011', 'Crónica de una muerte anunciada', NULL, 1, 1981, '1ª', 'Bogotá', 120, 1, 'Tapa blanda', NULL, 'Novela corta que narra el asesinato de Santiago Nasar, un crimen que todos sabían que iba a ocurrir.', '863', 'Bueno', 'Disponible', 'Estantería C-3', '2020-11-15', 'Donación', NULL, NULL, 1, '2025-12-01', NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-12-01 13:58:07', NULL),
(12, '978-84-08-00134-5', 'LIB012', 'El otoño del patriarca', NULL, 1, 1975, '1ª', 'Barcelona', 256, 1, 'Tapa blanda', NULL, 'Novela que explora el poder absoluto a través de la figura de un dictador latinoamericano.', '863', 'Regular', 'Disponible', 'Estantería C-4', '2020-12-10', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(13, '978-84-08-00135-6', 'LIB013', 'De amor y de sombra', NULL, 2, 1984, '1ª', 'Barcelona', 320, 1, 'Tapa blanda', NULL, 'Historia de amor en medio de la dictadura chilena, donde dos jóvenes descubren una fosa común.', '863', 'Bueno', 'Disponible', 'Estantería D-1', '2021-01-05', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(14, '978-84-08-00136-7', 'LIB014', 'Paula', NULL, 2, 1994, '1ª', 'Barcelona', 330, 1, 'Tapa dura', NULL, 'Memoria escrita por Isabel Allende para su hija Paula, quien cayó en coma porfiria.', '863', 'Excelente', 'Disponible', 'Estantería D-2', '2021-02-12', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(15, '978-84-08-00137-8', 'LIB015', 'La fiesta del chivo', NULL, 3, 2000, '1ª', 'Madrid', 480, 1, 'Tapa blanda', NULL, 'Novela sobre la dictadura de Rafael Trujillo en República Dominicana.', '863', 'Bueno', 'Disponible', 'Estantería D-3', '2021-03-20', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(16, '978-84-08-00138-9', 'LIB016', 'El sueño del celta', NULL, 3, 2010, '1ª', 'Madrid', 448, 1, 'Tapa dura', NULL, 'Novela histórica sobre Roger Casement y su lucha contra la explotación colonial.', '863', 'Bueno', 'Disponible', 'Estantería D-4', '2021-04-15', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(17, '978-84-08-00139-0', 'LIB017', 'Historias de cronopios y de famas', NULL, 3, 1962, '1ª', 'Buenos Aires', 160, 1, 'Tapa blanda', NULL, 'Libro de cuentos y relatos cortos de Cortázar con personajes fantásticos.', '863', 'Excelente', 'Disponible', 'Estantería E-1', '2021-05-10', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 1, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(18, '978-84-08-00140-1', 'LIB018', 'Final del juego', NULL, 3, 1956, '1ª', 'Buenos Aires', 192, 1, 'Tapa blanda', NULL, 'Colección de cuentos de Cortázar que incluye \"La noche boca arriba\" y otros relatos.', '863', 'Bueno', 'Disponible', 'Estantería E-2', '2021-06-05', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(19, '978-84-08-00141-2', 'LIB019', 'Residencia en la tierra', NULL, 2, 1933, '1ª', 'Santiago', 128, 1, 'Tapa blanda', NULL, 'Libro de poesía de Neruda que marca su etapa surrealista.', '861', 'Regular', 'Disponible', 'Estantería E-3', '2021-07-12', 'Donación', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(20, '978-84-08-00142-3', 'LIB020', 'Memorial de Isla Negra', NULL, 2, 1964, '1ª', 'Buenos Aires', 320, 1, 'Tapa dura', NULL, 'Autobiografía poética de Neruda dividida en cinco partes que corresponden a las etapas de su vida.', '861', 'Bueno', 'Disponible', 'Estantería E-4', '2021-08-20', 'Compra', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0.00, 0, 0, NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `libro_autor`
--

CREATE TABLE `libro_autor` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `autor_id` int NOT NULL,
  `orden` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `libro_autor`
--

INSERT INTO `libro_autor` (`id`, `libro_id`, `autor_id`, `orden`, `created_at`) VALUES
(1, 1, 1, 1, '2025-11-28 15:21:43'),
(2, 2, 1, 1, '2025-11-28 15:21:43'),
(3, 3, 2, 1, '2025-11-28 15:21:43'),
(4, 4, 2, 1, '2025-11-28 15:21:43'),
(5, 5, 3, 1, '2025-11-28 15:21:43'),
(6, 6, 3, 1, '2025-11-28 15:21:43'),
(7, 7, 4, 1, '2025-11-28 15:21:43'),
(8, 8, 4, 1, '2025-11-28 15:21:43'),
(9, 9, 5, 1, '2025-11-28 15:21:43'),
(10, 10, 5, 1, '2025-11-28 15:21:43'),
(11, 11, 1, 1, '2025-11-28 15:21:43'),
(12, 12, 1, 1, '2025-11-28 15:21:43'),
(13, 13, 2, 1, '2025-11-28 15:21:43'),
(14, 14, 2, 1, '2025-11-28 15:21:43'),
(15, 15, 3, 1, '2025-11-28 15:21:43'),
(16, 16, 3, 1, '2025-11-28 15:21:43'),
(17, 17, 4, 1, '2025-11-28 15:21:43'),
(18, 18, 4, 1, '2025-11-28 15:21:43'),
(19, 19, 5, 1, '2025-11-28 15:21:43'),
(20, 20, 5, 1, '2025-11-28 15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `libro_categoria`
--

CREATE TABLE `libro_categoria` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `categoria_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `libro_categoria`
--

INSERT INTO `libro_categoria` (`id`, `libro_id`, `categoria_id`, `created_at`) VALUES
(1, 1, 1, '2025-11-28 15:21:43'),
(2, 2, 1, '2025-11-28 15:21:43'),
(3, 3, 1, '2025-11-28 15:21:43'),
(4, 4, 1, '2025-11-28 15:21:43'),
(5, 5, 1, '2025-11-28 15:21:43'),
(6, 6, 1, '2025-11-28 15:21:43'),
(7, 7, 1, '2025-11-28 15:21:43'),
(8, 8, 1, '2025-11-28 15:21:43'),
(9, 11, 1, '2025-11-28 15:21:43'),
(10, 12, 1, '2025-11-28 15:21:43'),
(11, 13, 1, '2025-11-28 15:21:43'),
(12, 15, 1, '2025-11-28 15:21:43'),
(13, 16, 1, '2025-11-28 15:21:43'),
(14, 17, 1, '2025-11-28 15:21:43'),
(15, 18, 1, '2025-11-28 15:21:43'),
(16, 9, 1, '2025-11-28 15:21:43'),
(17, 10, 1, '2025-11-28 15:21:43'),
(18, 19, 1, '2025-11-28 15:21:43'),
(19, 20, 1, '2025-11-28 15:21:43'),
(20, 14, 2, '2025-11-28 15:21:43'),
(21, 15, 2, '2025-11-28 15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `libro_tag`
--

CREATE TABLE `libro_tag` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `ejemplar_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `estado` enum('Activo','Devuelto','Vencido','Perdido') COLLATE utf8mb4_unicode_ci DEFAULT 'Activo',
  `multa` decimal(10,2) DEFAULT '0.00',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prestamos`
--

INSERT INTO `prestamos` (`id`, `libro_id`, `ejemplar_id`, `usuario_id`, `fecha_prestamo`, `fecha_vencimiento`, `fecha_devolucion`, `estado`, `multa`, `notas`, `created_at`, `updated_at`) VALUES
(1, 8, 15, 5, '2025-12-01', '2025-12-15', NULL, 'Devuelto', 0.00, '', '2025-12-01 13:49:20', '2025-12-01 13:54:25'),
(2, 1, 1, 6, '2025-11-27', '2025-12-15', NULL, 'Devuelto', 0.00, '', '2025-12-01 13:53:35', '2025-12-01 14:03:43'),
(3, 11, 19, 6, '2025-12-01', '2025-12-15', NULL, 'Devuelto', 0.00, '', '2025-12-01 13:58:07', '2025-12-01 13:58:51'),
(4, 8, 15, 2, '2025-12-01', '2025-12-15', '2025-12-01', 'Devuelto', 0.00, NULL, '2025-12-01 14:07:22', '2025-12-01 14:08:51'),
(5, 8, 32, 1, '2025-12-01', '2025-12-15', NULL, 'Activo', 0.00, NULL, '2025-12-01 14:08:45', '2025-12-01 14:08:45');

--
-- Triggers `prestamos`
--
DELIMITER $$
CREATE TRIGGER `actualizar_estado_ejemplar_devolucion` AFTER UPDATE ON `prestamos` FOR EACH ROW BEGIN
    IF NEW.fecha_devolucion IS NOT NULL AND OLD.fecha_devolucion IS NULL THEN
        UPDATE ejemplares 
        SET estado_disponibilidad = 'Disponible'
        WHERE id = NEW.ejemplar_id;
        
        UPDATE libros 
        SET ultima_fecha_devolucion = NEW.fecha_devolucion
        WHERE id = NEW.libro_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `actualizar_estado_ejemplar_prestamo` AFTER INSERT ON `prestamos` FOR EACH ROW BEGIN
    UPDATE ejemplares 
    SET estado_disponibilidad = 'Prestado'
    WHERE id = NEW.ejemplar_id;
    
    UPDATE libros 
    SET numero_prestamos = numero_prestamos + 1,
        ultima_fecha_prestamo = NEW.fecha_prestamo
    WHERE id = NEW.libro_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `resenas`
--

CREATE TABLE `resenas` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `calificacion` int NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `fecha_resena` date NOT NULL,
  `aprobada` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Triggers `resenas`
--
DELIMITER $$
CREATE TRIGGER `actualizar_valoracion_libro` AFTER INSERT ON `resenas` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reservas`
--

CREATE TABLE `reservas` (
  `id` int NOT NULL,
  `libro_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha_reserva` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('Pendiente','Completada','Cancelada','Vencida') COLLATE utf8mb4_unicode_ci DEFAULT 'Pendiente',
  `fecha_notificacion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservas`
--

INSERT INTO `reservas` (`id`, `libro_id`, `usuario_id`, `fecha_reserva`, `fecha_vencimiento`, `estado`, `fecha_notificacion`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2025-12-01', '2025-12-08', 'Completada', NULL, '2025-12-01 13:40:14', '2025-12-01 13:53:35'),
(3, 11, 6, '2025-12-01', '2025-12-08', 'Completada', NULL, '2025-12-01 13:43:50', '2025-12-01 13:58:07'),
(4, 8, 2, '2025-12-01', '2025-12-08', 'Completada', NULL, '2025-12-01 14:06:43', '2025-12-01 14:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `numero_cedula` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('Activo','Inactivo','Suspendido') COLLATE utf8mb4_unicode_ci DEFAULT 'Activo',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `numero_cedula`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `fecha_nacimiento`, `fecha_registro`, `estado`, `notas`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1234567890', 'María', 'González', 'maria.gonzalez@email.com', '555-0101', 'Calle Principal 123', '1990-05-15', '2025-11-28 15:21:43', 'Activo', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(2, '2345678901', 'Juan', 'Pérez', 'juan.perez@email.com', '555-0102', 'Avenida Central 456', '1985-08-20', '2025-11-28 15:21:43', 'Activo', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(3, '3456789012', 'Ana', 'Martínez', 'ana.martinez@email.com', '555-0103', 'Boulevard Norte 789', '1992-11-10', '2025-11-28 15:21:43', 'Activo', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(4, '4567890123', 'Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '555-0104', 'Calle Sur 321', '1988-03-25', '2025-11-28 15:21:43', 'Activo', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(5, '5678901234', 'Laura', 'Sánchez', 'laura.sanchez@email.com', '555-0105', 'Avenida Este 654', '1995-07-30', '2025-11-28 15:21:43', 'Activo', NULL, '2025-11-28 15:21:43', '2025-11-28 15:21:43', NULL),
(6, '123123', 'Usuario', 'Temporal', NULL, NULL, NULL, NULL, '2025-12-01 13:40:14', 'Activo', NULL, '2025-12-01 13:40:14', '2025-12-01 13:40:14', NULL),
(7, '5151910', 'Usuario', 'Temporal', NULL, NULL, NULL, NULL, '2025-12-01 13:43:35', 'Activo', NULL, '2025-12-01 13:43:35', '2025-12-01 13:43:35', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_libros_disponibles`
-- (See below for the actual view)
--
CREATE TABLE `vista_libros_disponibles` (
`anio_publicacion` int
,`autores` text
,`codigo_interno` varchar(50)
,`editorial` varchar(255)
,`ejemplares_disponibles` bigint
,`id` int
,`isbn` varchar(20)
,`numero_prestamos` int
,`titulo` varchar(255)
,`total_ejemplares` bigint
,`valoracion_promedio` decimal(3,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_prestamos_activos`
-- (See below for the actual view)
--
CREATE TABLE `vista_prestamos_activos` (
`codigo_ejemplar` varchar(50)
,`codigo_interno` varchar(50)
,`dias_vencido` int
,`fecha_prestamo` date
,`fecha_vencimiento` date
,`id` int
,`libro` varchar(255)
,`multa` decimal(10,2)
,`numero_cedula` varchar(50)
,`telefono` varchar(20)
,`usuario` varchar(511)
);

-- --------------------------------------------------------

--
-- Structure for view `vista_libros_disponibles`
--
DROP TABLE IF EXISTS `vista_libros_disponibles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_libros_disponibles`  AS SELECT `l`.`id` AS `id`, `l`.`codigo_interno` AS `codigo_interno`, `l`.`titulo` AS `titulo`, `l`.`isbn` AS `isbn`, group_concat(distinct `a`.`nombre_completo` separator ', ') AS `autores`, `e`.`nombre` AS `editorial`, `l`.`anio_publicacion` AS `anio_publicacion`, count(distinct (case when (`ej`.`estado_disponibilidad` = 'Disponible') then `ej`.`id` end)) AS `ejemplares_disponibles`, count(distinct `ej`.`id`) AS `total_ejemplares`, `l`.`valoracion_promedio` AS `valoracion_promedio`, `l`.`numero_prestamos` AS `numero_prestamos` FROM ((((`libros` `l` left join `libro_autor` `la` on((`l`.`id` = `la`.`libro_id`))) left join `autores` `a` on((`la`.`autor_id` = `a`.`id`))) left join `editoriales` `e` on((`l`.`editorial_id` = `e`.`id`))) left join `ejemplares` `ej` on(((`l`.`id` = `ej`.`libro_id`) and (`ej`.`deleted_at` is null)))) WHERE (`l`.`deleted_at` is null) GROUP BY `l`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `vista_prestamos_activos`
--
DROP TABLE IF EXISTS `vista_prestamos_activos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_prestamos_activos`  AS SELECT `p`.`id` AS `id`, `p`.`fecha_prestamo` AS `fecha_prestamo`, `p`.`fecha_vencimiento` AS `fecha_vencimiento`, (to_days(curdate()) - to_days(`p`.`fecha_vencimiento`)) AS `dias_vencido`, `l`.`titulo` AS `libro`, `l`.`codigo_interno` AS `codigo_interno`, `ej`.`codigo_ejemplar` AS `codigo_ejemplar`, concat(`u`.`nombre`,' ',`u`.`apellido`) AS `usuario`, `u`.`numero_cedula` AS `numero_cedula`, `u`.`telefono` AS `telefono`, `p`.`multa` AS `multa` FROM (((`prestamos` `p` join `libros` `l` on((`p`.`libro_id` = `l`.`id`))) join `ejemplares` `ej` on((`p`.`ejemplar_id` = `ej`.`id`))) join `usuarios` `u` on((`p`.`usuario_id` = `u`.`id`))) WHERE (`p`.`estado` = 'Activo') ORDER BY `p`.`fecha_vencimiento` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_apellido` (`apellido`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `categoria_padre_id` (`categoria_padre_id`),
  ADD KEY `idx_nombre` (`nombre`);

--
-- Indexes for table `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nombre` (`nombre`);

--
-- Indexes for table `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_ejemplar` (`codigo_ejemplar`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_codigo_ejemplar` (`codigo_ejemplar`),
  ADD KEY `idx_estado_disponibilidad` (`estado_disponibilidad`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `idiomas`
--
ALTER TABLE `idiomas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_interno` (`codigo_interno`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `editorial_id` (`editorial_id`),
  ADD KEY `idioma_id` (`idioma_id`),
  ADD KEY `idx_titulo` (`titulo`),
  ADD KEY `idx_isbn` (`isbn`),
  ADD KEY `idx_codigo_interno` (`codigo_interno`),
  ADD KEY `idx_estado_disponibilidad` (`estado_disponibilidad`),
  ADD KEY `idx_destacado` (`destacado`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `libro_autor`
--
ALTER TABLE `libro_autor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_libro_autor` (`libro_id`,`autor_id`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_autor` (`autor_id`);

--
-- Indexes for table `libro_categoria`
--
ALTER TABLE `libro_categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_libro_categoria` (`libro_id`,`categoria_id`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_categoria` (`categoria_id`);

--
-- Indexes for table `libro_tag`
--
ALTER TABLE `libro_tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_libro_tag` (`libro_id`,`tag_id`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_tag` (`tag_id`);

--
-- Indexes for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_ejemplar` (`ejemplar_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_vencimiento` (`fecha_vencimiento`);

--
-- Indexes for table `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_libro_usuario` (`libro_id`,`usuario_id`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_calificacion` (`calificacion`);

--
-- Indexes for table `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_libro` (`libro_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_vencimiento` (`fecha_vencimiento`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_nombre` (`nombre`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_cedula` (`numero_cedula`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_nombre` (`nombre`,`apellido`),
  ADD KEY `idx_estado` (`estado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ejemplares`
--
ALTER TABLE `ejemplares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `idiomas`
--
ALTER TABLE `idiomas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `libro_autor`
--
ALTER TABLE `libro_autor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `libro_categoria`
--
ALTER TABLE `libro_categoria`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `libro_tag`
--
ALTER TABLE `libro_tag`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD CONSTRAINT `ejemplares_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`editorial_id`) REFERENCES `editoriales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `libros_ibfk_2` FOREIGN KEY (`idioma_id`) REFERENCES `idiomas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `libro_autor`
--
ALTER TABLE `libro_autor`
  ADD CONSTRAINT `libro_autor_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `libro_autor_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `libro_categoria`
--
ALTER TABLE `libro_categoria`
  ADD CONSTRAINT `libro_categoria_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `libro_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `libro_tag`
--
ALTER TABLE `libro_tag`
  ADD CONSTRAINT `libro_tag_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `libro_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`ejemplar_id`) REFERENCES `ejemplares` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `prestamos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
