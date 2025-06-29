<?php
// ==================================================================
// INCLUDES/LOGGER.PHP
// ==================================================================
class Logger {
    private static $logFile = __DIR__ . '/../logs/sistema.log';
    
    public static function log($nivel, $mensaje, $contexto = []) {
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
        $usuario = $_SESSION['usuario']['numero_documento'] ?? 'Anónimo';
        
        $logEntry = sprintf(
            "[%s] %s - IP: %s - Usuario: %s - %s - Contexto: %s\n",
            $fecha,
            strtoupper($nivel),
            $ip,
            $usuario,
            $mensaje,
            json_encode($contexto)
        );
        
        // Crear directorio de logs si no existe
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public static function info($mensaje, $contexto = []) {
        self::log('info', $mensaje, $contexto);
    }
    
    public static function warning($mensaje, $contexto = []) {
        self::log('warning', $mensaje, $contexto);
    }
    
    public static function error($mensaje, $contexto = []) {
        self::log('error', $mensaje, $contexto);
    }
    
    public static function debug($mensaje, $contexto = []) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            self::log('debug', $mensaje, $contexto);
        }
    }
}