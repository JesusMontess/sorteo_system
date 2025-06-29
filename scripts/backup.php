<?php
// ==================================================================
// SCRIPTS/BACKUP.PHP
// ==================================================================
class BackupManager {
    
    public static function crearBackup() {
        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "backup_sorteo_{$fecha}.sql";
        $rutaBackup = __DIR__ . "/../backups/{$nombreArchivo}";
        
        try {
            $pdo = DatabaseConfig::getConnection();
            
            // Obtener lista de tablas
            $tablas = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tablas[] = $row[0];
            }
            
            $contenido = "-- Backup de Base de Datos - " . date('Y-m-d H:i:s') . "\n";
            $contenido .= "-- Sistema de Sorteo - Clínica Maicao\n\n";
            $contenido .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            foreach ($tablas as $tabla) {
                $contenido .= self::exportarTabla($pdo, $tabla);
            }
            
            $contenido .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            file_put_contents($rutaBackup, $contenido);
            
            Logger::info("Backup creado exitosamente", ['archivo' => $nombreArchivo]);
            
            return $rutaBackup;
            
        } catch (Exception $e) {
            Logger::error("Error creando backup: " . $e->getMessage());
            throw $e;
        }
    }
    
    private static function exportarTabla($pdo, $tabla) {
        $contenido = "-- Estructura de tabla para `{$tabla}`\n";
        $contenido .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
        
        // Obtener estructura
        $stmt = $pdo->query("SHOW CREATE TABLE `{$tabla}`");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $contenido .= $row[1] . ";\n\n";
        
        // Obtener datos
        $stmt = $pdo->query("SELECT * FROM `{$tabla}`");
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($filas)) {
            $contenido .= "-- Datos de la tabla `{$tabla}`\n";
            $contenido .= "INSERT INTO `{$tabla}` VALUES\n";
            
            $valores = [];
            foreach ($filas as $fila) {
                $fila = array_map(function($valor) use ($pdo) {
                    return $valor === null ? 'NULL' : $pdo->quote($valor);
                }, $fila);
                $valores[] = '(' . implode(',', $fila) . ')';
            }
            
            $contenido .= implode(",\n", $valores) . ";\n\n";
        }
        
        return $contenido;
    }
    
    public static function restaurarBackup($rutaArchivo) {
        if (!file_exists($rutaArchivo)) {
            throw new Exception("Archivo de backup no encontrado");
        }
        
        try {
            $pdo = DatabaseConfig::getConnection();
            $sql = file_get_contents($rutaArchivo);
            
            $pdo->exec($sql);
            
            Logger::info("Backup restaurado exitosamente", ['archivo' => basename($rutaArchivo)]);
            
        } catch (Exception $e) {
            Logger::error("Error restaurando backup: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function limpiarBackupsAntiguos($dias = 30) {
        $directorioBackups = __DIR__ . '/../backups';
        $archivos = glob($directorioBackups . '/backup_sorteo_*.sql');
        
        $eliminados = 0;
        foreach ($archivos as $archivo) {
            if (time() - filemtime($archivo) > ($dias * 24 * 60 * 60)) {
                unlink($archivo);
                $eliminados++;
            }
        }
        
        Logger::info("Backups antiguos eliminados", ['cantidad' => $eliminados]);
        return $eliminados;
    }
}
