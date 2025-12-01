<?php
/**
 * Script para crear la tabla de ejemplares
 * Ejecutar una sola vez desde el navegador: http://localhost:8888/biblio/instalar_ejemplares.php
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Instalando sistema de ejemplares...</h2>";
    
    // Crear tabla de ejemplares
    $sql1 = "CREATE TABLE IF NOT EXISTS ejemplares (
        id INT PRIMARY KEY AUTO_INCREMENT,
        libro_id INT NOT NULL,
        codigo_ejemplar VARCHAR(50) UNIQUE NOT NULL,
        estado_fisico ENUM('Excelente', 'Bueno', 'Regular', 'Malo', 'Requiere reparación', 'Perdido') NOT NULL DEFAULT 'Bueno',
        estado_disponibilidad ENUM('Disponible', 'Prestado', 'Reservado', 'En reparación', 'No disponible') NOT NULL DEFAULT 'Disponible',
        ubicacion_fisica VARCHAR(100),
        fecha_adquisicion DATE,
        precio_compra DECIMAL(10,2),
        notas TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL,
        FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
        INDEX idx_libro (libro_id),
        INDEX idx_codigo_ejemplar (codigo_ejemplar),
        INDEX idx_estado_disponibilidad (estado_disponibilidad),
        INDEX idx_deleted_at (deleted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql1);
    echo "<p>✓ Tabla 'ejemplares' creada</p>";
    
    // Verificar si la columna ejemplar_id ya existe
    $checkColumn = $db->query("SHOW COLUMNS FROM prestamos LIKE 'ejemplar_id'");
    if ($checkColumn->rowCount() == 0) {
        // Agregar columna ejemplar_id a prestamos
        $sql2 = "ALTER TABLE prestamos ADD COLUMN ejemplar_id INT NULL AFTER libro_id";
        $db->exec($sql2);
        echo "<p>✓ Columna 'ejemplar_id' agregada a tabla 'prestamos'</p>";
        
        // Agregar foreign key
        $sql3 = "ALTER TABLE prestamos ADD FOREIGN KEY (ejemplar_id) REFERENCES ejemplares(id) ON DELETE RESTRICT";
        $db->exec($sql3);
        echo "<p>✓ Foreign key agregada</p>";
        
        // Agregar índice
        $sql4 = "CREATE INDEX idx_ejemplar ON prestamos(ejemplar_id)";
        $db->exec($sql4);
        echo "<p>✓ Índice agregado</p>";
    } else {
        echo "<p>✓ Columna 'ejemplar_id' ya existe</p>";
    }
    
    // Migrar datos existentes: crear un ejemplar por cada libro existente
    $sql5 = "INSERT INTO ejemplares (libro_id, codigo_ejemplar, estado_fisico, estado_disponibilidad, ubicacion_fisica, fecha_adquisicion, precio_compra)
    SELECT 
        id,
        CONCAT(codigo_interno, '-E1') AS codigo_ejemplar,
        estado_fisico,
        estado_disponibilidad,
        ubicacion_fisica,
        fecha_adquisicion,
        precio_compra
    FROM libros
    WHERE deleted_at IS NULL
    AND NOT EXISTS (
        SELECT 1 FROM ejemplares WHERE ejemplares.libro_id = libros.id
    )";
    
    $stmt = $db->prepare($sql5);
    $stmt->execute();
    $ejemplaresCreados = $stmt->rowCount();
    echo "<p>✓ Se crearon {$ejemplaresCreados} ejemplares desde libros existentes</p>";
    
    // Actualizar préstamos existentes
    $sql6 = "UPDATE prestamos p
    INNER JOIN ejemplares e ON p.libro_id = e.libro_id
    SET p.ejemplar_id = e.id
    WHERE p.ejemplar_id IS NULL";
    
    $stmt = $db->prepare($sql6);
    $stmt->execute();
    $prestamosActualizados = $stmt->rowCount();
    echo "<p>✓ Se actualizaron {$prestamosActualizados} préstamos existentes</p>";
    
    echo "<h3 style='color: green;'>✓ Instalación completada exitosamente</h3>";
    echo "<p><a href='admin/index.php'>Ir al panel de administración</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}


