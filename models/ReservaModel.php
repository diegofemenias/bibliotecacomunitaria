<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/UsuarioModel.php';

class ReservaModel extends Model {
    
    public function __construct() {
        parent::__construct('reservas');
    }
    
    public function crearReserva($libroId, $cedula, $ipAddress = null) {
        $this->db->beginTransaction();
        
        try {
            // Buscar o crear usuario
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->getByCedula($cedula);
            
            if (!$usuario) {
                // Crear usuario básico
                $usuarioId = $usuarioModel->create([
                    'numero_cedula' => $cedula,
                    'nombre' => 'Usuario',
                    'apellido' => 'Temporal',
                    'estado' => 'Activo'
                ]);
            } else {
                $usuarioId = $usuario['id'];
            }
            
            // Verificar si ya existe una reserva activa
            $stmt = $this->db->prepare("
                SELECT id FROM reservas 
                WHERE libro_id = ? AND usuario_id = ? AND estado = 'Pendiente'
            ");
            $stmt->execute([$libroId, $usuarioId]);
            if ($stmt->fetch()) {
                throw new Exception("Ya tiene una reserva pendiente para este libro");
            }
            
            // Validar restricción de IP solo si se proporciona una IP (reservas públicas)
            // Las reservas desde admin no pasan IP, por lo que no se validan
            if ($ipAddress && !empty($ipAddress)) {
                $stmt = $this->db->prepare("
                    SELECT id FROM reservas 
                    WHERE ip_address = ? 
                    AND DATE(fecha_reserva) = CURDATE()
                    AND estado = 'Pendiente'
                ");
                $stmt->execute([$ipAddress]);
                if ($stmt->fetch()) {
                    throw new Exception("Solo se permite una reserva por día desde este dispositivo. Puede realizar otra reserva mañana o cuando complete la reserva actual.");
                }
            }
            
            // Crear reserva
            $fechaVencimiento = date('Y-m-d', strtotime('+' . DIAS_VALIDEZ_RESERVA . ' days'));
            $dataReserva = [
                'libro_id' => $libroId,
                'usuario_id' => $usuarioId,
                'fecha_reserva' => date('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'Pendiente'
            ];
            
            // Solo agregar IP si se proporciona (reservas públicas)
            if ($ipAddress && !empty($ipAddress)) {
                $dataReserva['ip_address'] = $ipAddress;
            }
            
            $reservaId = $this->create($dataReserva);
            
            if (!$reservaId) {
                throw new Exception("Error al crear la reserva");
            }
            
            // No actualizamos el estado del libro, ya que las reservas son del libro en general
            // El estado de disponibilidad se calcula dinámicamente basado en los ejemplares
            
            $this->db->commit();
            return $reservaId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

