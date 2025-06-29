<?php
// ==================================================================
// CRON/TAREAS.PHP
// ==================================================================
class TareasAutomaticas {
    
    public static function ejecutarTareasDiarias() {
        echo "Ejecutando tareas diarias...\n";
        
        try {
            // Limpiar logs antiguos
            self::limpiarLogsAntiguos();
            
            // Crear backup automático
            BackupManager::crearBackup();
            
            // Limpiar backups antiguos
            BackupManager::limpiarBackupsAntiguos();
            
            // Limpiar sesiones expiradas
            self::limpiarSesionesExpiradas();
            
            // Generar reporte diario
            self::generarReporteDiario();
            
            echo "✅ Tareas diarias completadas\n";
            
        } catch (Exception $e) {
            Logger::error("Error en tareas diarias: " . $e->getMessage());
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    private static function limpiarLogsAntiguos() {
        $directorioLogs = __DIR__ . '/../logs';
        $archivos = glob($directorioLogs . '/*.log');
        
        $eliminados = 0;
        foreach ($archivos as $archivo) {
            if (time() - filemtime($archivo) > (90 * 24 * 60 * 60)) { // 90 días
                unlink($archivo);
                $eliminados++;
            }
        }
        
        echo "🗑️  Logs antiguos eliminados: $eliminados\n";
    }
    
    private static function limpiarSesionesExpiradas() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $directorioSesiones = session_save_path();
        if (empty($directorioSesiones)) {
            $directorioSesiones = sys_get_temp_dir();
        }
        
        $archivos = glob($directorioSesiones . '/sess_*');
        $eliminados = 0;
        
        foreach ($archivos as $archivo) {
            if (time() - filemtime($archivo) > 1800) { // 30 minutos
                unlink($archivo);
                $eliminados++;
            }
        }
        
        echo "🗑️  Sesiones expiradas eliminadas: $eliminados\n";
    }
    
    private static function generarReporteDiario() {
        $sorteoModel = new Sorteo();
        $balotaModel = new Balota();
        
        $sorteoActivo = $sorteoModel->obtenerSorteoActivo();
        
        if ($sorteoActivo) {
            $participantes = $sorteoModel->obtenerParticipantesSorteo($sorteoActivo['id']);
            $balotas = $balotaModel->obtenerResumenBalotas($sorteoActivo['id']);
            
            $reporte = [
                'fecha' => date('Y-m-d'),
                'sorteo' => $sorteoActivo['descripcion'],
                'total_participantes' => count($participantes),
                'balotas_elegidas' => count($balotas),
                'porcentaje_completado' => (count($balotas) / 800) * 100
            ];
            
            $archivo = __DIR__ . '/../reports/reporte_diario_' . date('Y-m-d') . '.json';
            $directorio = dirname($archivo);
            
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            file_put_contents($archivo, json_encode($reporte, JSON_PRETTY_PRINT));
            
            echo "📊 Reporte diario generado\n";
        }
    }
}
