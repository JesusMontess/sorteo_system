<?php

// ==================================================================
// SCRIPTS/MIGRATION.PHP
// ==================================================================
class Migration {
    
    public static function ejecutarMigraciones() {
        $pdo = DatabaseConfig::getConnection();
        
        // Crear tabla de migraciones si no existe
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migraciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                ejecutada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_nombre (nombre)
            )
        ");
        
        $migraciones = [
            '001_agregar_indices' => self::migration001(),
            '002_agregar_auditoria' => self::migration002(),
            '003_mejorar_seguridad' => self::migration003()
        ];
        
        foreach ($migraciones as $nombre => $sql) {
            if (!self::migrationEjecutada($pdo, $nombre)) {
                try {
                    $pdo->exec($sql);
                    self::marcarMigrationEjecutada($pdo, $nombre);
                    echo "✅ Migración ejecutada: $nombre\n";
                } catch (Exception $e) {
                    echo "❌ Error en migración $nombre: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    private static function migrationEjecutada($pdo, $nombre) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM migraciones WHERE nombre = ?");
        $stmt->execute([$nombre]);
        return $stmt->fetchColumn() > 0;
    }
    
    private static function marcarMigrationEjecutada($pdo, $nombre) {
        $stmt = $pdo->prepare("INSERT INTO migraciones (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
    }
    
    private static function migration001() {
        return "
            -- Agregar índices para mejor rendimiento
            ALTER TABLE balota_concursante ADD INDEX idx_fecha_eleccion (fecha_eleccion);
            ALTER TABLE empleados ADD INDEX idx_estado (estado_emplado);
            ALTER TABLE apertura_sorteo ADD INDEX idx_fechas (fecha_inicio_sorteo, fecha_cierre_sorteo);
        ";
    }
    
    private static function migration002() {
        return "
            -- Agregar tabla de auditoría
            CREATE TABLE IF NOT EXISTS auditoria (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tabla VARCHAR(50) NOT NULL,
                operacion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
                usuario_id INT,
                datos_anteriores JSON,
                datos_nuevos JSON,
                ip_address VARCHAR(45),
                fecha_operacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tabla_operacion (tabla, operacion),
                INDEX idx_fecha (fecha_operacion)
            );
        ";
    }
    
    private static function migration003() {
        return "
            -- Agregar campos de seguridad a usuarios
            ALTER TABLE usuario_concurso ADD COLUMN intentos_fallidos INT DEFAULT 0;
            ALTER TABLE usuario_concurso ADD COLUMN bloqueado_hasta TIMESTAMP NULL;
            ALTER TABLE usuario_concurso ADD COLUMN ultimo_acceso TIMESTAMP NULL;
            
            ALTER TABLE usuario_moderador ADD COLUMN intentos_fallidos INT DEFAULT 0;
            ALTER TABLE usuario_moderador ADD COLUMN bloqueado_hasta TIMESTAMP NULL;
            ALTER TABLE usuario_moderador ADD COLUMN ultimo_acceso TIMESTAMP NULL;
        ";
    }
}
