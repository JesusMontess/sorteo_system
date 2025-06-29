<?php
// ==================================================================
// INCLUDES/VALIDATOR.PHP
// ==================================================================
class Validator {
    public static function validarDocumento($documento) {
        return preg_match('/^[0-9]{7,15}$/', $documento);
    }
    
    public static function validarFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    
    public static function validarNumeroBalota($numero) {
        return is_numeric($numero) && $numero >= 100 && $numero <= 800;
    }
    
    public static function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'));
    }
    
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validarContrasena($password) {
        // Mínimo 6 caracteres
        return strlen($password) >= 6;
    }
}