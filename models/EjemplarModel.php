<?php

require_once __DIR__ . '/Model.php';

class EjemplarModel extends Model {
    
    public function __construct() {
        parent::__construct('ejemplares');
    }
    
    public function getByLibro($libroId) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE libro_id = ? AND deleted_at IS NULL 
            ORDER BY codigo_ejemplar ASC
        ");
        $stmt->execute([$libroId]);
        return $stmt->fetchAll();
    }
    
    public function getDisponiblesByLibro($libroId) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE libro_id = ? 
            AND estado_disponibilidad = 'Disponible' 
            AND deleted_at IS NULL 
            ORDER BY codigo_ejemplar ASC
        ");
        $stmt->execute([$libroId]);
        return $stmt->fetchAll();
    }
    
    public function contarDisponibles($libroId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE libro_id = ? 
            AND estado_disponibilidad = 'Disponible' 
            AND deleted_at IS NULL
        ");
        $stmt->execute([$libroId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function contarTotal($libroId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE libro_id = ? 
            AND deleted_at IS NULL
        ");
        $stmt->execute([$libroId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}


