<?php
/**
 * Constantes del Sistema de Sorteo
 * Configuración global de rutas y parámetros
 */

// ===== CONFIGURACIÓN DE RUTAS =====
// Por:
define('BASE_URL', 'http://localhost/sorteo_system/public/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_PATH', __DIR__ . '/../uploads/');
define('LOGS_PATH', __DIR__ . '/../logs/');

// ===== CONFIGURACIÓN DE BASE DE DATOS =====
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'bdd_sorteo_cmaicao');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_CHARSET', 'utf8mb4');

// ===== ESTADOS DEL SISTEMA =====
define('ESTADO_ACTIVO', 1);
define('ESTADO_INACTIVO', 0);

// ===== ROLES DE USUARIO =====
define('ROL_CONCURSANTE', 'concursante');
define('ROL_MODERADOR', 'moderador');

// ===== ESTADOS DE SORTEO =====
define('SORTEO_EN_JUEGO', 1);
define('SORTEO_TERMINADO', 0);
define('SORTEO_PAUSADO', 2);

// ===== CONFIGURACIÓN DE SESIONES =====
define('SESSION_TIMEOUT', 1800); // 30 minutos
define('SESSION_NAME', 'sorteo_session');

// ===== CONFIGURACIÓN DE LOGS =====
define('LOG_LEVEL', 'INFO');
define('DEBUG_MODE', true);

// ===== CONFIGURACIÓN DE BALOTAS =====
define('BALOTA_MIN', 100);
define('BALOTA_MAX', 800);
define('MAX_ELECCIONES_DEFAULT', 1);

// ===== CONFIGURACIÓN DE ARCHIVOS =====
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// ===== CONFIGURACIÓN DE PAGINACIÓN =====
define('ITEMS_PER_PAGE', 20);

// ===== MENSAJES DEL SISTEMA =====
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'danger');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// ===== CONFIGURACIÓN DE TIMEZONE =====
date_default_timezone_set('America/Bogota');

// ===== CONFIGURACIÓN DE ERRORES =====
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>