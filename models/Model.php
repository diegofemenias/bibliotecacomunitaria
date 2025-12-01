<?php

require_once __DIR__ . '/../config/database.php';

class Model {
    protected $db;
    protected $table;
    protected $hasDeletedAt = null; // Cache para verificar si tiene deleted_at
    
    public function __construct($table) {
        $this->db = Database::getInstance()->getConnection();
        $this->table = $table;
    }
    
    /**
     * Verifica si la tabla tiene la columna deleted_at
     */
    protected function hasDeletedAtColumn() {
        if ($this->hasDeletedAt !== null) {
            return $this->hasDeletedAt;
        }
        
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'deleted_at'");
            $this->hasDeletedAt = $stmt->rowCount() > 0;
            return $this->hasDeletedAt;
        } catch (Exception $e) {
            $this->hasDeletedAt = false;
            return false;
        }
    }
    
    public function getAll() {
        $whereClause = $this->hasDeletedAtColumn() ? "WHERE deleted_at IS NULL" : "";
        $stmt = $this->db->query("SELECT * FROM {$this->table} {$whereClause} ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $whereClause = $this->hasDeletedAtColumn() ? "AND deleted_at IS NULL" : "";
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? {$whereClause}");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        // Filtrar valores null para campos opcionales, pero mantener null explícitos
        $filteredData = [];
        foreach ($data as $key => $value) {
            // Solo incluir si no es string vacío, pero permitir null explícitos
            if ($value !== '' || $value === null) {
                $filteredData[$key] = $value;
            }
        }
        
        if (empty($filteredData)) {
            return false;
        }
        
        // Escapar nombres de columnas con backticks
        $fields = '`' . implode('`, `', array_keys($filteredData)) . '`';
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO `{$this->table}` ({$fields}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($filteredData as $key => $value) {
                // Manejar null explícitamente
                if ($value === null) {
                    $stmt->bindValue(':' . $key, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':' . $key, $value);
                }
            }
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            error_log("SQL Query: " . $sql);
            throw $e;
        }
        return false;
    }
    
    public function update($id, $data) {
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
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        // Soft delete si la tabla tiene deleted_at, sino eliminación física
        if ($this->hasDeletedAtColumn()) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            // Eliminación física para tablas sin deleted_at
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function queryOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}

