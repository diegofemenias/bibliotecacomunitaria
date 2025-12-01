<?php

require_once __DIR__ . '/Model.php';

class UsuarioModel extends Model {
    
    public function __construct() {
        parent::__construct('usuarios');
    }
    
    public function getByCedula($cedula) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE numero_cedula = ? AND deleted_at IS NULL");
        $stmt->execute([$cedula]);
        return $stmt->fetch();
    }
}


