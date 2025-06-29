<?php
// ==================================================================
// API/ENDPOINTS.PHP
// ==================================================================
class API {
    
    public static function manejarRequest() {
        header('Content-Type: application/json');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($uri, '/'));
        
        try {
            switch ($segments[2] ?? '') {
                case 'ping':
                    self::ping();
                    break;
                    
                case 'verificar-balota':
                    self::verificarBalota();
                    break;
                    
                case 'estadisticas':
                    self::obtenerEstadisticas();
                    break;
                    
                case 'notificaciones':
                    self::obtenerNotificaciones();
                    break;
                    
                default:
                    self::respuesta(['error' => 'Endpoint no encontrado'], 404);
            }
        } catch (Exception $e) {
            Logger::error('Error en API: ' . $e->getMessage());
            self::respuesta(['error' => 'Error interno del servidor'], 500);
        }
    }
    
    private static function ping() {
        self::respuesta(['status' => 'ok', 'timestamp' => time()]);
    }
    
    private static function verificarBalota() {
        if (!SessionManager::verificarSesion()) {
            self::respuesta(['error' => 'No autorizado'], 401);
            return;
        }
        
        $numero = $_GET['numero'] ?? '';
        if (!Validator::validarNumeroBalota($numero)) {
            self::respuesta(['error' => 'Número de balota inválido'], 400);
            return;
        }
        
        $balotaModel = new Balota();
        $sorteoModel = new Sorteo();
        
        $sorteoActivo = $sorteoModel->obtenerSorteoActivo();
        if (!$sorteoActivo) {
            self::respuesta(['disponible' => false, 'razon' => 'No hay sorteo activo']);
            return;
        }
        
        $disponible = $balotaModel->verificarBalotaDisponible($numero, $sorteoActivo['id']);
        self::respuesta(['disponible' => $disponible]);
    }
    
    private static function obtenerEstadisticas() {
        if (!SessionManager::esAdmin()) {
            self::respuesta(['error' => 'No autorizado'], 401);
            return;
        }
        
        $sorteoModel = new Sorteo();
        $balotaModel = new Balota();
        
        $sorteoActivo = $sorteoModel->obtenerSorteoActivo();
        $estadisticas = [];
        
        if ($sorteoActivo) {
            $participantes = $sorteoModel->obtenerParticipantesSorteo($sorteoActivo['id']);
            $balotas = $balotaModel->obtenerResumenBalotas($sorteoActivo['id']);
            
            $estadisticas = [
                'total_participantes' => count($participantes),
                'total_balotas_elegidas' => count($balotas),
                'balotas_disponibles' => count($balotaModel->obtenerBalotasDisponibles($sorteoActivo['id'])),
                'porcentaje_completado' => (count($balotas) / 800) * 100
            ];
        }
        
        self::respuesta($estadisticas);
    }
    
    private static function obtenerNotificaciones() {
        if (!SessionManager::verificarSesion()) {
            self::respuesta(['error' => 'No autorizado'], 401);
            return;
        }
        
        // Implementar sistema de notificaciones
        $notificaciones = [];
        self::respuesta($notificaciones);
    }
    
    private static function respuesta($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}
