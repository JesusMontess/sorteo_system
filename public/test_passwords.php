<?php
// Script temporal para verificar y corregir contraseñas
// Ejecutar este archivo una sola vez para corregir las contraseñas

// Configuración de base de datos
$host = 'localhost';
$dbname = 'sorteo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Script de Corrección de Contraseñas</h2>";
    
    // Generar hashes correctos
    $password_123456 = password_hash('123456', PASSWORD_DEFAULT);
    $password_admin123 = password_hash('admin123', PASSWORD_DEFAULT);
    
    echo "<p><strong>Hash para '123456':</strong> $password_123456</p>";
    echo "<p><strong>Hash para 'admin123':</strong> $password_admin123</p>";
    
    // Actualizar contraseñas de usuarios concursantes
    $stmt = $pdo->prepare("UPDATE usuario_concurso SET clave = ?");
    $stmt->execute([$password_123456]);
    echo "<p>✅ Actualizadas " . $stmt->rowCount() . " contraseñas de concursantes (nueva contraseña: <strong>123456</strong>)</p>";
    
    // Actualizar contraseñas de usuarios moderadores
    $stmt = $pdo->prepare("UPDATE usuario_moderador SET clave = ?");
    $stmt->execute([$password_admin123]);
    echo "<p>✅ Actualizadas " . $stmt->rowCount() . " contraseñas de moderadores (nueva contraseña: <strong>admin123</strong>)</p>";
    
    echo "<hr>";
    echo "<h3>📋 Credenciales Actualizadas:</h3>";
    
    // Mostrar concursantes
    echo "<h4>👤 Concursantes (contraseña: 123456):</h4>";
    $stmt = $pdo->query("
        SELECT DISTINCT e.numero_documento, e.nombre_completo, e.cargo
        FROM empleados e
        JOIN empleados_en_sorteo es ON e.id = es.id_empleado
        JOIN usuario_concurso uc ON es.id = uc.id_empleado_sort
        WHERE uc.estado = 1
    ");
    while ($row = $stmt->fetch()) {
        echo "<li>📄 <strong>{$row['numero_documento']}</strong> - {$row['nombre_completo']} ({$row['cargo']})</li>";
    }
    
    // Mostrar moderadores  
    echo "<h4>🛡️ Moderadores (contraseña: admin123):</h4>";
    $stmt = $pdo->query("
        SELECT e.numero_documento, e.nombre_completo, e.cargo, um.nivel_permiso
        FROM empleados e
        JOIN usuario_moderador um ON e.id = um.id_empleado
        WHERE um.estado = 1
    ");
    while ($row = $stmt->fetch()) {
        echo "<li>👑 <strong>{$row['numero_documento']}</strong> - {$row['nombre_completo']} ({$row['cargo']}) - Nivel: {$row['nivel_permiso']}</li>";
    }
    
    echo "<hr>";
    echo "<h3>🧪 Prueba de Autenticación:</h3>";
    
    // Función de prueba
    function testAuth($pdo, $documento, $clave) {
        // Buscar usuario concursante
        $stmt = $pdo->prepare("
            SELECT e.nombre_completo, uc.clave, 'concursante' as tipo
            FROM empleados e
            JOIN empleados_en_sorteo es ON e.id = es.id_empleado
            JOIN usuario_concurso uc ON es.id = uc.id_empleado_sort
            WHERE e.numero_documento = ? AND uc.estado = 1
            LIMIT 1
        ");
        $stmt->execute([$documento]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Buscar moderador
            $stmt = $pdo->prepare("
                SELECT e.nombre_completo, um.clave, 'moderador' as tipo
                FROM empleados e
                JOIN usuario_moderador um ON e.id = um.id_empleado
                WHERE e.numero_documento = ? AND um.estado = 1
            ");
            $stmt->execute([$documento]);
            $user = $stmt->fetch();
        }
        
        if ($user) {
            $resultado = password_verify($clave, $user['clave']) ? "✅ CORRECTO" : "❌ INCORRECTO";
            echo "<p>🔍 <strong>$documento</strong> ({$user['nombre_completo']}) - {$user['tipo']}: $resultado</p>";
        } else {
            echo "<p>❌ <strong>$documento</strong>: Usuario no encontrado</p>";
        }
    }
    
    // Probar algunas cédulas
    testAuth($pdo, '12345678', '123456');
    testAuth($pdo, '87654321', '123456');
    testAuth($pdo, '22222222', '123456');
    testAuth($pdo, '11111111', 'admin123');
    testAuth($pdo, '33333333', 'admin123');
    
    echo "<hr>";
    echo "<p><strong>🎯 Ahora puedes probar el login en:</strong></p>";
    echo "<p><a href='index.php?action=login' target='_blank'>http://localhost/sorteo_system/public/index.php?action=login</a></p>";
    
    echo "<hr>";
    echo "<p><small>⚠️ <strong>Importante:</strong> Elimina este archivo después de usarlo por seguridad.</small></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>MySQL esté funcionando</li>";
    echo "<li>La base de datos 'bdd_sorteo_cmaicao' exista</li>";
    echo "<li>Las credenciales de conexión sean correctas</li>";
    echo "</ul>";
}
?>