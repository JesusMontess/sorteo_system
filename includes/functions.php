<?php
/**
 * Funciones auxiliares adaptadas para el sistema de sorteos CMAICAO - CORREGIDAS
 */

/**
 * Sanitizar entrada de datos
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar número de documento
 */
function validateDocumento($documento) {
    return preg_match('/^[0-9]{6,15}$/', $documento);
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redireccionar con mensaje
 */
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Obtener mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if ($date instanceof DateTime) {
        return $date->format($format);
    }
    return date($format, strtotime($date));
}

/**
 * Obtener sorteos activos
 */
function getSorteosActivos() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM apertura_sorteo 
            WHERE estado = 1 
            AND fecha_inicio_sorteo <= CURDATE() 
            AND fecha_cierre_sorteo >= CURDATE()
            ORDER BY fecha_inicio_sorteo ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error obteniendo sorteos activos: " . $e->getMessage());
        return [];
    }
}

/**
 * Verificar si empleado ya está en sorteo
 */
function isEmpleadoInSorteo($empleado_id, $sorteo_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM empleados_en_sorteo 
            WHERE id_empleado = ? AND id_sorteo = ? AND estado = 1
        ");
        $stmt->execute([$empleado_id, $sorteo_id]);
        return $stmt->fetchColumn() > 0;
        
    } catch (PDOException $e) {
        error_log("Error verificando empleado en sorteo: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener empleado en sorteo
 */
function getEmpleadoEnSorteo($empleado_id, $sorteo_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT es.*, e.nombre_completo, e.numero_documento
            FROM empleados_en_sorteo es
            JOIN empleados e ON es.id_empleado = e.id
            WHERE es.id_empleado = ? AND es.id_sorteo = ? AND es.estado = 1
        ");
        $stmt->execute([$empleado_id, $sorteo_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error obteniendo empleado en sorteo: " . $e->getMessage());
        return false;
    }
}

/**
 * Verificar si balota ya fue elegida en el sorteo
 */
function isBallotTaken($numero_balota, $sorteo_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM balota_concursante bc
            JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
            WHERE bc.numero_balota = ? AND es.id_sorteo = ?
        ");
        $stmt->execute([$numero_balota, $sorteo_id]);
        return $stmt->fetchColumn() > 0;
        
    } catch (PDOException $e) {
        error_log("Error verificando balota: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener balotas disponibles para un sorteo
 */
function getBallotasDisponibles($sorteo_id, $limit = 100) {
    global $pdo;
    
    try {
        // Obtener todas las balotas que NO han sido elegidas en este sorteo
        $stmt = $pdo->prepare("
            SELECT b.numero_balota 
            FROM balotas b
            WHERE b.numero_balota NOT IN (
                SELECT bc.numero_balota 
                FROM balota_concursante bc
                JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                WHERE es.id_sorteo = ?
            )
            ORDER BY CAST(b.numero_balota AS UNSIGNED)
            LIMIT ?
        ");
        $stmt->execute([$sorteo_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo balotas disponibles: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener balotas elegidas por un empleado en un sorteo
 */
function getBallotasEmpleado($empleado_sort_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT bc.*, b.equivalencia_binaria
            FROM balota_concursante bc
            JOIN balotas b ON bc.numero_balota = b.numero_balota
            WHERE bc.id_empleado_sort = ?
            ORDER BY bc.fecha_eleccion DESC
        ");
        $stmt->execute([$empleado_sort_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error obteniendo balotas del empleado: " . $e->getMessage());
        return [];
    }
}

/**
 * Registrar elección de balota
 */
function registrarEleccionBalota($empleado_sort_id, $numero_balota) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Verificar que la balota existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM balotas WHERE numero_balota = ?");
        $stmt->execute([$numero_balota]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'La balota no existe'];
        }
        
        // Verificar que el empleado aún tiene elecciones disponibles
        $stmt = $pdo->prepare("
            SELECT es.cantidad_elecciones,
                   (SELECT COUNT(*) FROM balota_concursante WHERE id_empleado_sort = ?) as usadas
            FROM empleados_en_sorteo es
            WHERE es.id = ?
        ");
        $stmt->execute([$empleado_sort_id, $empleado_sort_id]);
        $info = $stmt->fetch();
        
        if (!$info) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Empleado no encontrado en sorteo'];
        }
        
        if ($info['usadas'] >= $info['cantidad_elecciones']) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Ya agotaste todas tus elecciones'];
        }
        
        // Verificar que la balota no haya sido elegida ya por este empleado
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM balota_concursante 
            WHERE id_empleado_sort = ? AND numero_balota = ?
        ");
        $stmt->execute([$empleado_sort_id, $numero_balota]);
        if ($stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Ya elegiste esta balota'];
        }
        
        // Verificar que la balota no esté tomada por otro empleado en el mismo sorteo
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM balota_concursante bc
            JOIN empleados_en_sorteo es1 ON bc.id_empleado_sort = es1.id
            JOIN empleados_en_sorteo es2 ON es1.id_sorteo = es2.id_sorteo
            WHERE bc.numero_balota = ? AND es2.id = ? AND bc.id_empleado_sort != ?
        ");
        $stmt->execute([$numero_balota, $empleado_sort_id, $empleado_sort_id]);
        if ($stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Esta balota ya fue elegida por otro participante'];
        }
        
        // Registrar la elección
        $stmt = $pdo->prepare("
            INSERT INTO balota_concursante (id_empleado_sort, numero_balota, fecha_eleccion)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$empleado_sort_id, $numero_balota]);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Balota elegida exitosamente'];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error registrando elección: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}

/**
 * Autenticar usuario (CORREGIDA para trabajar con tu estructura)
 */
function authenticateUser($numero_documento, $clave) {
    global $pdo;
    
    try {
        // Primero buscar en usuarios concursantes
        $stmt = $pdo->prepare("
            SELECT 
                e.id as empleado_id,
                e.numero_documento,
                e.nombre_completo,
                e.cargo,
                e.area,
                uc.clave,
                'concursante' as tipo_usuario,
                uc.id as user_id,
                es.id as empleado_sorteo_id
            FROM empleados e
            JOIN empleados_en_sorteo es ON e.id = es.id_empleado
            JOIN usuario_concurso uc ON es.id = uc.id_empleado_sort
            WHERE e.numero_documento = ? AND uc.estado = 1 AND e.estado_emplado = 1
            LIMIT 1
        ");
        $stmt->execute([$numero_documento]);
        $user = $stmt->fetch();
        
        // Si no se encuentra como concursante, buscar como moderador
        if (!$user) {
            $stmt = $pdo->prepare("
                SELECT 
                    e.id as empleado_id,
                    e.numero_documento,
                    e.nombre_completo,
                    e.cargo,
                    e.area,
                    um.clave,
                    'moderador' as tipo_usuario,
                    um.nivel_permiso,
                    um.id as user_id
                FROM empleados e
                JOIN usuario_moderador um ON e.id = um.id_empleado
                WHERE e.numero_documento = ? AND um.estado = 1 AND e.estado_emplado = 1
            ");
            $stmt->execute([$numero_documento]);
            $user = $stmt->fetch();
        }
        
        if ($user) {
            // Para debugging: log lo que estamos comparando
            error_log("Autenticación - Usuario encontrado: " . $user['nombre_completo']);
            error_log("Autenticación - Clave ingresada: " . $clave);
            error_log("Autenticación - Hash en BD: " . $user['clave']);
            
            // Verificar contraseña
            if (password_verify($clave, $user['clave'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['empleado_id'] = $user['empleado_id'];
                $_SESSION['numero_documento'] = $user['numero_documento'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                $_SESSION['cargo'] = $user['cargo'];
                $_SESSION['area'] = $user['area'];
                $_SESSION['user_role'] = $user['tipo_usuario'];
                
                if ($user['tipo_usuario'] === 'moderador') {
                    $_SESSION['nivel_permiso'] = $user['nivel_permiso'];
                }
                
                return true;
            } else {
                error_log("Autenticación - Contraseña no coincide");
            }
        } else {
            error_log("Autenticación - Usuario no encontrado para cédula: " . $numero_documento);
        }
        
    } catch (PDOException $e) {
        error_log("Error en autenticación: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Verificar si usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar si es moderador
 */
function isModerador() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'moderador';
}

/**
 * Verificar nivel de permiso del moderador
 */
function hasPermissionLevel($required_level) {
    return isModerador() && 
           isset($_SESSION['nivel_permiso']) && 
           $_SESSION['nivel_permiso'] >= $required_level;
}

/**
 * Obtener estadísticas del sistema
 */
function getEstadisticasSistema() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total empleados activos
        $stmt = $pdo->query("SELECT COUNT(*) FROM empleados WHERE estado_emplado = 1");
        $stats['total_empleados'] = $stmt->fetchColumn();
        
        // Total sorteos activos
        $stmt = $pdo->query("SELECT COUNT(*) FROM apertura_sorteo WHERE estado = 1");
        $stats['sorteos_activos'] = $stmt->fetchColumn();
        
        // Total participaciones
        $stmt = $pdo->query("SELECT COUNT(*) FROM balota_concursante");
        $stats['total_participaciones'] = $stmt->fetchColumn();
        
        // Total balotas disponibles
        $stmt = $pdo->query("SELECT COUNT(*) FROM balotas");
        $stats['total_balotas'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        return [
            'total_empleados' => 0,
            'sorteos_activos' => 0,
            'total_participaciones' => 0,
            'total_balotas' => 0
        ];
    }
}

/**
 * Obtener participaciones del empleado actual
 */
function getParticipacionesEmpleado($empleado_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                bc.*,
                aps.descripcion as sorteo_descripcion,
                aps.fecha_cierre_sorteo,
                es.cantidad_elecciones,
                (SELECT COUNT(*) FROM balota_concursante bc2 WHERE bc2.id_empleado_sort = bc.id_empleado_sort) as elecciones_usadas
            FROM balota_concursante bc
            JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
            JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
            WHERE es.id_empleado = ?
            ORDER BY bc.fecha_eleccion DESC
        ");
        $stmt->execute([$empleado_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error obteniendo participaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Logging del sistema
 */
function logActivity($empleado_id, $action, $details = '') {
    global $pdo;
    
    try {
        // Crear tabla de logs si no existe
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                empleado_id INT,
                accion VARCHAR(100) NOT NULL,
                detalles TEXT,
                ip_address VARCHAR(45),
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (empleado_id) REFERENCES empleados(id)
            )
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO system_logs (empleado_id, accion, detalles, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $empleado_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Función temporal para crear contraseñas hasheadas (solo para testing)
 */
function crearHashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?>