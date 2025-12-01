<?php

require_once __DIR__ . '/Model.php';

class LibroModel extends Model {
    
    public function __construct() {
        parent::__construct('libros');
    }
    
    public function buscar($termino) {
        $sql = "SELECT DISTINCT
                    l.id,
                    l.titulo,
                    l.codigo_interno,
                    l.isbn,
                    l.sinopsis,
                    COUNT(DISTINCT CASE WHEN ej.estado_disponibilidad = 'Disponible' THEN ej.id END) AS ejemplares_disponibles_base,
                    COUNT(DISTINCT ej.id) AS total_ejemplares,
                    (SELECT COUNT(*) 
                     FROM reservas r 
                     WHERE r.libro_id = l.id 
                     AND r.estado = 'Pendiente') AS reservas_pendientes,
                    GROUP_CONCAT(DISTINCT a.nombre_completo SEPARATOR ', ') AS autores,
                    e.nombre AS editorial
                FROM libros l
                LEFT JOIN libro_autor la ON l.id = la.libro_id
                LEFT JOIN autores a ON la.autor_id = a.id
                LEFT JOIN editoriales e ON l.editorial_id = e.id
                LEFT JOIN ejemplares ej ON l.id = ej.libro_id AND ej.deleted_at IS NULL
                WHERE (l.titulo LIKE ? 
                    OR l.codigo_interno LIKE ? 
                    OR l.isbn LIKE ? 
                    OR a.nombre LIKE ? 
                    OR a.apellido LIKE ?)
                    AND l.deleted_at IS NULL
                GROUP BY l.id
                HAVING total_ejemplares > 0
                ORDER BY l.titulo";
        
        $termino = "%{$termino}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$termino, $termino, $termino, $termino, $termino]);
        $resultados = $stmt->fetchAll();
        
        // Ajustar ejemplares disponibles restando las reservas pendientes
        foreach ($resultados as &$libro) {
            $disponiblesBase = intval($libro['ejemplares_disponibles_base'] ?? 0);
            $reservasPendientes = intval($libro['reservas_pendientes'] ?? 0);
            $libro['ejemplares_disponibles'] = max(0, $disponiblesBase - $reservasPendientes);
            unset($libro['ejemplares_disponibles_base']);
            unset($libro['reservas_pendientes']);
        }
        
        return $resultados;
    }
    
    public function getLibroCompleto($id) {
        $sql = "SELECT l.*, 
                    e.nombre AS editorial_nombre,
                    i.nombre AS idioma_nombre
                FROM libros l
                LEFT JOIN editoriales e ON l.editorial_id = e.id
                LEFT JOIN idiomas i ON l.idioma_id = i.id
                WHERE l.id = ? AND l.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $libro = $stmt->fetch();
        
        if ($libro) {
            // Obtener autores
            $stmt = $this->db->prepare("
                SELECT a.*, la.orden 
                FROM autores a
                INNER JOIN libro_autor la ON a.id = la.autor_id
                WHERE la.libro_id = ?
                ORDER BY la.orden
            ");
            $stmt->execute([$id]);
            $libro['autores'] = $stmt->fetchAll();
            
            // Obtener categorías
            $stmt = $this->db->prepare("
                SELECT c.* 
                FROM categorias c
                INNER JOIN libro_categoria lc ON c.id = lc.categoria_id
                WHERE lc.libro_id = ?
            ");
            $stmt->execute([$id]);
            $libro['categorias'] = $stmt->fetchAll();
            
            // Obtener tags
            $stmt = $this->db->prepare("
                SELECT t.* 
                FROM tags t
                INNER JOIN libro_tag lt ON t.id = lt.tag_id
                WHERE lt.libro_id = ?
            ");
            $stmt->execute([$id]);
            $libro['tags'] = $stmt->fetchAll();
        }
        
        return $libro;
    }
    
    public function createWithRelations($data, $autores = [], $categorias = [], $tags = []) {
        $this->db->beginTransaction();
        
        try {
            // Filtrar valores vacíos pero mantener null explícitos
            $filteredData = [];
            foreach ($data as $key => $value) {
                if ($value !== '' || $value === null) {
                    $filteredData[$key] = $value;
                }
            }
            
            // Crear el libro
            // Construir SQL de forma segura con caracteres especiales
            $fields = [];
            $placeholders = [];
            $bindValues = [];
            
            foreach ($filteredData as $key => $value) {
                // Usar el nombre de columna tal cual está en la base de datos
                $fields[] = "`{$key}`";
                // Crear placeholder único para evitar problemas con caracteres especiales
                $placeholder = ":p" . count($placeholders);
                $placeholders[] = $placeholder;
                $bindValues[$placeholder] = $value;
            }
            
            $fieldsStr = implode(', ', $fields);
            $placeholdersStr = implode(', ', $placeholders);
            $sql = "INSERT INTO `{$this->table}` ({$fieldsStr}) VALUES ({$placeholdersStr})";
            
            try {
                $stmt = $this->db->prepare($sql);
                
                foreach ($bindValues as $placeholder => $value) {
                    // Manejar null explícitamente
                    if ($value === null) {
                        $stmt->bindValue($placeholder, null, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($placeholder, $value);
                    }
                }
                
                $stmt->execute();
            } catch (PDOException $e) {
                error_log("SQL Error: " . $e->getMessage());
                error_log("SQL Query: " . $sql);
                error_log("Fields: " . print_r(array_keys($filteredData), true));
                error_log("Placeholders: " . print_r($placeholders, true));
                throw $e;
            }
            $libroId = $this->db->lastInsertId();
            
            // Insertar autores
            foreach ($autores as $index => $autorId) {
                $stmt = $this->db->prepare("INSERT INTO libro_autor (libro_id, autor_id, orden) VALUES (?, ?, ?)");
                $stmt->execute([$libroId, $autorId, $index + 1]);
            }
            
            // Insertar categorías
            foreach ($categorias as $categoriaId) {
                $stmt = $this->db->prepare("INSERT INTO libro_categoria (libro_id, categoria_id) VALUES (?, ?)");
                $stmt->execute([$libroId, $categoriaId]);
            }
            
            // Insertar tags
            foreach ($tags as $tagId) {
                $stmt = $this->db->prepare("INSERT INTO libro_tag (libro_id, tag_id) VALUES (?, ?)");
                $stmt->execute([$libroId, $tagId]);
            }
            
            $this->db->commit();
            return $libroId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function updateWithRelations($id, $data, $autores = [], $categorias = [], $tags = []) {
        $this->db->beginTransaction();
        
        try {
            // Actualizar el libro
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "`{$key}` = :{$key}";
            }
            $set = implode(', ', $set);
            
            $sql = "UPDATE `{$this->table}` SET {$set} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            // Eliminar relaciones existentes
            $this->db->prepare("DELETE FROM libro_autor WHERE libro_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM libro_categoria WHERE libro_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM libro_tag WHERE libro_id = ?")->execute([$id]);
            
            // Insertar nuevas relaciones
            foreach ($autores as $index => $autorId) {
                $stmt = $this->db->prepare("INSERT INTO libro_autor (libro_id, autor_id, orden) VALUES (?, ?, ?)");
                $stmt->execute([$id, $autorId, $index + 1]);
            }
            
            foreach ($categorias as $categoriaId) {
                $stmt = $this->db->prepare("INSERT INTO libro_categoria (libro_id, categoria_id) VALUES (?, ?)");
                $stmt->execute([$id, $categoriaId]);
            }
            
            foreach ($tags as $tagId) {
                $stmt = $this->db->prepare("INSERT INTO libro_tag (libro_id, tag_id) VALUES (?, ?)");
                $stmt->execute([$id, $tagId]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

