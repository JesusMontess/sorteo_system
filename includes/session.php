<?php

// ==================================================================
// INCLUDES/SESSION.PHP - COMPLEMENTO
// ==================================================================
session_start();

class SessionManager {
    public static function iniciarSesion($usuario) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    public static function verificarSesion() {
        if (!isset($_SESSION['logged_in'])) {
            return false;
        }
        
        // Verificar tiempo de inactividad (30 minutos)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            self::cerrarSesion();
            return false;
        }
        
        // Verificar IP y User Agent para mayor seguridad
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] || 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            self::cerrarSesion();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public static function cerrarSesion() {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
    
    public static function obtenerUsuario() {
        return $_SESSION['usuario'] ?? null;
    }
    
    public static function esAdmin() {
        return isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo_usuario'] === 'moderador';
    }
    
    public static function esConcursante() {
        return isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo_usuario'] === 'concursante';
    }
}
