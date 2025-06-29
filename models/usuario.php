<?php
// ==================================================================
// MODELS/USUARIO.PHP
// ==================================================================
class Usuario extends Database {
    
    public function autenticar($numeroDocumento, $clave) {
        $sql = "SELECT * FROM vw_consulta_usuarios WHERE numero_documento = ? AND clave = ? AND estado = 1";
        $usuario = $this->fetch($sql, [$numeroDocumento, $clave]);
        
        if ($usuario) {
            $this->registrarAcceso($numeroDocumento);
            return $usuario;
        }
        return false;
    }
    
    public function obtenerUsuarioPorDocumento($numeroDocumento) {
        $sql = "SELECT * FROM vw_consulta_usuarios WHERE numero_documento = ?";
        return $this->fetch($sql, [$numeroDocumento]);
    }
    
    public function crearUsuarioConcursante($idEmpleadoSort, $clave) {
        $sql = "INSERT INTO usuario_concurso (id_empleado_sort, clave) VALUES (?, ?)";
        return $this->query($sql, [$idEmpleadoSort, $clave]);
    }
    
    public function obtenerEmpleadoPorDocumento($numeroDocumento) {
        $sql = "SELECT * FROM empleados WHERE numero_documento = ? AND estado_emplado = 1";
        return $this->fetch($sql, [$numeroDocumento]);
    }
    
    public function verificarParticipacionSorteo($idEmpleado, $idSorteo) {
        $sql = "SELECT * FROM empleados_en_sorteo WHERE id_empleado = ? AND id_sorteo = ? AND estado = 1";
        return $this->fetch($sql, [$idEmpleado, $idSorteo]);
    }
    
    private function registrarAcceso($numeroDocumento) {
        // Implementar registro de acceso si es necesario
    }
}