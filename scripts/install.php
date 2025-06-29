<?php
// ==================================================================
// SCRIPTS/INSTALL.PHP
// ==================================================================
class Installer {
    
    public static function ejecutar() {
        echo "=== INSTALADOR DEL SISTEMA DE SORTEO ===\n\n";
        
        try {
            self::verificarRequisitos();
            self::crearDirectorios();
            self::configurarBaseDatos();
            self::crearUsuarioAdmin();
            self::configurarPermisos();
            
            echo "✅ Instalación completada exitosamente!\n";
            echo "Accede al sistema en: " . BASE_URL . "\n";
            
        } catch (Exception $e) {
            echo "❌ Error durante la instalación: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    private static function verificarRequisitos() {
        echo "Verificando requisitos del sistema...\n";
        
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            throw new Exception('Se requiere PHP 7.4 o superior');
        }
        
        $extensiones = ['pdo', 'pdo_mysql', 'session', 'json'];
        foreach ($extensiones as $ext) {
            if (!extension_loaded($ext)) {
                throw new Exception("Extensión PHP requerida no encontrada: $ext");
            }
        }
        
        echo "✅ Requisitos verificados\n";
    }
    
    private static function crearDirectorios() {
        echo "Creando directorios necesarios...\n";
        
        $directorios = [
            __DIR__ . '/../logs',
            __DIR__ . '/../uploads',
            __DIR__ . '/../temp',
            __DIR__ . '/../backups'
        ];
        
        foreach ($directorios as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "📁 Creado: $dir\n";
            }
        }
    }
    
    private static function configurarBaseDatos() {
        echo "Configurando base de datos...\n";
        
        try {
            $pdo = DatabaseConfig::getConnection();
            
            // Verificar si las tablas ya existen
            $stmt = $pdo->query("SHOW TABLES LIKE 'empleados'");
            if ($stmt->rowCount() > 0) {
                echo "⚠️  Las tablas ya existen\n";
                return;
            }
            
            // Ejecutar script SQL
            $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
            $pdo->exec($sql);
            
            echo "✅ Base de datos configurada\n";
            
        } catch (Exception $e) {
            throw new Exception("Error configurando BD: " . $e->getMessage());
        }
    }
    
    private static function crearUsuarioAdmin() {
        echo "Creando usuario administrador...\n";
        
        $empleadoModel = new Empleado();
        
        // Crear empleado admin si no existe
        try {
            $empleadoModel->agregarEmpleado(
                'CC',
                '12345678',
                'Administrador del Sistema',
                'ADMINISTRADOR',
                'SISTEMAS'
            );
            
            // Crear usuario moderador
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("INSERT INTO usuario_moderador (id_empleado, clave, nivel_permiso) VALUES (LAST_INSERT_ID(), ?, 1)");
            $stmt->execute(['admin123']);
            
            echo "✅ Usuario admin creado (12345678 / admin123)\n";
            
        } catch (Exception $e) {
            echo "⚠️  Usuario admin ya existe o error: " . $e->getMessage() . "\n";
        }
    }
    
    private static function configurarPermisos() {
        echo "Configurando permisos de archivos...\n";
        
        $archivos = [
            __DIR__ . '/../logs' => 0755,
            __DIR__ . '/../uploads' => 0755,
            __DIR__ . '/../temp' => 0755,
            __DIR__ . '/../config' => 0644
        ];
        
        foreach ($archivos as $archivo => $permiso) {
            if (file_exists($archivo)) {
                chmod($archivo, $permiso);
                echo "🔒 Permisos configurados: $archivo\n";
            }
        }
    }
}