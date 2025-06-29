<?php
// ==================================================================
// MODELS/BALOTA.PHP
// ==================================================================
class Balota extends Database {
    
    public function obtenerBalotasDisponibles($idSorteo) {
        $sql = "SELECT b.* FROM balotas b 
                WHERE b.numero_balota NOT IN (
                    SELECT bc.numero_balota 
                    FROM balota_concursante bc 
                    INNER JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                    WHERE es.id_sorteo = ?
                )
                ORDER BY CAST(b.numero_balota AS UNSIGNED)";
        return $this->fetchAll($sql, [$idSorteo]);
    }
    
    public function verificarBalotaDisponible($numeroBalota, $idSorteo) {
        $sql = "SELECT COUNT(*) as count FROM balota_concursante bc
                INNER JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                WHERE bc.numero_balota = ? AND es.id_sorteo = ?";
        $result = $this->fetch($sql, [$numeroBalota, $idSorteo]);
        return $result['count'] == 0;
    }
    
    public function elegirBalota($idEmpleadoSort, $numeroBalota) {
        $this->beginTransaction();
        try {
            // Verificar que el usuario aún puede elegir balotas
            if (!$this->puedeElegirBalota($idEmpleadoSort)) {
                throw new Exception("Ya has elegido el máximo de balotas permitidas");
            }
            
            // Verificar que la balota esté disponible
            $sqlCheck = "SELECT COUNT(*) as count FROM balota_concursante 
                        WHERE id_empleado_sort = ? AND numero_balota = ?";
            $exists = $this->fetch($sqlCheck, [$idEmpleadoSort, $numeroBalota]);
            
            if ($exists['count'] > 0) {
                throw new Exception("Ya tienes esta balota asignada");
            }
            
            // Insertar la balota
            $sql = "INSERT INTO balota_concursante (id_empleado_sort, numero_balota) 
                    VALUES (?, ?)";
            $this->query($sql, [$idEmpleadoSort, $numeroBalota]);
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    public function puedeElegirBalota($idEmpleadoSort) {
        $sql = "SELECT es.cantidad_elecciones,
                (SELECT COUNT(*) FROM balota_concursante bc WHERE bc.id_empleado_sort = es.id) as elegidas
                FROM empleados_en_sorteo es
                WHERE es.id = ?";
        $result = $this->fetch($sql, [$idEmpleadoSort]);
        
        if (!$result) return false;
        
        return $result['elegidas'] < $result['cantidad_elecciones'];
    }
    
    public function obtenerBalotasUsuario($idEmpleadoSort) {
        $sql = "SELECT bc.*, b.equivalencia_binaria 
                FROM balota_concursante bc
                INNER JOIN balotas b ON bc.numero_balota = b.numero_balota
                WHERE bc.id_empleado_sort = ?
                ORDER BY bc.fecha_eleccion DESC";
        return $this->fetchAll($sql, [$idEmpleadoSort]);
    }
    
    public function obtenerResumenBalotas($idSorteo) {
        $sql = "SELECT bc.numero_balota, e.nombre_completo, e.numero_documento, 
                bc.fecha_eleccion
                FROM balota_concursante bc
                INNER JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                INNER JOIN empleados e ON es.id_empleado = e.id
                WHERE es.id_sorteo = ?
                ORDER BY CAST(bc.numero_balota AS UNSIGNED)";
        return $this->fetchAll($sql, [$idSorteo]);
    }
    
    public function elegirBalotaAleatoria($idEmpleadoSort, $idSorteo) {
        $balotasDisponibles = $this->obtenerBalotasDisponibles($idSorteo);
        
        if (empty($balotasDisponibles)) {
            throw new Exception("No hay balotas disponibles");
        }
        
        $balotaAleatoria = $balotasDisponibles[array_rand($balotasDisponibles)];
        return $this->elegirBalota($idEmpleadoSort, $balotaAleatoria['numero_balota']);
    }
}