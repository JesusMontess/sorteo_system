
<?php
// ==================================================================
// MODELS/SORTEO.PHP
// ==================================================================
class Sorteo extends Database {
    
    public function obtenerSorteoActivo() {
        $sql = "SELECT * FROM apertura_sorteo WHERE estado = 1 ORDER BY fecha_inicio_sorteo DESC LIMIT 1";
        return $this->fetch($sql);
    }
    
    public function obtenerTodosSorteos() {
        $sql = "SELECT *, 
                CASE 
                    WHEN estado = 1 THEN 'EN JUEGO'
                    WHEN estado = 0 THEN 'TERMINADO'
                    ELSE 'PAUSADO'
                END as estado_texto
                FROM apertura_sorteo ORDER BY fecha_inicio_sorteo DESC";
        return $this->fetchAll($sql);
    }
    
    public function crearSorteo($descripcion, $fechaInicio, $fechaCierre) {
        $sql = "INSERT INTO apertura_sorteo (descripcion, fecha_inicio_sorteo, fecha_cierre_sorteo) 
                VALUES (?, ?, ?)";
        return $this->query($sql, [$descripcion, $fechaInicio, $fechaCierre]);
    }
    
    public function cerrarSorteo($idSorteo) {
        $sql = "UPDATE apertura_sorteo SET estado = 0 WHERE id = ?";
        return $this->query($sql, [$idSorteo]);
    }
    
    public function pausarSorteo($idSorteo) {
        $sql = "UPDATE apertura_sorteo SET estado = 2 WHERE id = ?";
        return $this->query($sql, [$idSorteo]);
    }
    
    public function reanudarSorteo($idSorteo) {
        $sql = "UPDATE apertura_sorteo SET estado = 1 WHERE id = ?";
        return $this->query($sql, [$idSorteo]);
    }
    
    public function obtenerParticipantesSorteo($idSorteo) {
        $sql = "SELECT es.*, e.nombre_completo, e.numero_documento,
                (SELECT COUNT(*) FROM balota_concursante bc WHERE bc.id_empleado_sort = es.id) as balotas_jugadas
                FROM empleados_en_sorteo es
                INNER JOIN empleados e ON es.id_empleado = e.id
                WHERE es.id_sorteo = ? AND es.estado = 1";
        return $this->fetchAll($sql, [$idSorteo]);
    }
}