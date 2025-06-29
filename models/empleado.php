<?php
// ==================================================================
// MODELS/EMPLEADO.PHP
// ==================================================================
class Empleado extends Database {
    
    public function obtenerTodos() {
        $sql = "SELECT * FROM empleados WHERE estado_emplado = 1 ORDER BY nombre_completo";
        return $this->fetchAll($sql);
    }
    
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM empleados WHERE id = ?";
        return $this->fetch($sql, [$id]);
    }
    
    public function agregarEmpleado($tipoDoc, $numeroDoc, $nombreCompleto, $cargo, $area = null) {
        $sql = "INSERT INTO empleados (tipo_documento, numero_documento, nombre_completo, cargo, area) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->query($sql, [$tipoDoc, $numeroDoc, $nombreCompleto, $cargo, $area]);
    }
    
    public function inscribirEnSorteo($idSorteo, $idEmpleado, $cantidadElecciones = 1) {
        $sql = "INSERT INTO empleados_en_sorteo (id_sorteo, id_empleado, cantidad_elecciones, fecha_autorizacion) 
                VALUES (?, ?, ?, CURDATE())";
        return $this->query($sql, [$idSorteo, $idEmpleado, $cantidadElecciones]);
    }
    
    public function removerDeSorteo($idSorteo, $idEmpleado) {
        $sql = "UPDATE empleados_en_sorteo SET estado = 0 WHERE id_sorteo = ? AND id_empleado = ?";
        return $this->query($sql, [$idSorteo, $idEmpleado]);
    }
}