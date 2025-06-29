<?php
require_once '../config/constants.php';
// Iniciar sesión de forma segura
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de rutas (desde public/ hacia arriba)
define('ROOT_PATH', dirname(__DIR__)); // Subir un nivel desde public/
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('ASSETS_PATH', '/sorteo_system/assets'); // Ruta web para assets

// Verificar que los directorios existen
if (!is_dir(CONFIG_PATH)) {
    die('Error: No se encuentra la carpeta config/. Ruta buscada: ' . CONFIG_PATH);
}

if (!is_dir(INCLUDES_PATH)) {
    die('Error: No se encuentra la carpeta includes/. Ruta buscada: ' . INCLUDES_PATH);
}

if (!is_dir(VIEWS_PATH)) {
    die('Error: No se encuentra la carpeta views/. Ruta buscada: ' . VIEWS_PATH);
}

// Verificar archivos críticos
if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('Error: No se encuentra config/database.php. Ruta buscada: ' . CONFIG_PATH . '/database.php');
}

if (!file_exists(INCLUDES_PATH . '/functions.php')) {
    die('Error: No se encuentra includes/functions.php. Ruta buscada: ' . INCLUDES_PATH . '/functions.php');
}

// Incluir archivos necesarios
try {
    require_once CONFIG_PATH . '/database.php';
    require_once INCLUDES_PATH . '/functions.php';
} catch (Exception $e) {
    die('Error al cargar archivos de configuración: ' . $e->getMessage());
}

// Obtener la acción de la URL
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$page = isset($_GET['page']) ? $_GET['page'] : '';

// Router básico adaptado para tu sistema
switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar login con cédula
            $numero_documento = $_POST['numero_documento'] ?? '';
            $clave = $_POST['clave'] ?? '';
            
            if (validateDocumento($numero_documento) && authenticateUser($numero_documento, $clave)) {
                // Log del login
                logActivity($_SESSION['empleado_id'], 'LOGIN', 'Usuario ingresó al sistema');
                
                header('Location: index.php?action=dashboard');
                exit;
            } else {
                $error = "Número de documento o contraseña incorrectos";
            }
        }
        
        $loginFile = VIEWS_PATH . '/auth/login.php';
        if (file_exists($loginFile)) {
            include $loginFile;
        } else {
            echo '<h1>Error</h1><p>No se encuentra views/auth/login.php en: ' . $loginFile . '</p>';
        }
        break;
        
    case 'logout':
        if (isLoggedIn()) {
            logActivity($_SESSION['empleado_id'], 'LOGOUT', 'Usuario cerró sesión');
        }
        session_destroy();
        header('Location: index.php');
        exit;
        break;
        
    case 'dashboard':
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
        
        if (isModerador()) {
            $dashboardFile = VIEWS_PATH . '/admin/dashboard.php';
        } else {
            $dashboardFile = VIEWS_PATH . '/sorteo/dashboard.php';
        }
        
        if (file_exists($dashboardFile)) {
            include $dashboardFile;
        } else {
            echo '<h1>Error</h1><p>No se encuentra el archivo de dashboard: ' . $dashboardFile . '</p>';
        }
        break;
        
    case 'sorteos':
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $sorteoFile = VIEWS_PATH . '/sorteo/listado.php';
        if (file_exists($sorteoFile)) {
            include $sorteoFile;
        } else {
            echo '<h1>Error</h1><p>No se encuentra: ' . $sorteoFile . '</p>';
        }
        break;
        
    case 'participar':
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Solo concursantes pueden participar
        if (isModerador()) {
            redirect('index.php?action=dashboard', 'Los moderadores no pueden participar en sorteos', 'warning');
        }
        
        $participarFile = VIEWS_PATH . '/sorteo/elegir_balota.php';
        if (file_exists($participarFile)) {
            include $participarFile;
        } else {
            echo '<h1>Error</h1><p>No se encuentra: ' . $participarFile . '</p>';
        }
        break;
        
    case 'mis_balotas':
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $misBallotasFile = VIEWS_PATH . '/sorteo/mis_balotas.php';
        if (file_exists($misBallotasFile)) {
            include $misBallotasFile;
        } else {
            // Crear vista temporal si no existe
            include VIEWS_PATH . '/layout/header.php';
            echo '<div class="container mt-4">
                    <div class="alert alert-info">
                        <h4>Mis Balotas</h4>
                        <p>Esta funcionalidad está en desarrollo. Mientras tanto, puedes ver tus balotas en el dashboard.</p>
                        <a href="index.php?action=dashboard" class="btn btn-primary">Volver al Dashboard</a>
                    </div>
                  </div>';
            include VIEWS_PATH . '/layout/footer.php';
        }
        break;
        
    case 'admin':
        if (!isModerador()) {
            redirect('index.php?action=dashboard', 'No tienes permisos para acceder a esta sección', 'error');
        }
        
        switch ($page) {
            case 'empleados':
                $adminFile = VIEWS_PATH . '/admin/empleados.php';
                break;
            case 'sorteos':
                $adminFile = VIEWS_PATH . '/admin/sorteos.php';
                break;
            case 'reportes':
                $adminFile = VIEWS_PATH . '/admin/reportes.php';
                break;
            default:
                $adminFile = VIEWS_PATH . '/admin/dashboard.php';
        }
        
        if (file_exists($adminFile)) {
            include $adminFile;
        } else {
            // Vista temporal para admin
            include VIEWS_PATH . '/layout/header.php';
            echo '<div class="container mt-4">
                    <div class="alert alert-warning">
                        <h4>Panel Administrativo</h4>
                        <p>Archivo no encontrado: ' . htmlspecialchars($adminFile) . '</p>
                        <p>Esta sección está en desarrollo.</p>
                        <a href="index.php?action=dashboard" class="btn btn-primary">Volver al Dashboard</a>
                    </div>
                  </div>';
            include VIEWS_PATH . '/layout/footer.php';
        }
        break;
        
    case 'api':
        // Manejar peticiones AJAX
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $api_action = $input['action'] ?? '';
            
            switch ($api_action) {
                case 'elegir_balota':
                    if (!isLoggedIn()) {
                        echo json_encode(['success' => false, 'message' => 'No autorizado']);
                        exit;
                    }
                    
                    $sorteo_id = $input['sorteo_id'] ?? 0;
                    $numero_balota = $input['numero_balota'] ?? '';
                    
                    // Obtener el empleado_en_sorteo_id
                    $empleado_sorteo = getEmpleadoEnSorteo($_SESSION['empleado_id'], $sorteo_id);
                    if (!$empleado_sorteo) {
                        echo json_encode(['success' => false, 'message' => 'No estás autorizado para este sorteo']);
                        exit;
                    }
                    
                    $result = registrarEleccionBalota($empleado_sorteo['id'], $numero_balota);
                    echo json_encode($result);
                    break;
                    
                case 'get_balotas_disponibles':
                    if (!isLoggedIn()) {
                        echo json_encode(['success' => false, 'message' => 'No autorizado']);
                        exit;
                    }
                    
                    $sorteo_id = $input['sorteo_id'] ?? 0;
                    $balotas = getBallotasDisponibles($sorteo_id, 100);
                    echo json_encode(['success' => true, 'balotas' => $balotas]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        }
        exit;
        break;
        
    case 'ayuda':
        include VIEWS_PATH . '/layout/header.php';
        ?>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h4><i class="fas fa-question-circle me-2"></i>Ayuda del Sistema</h4>
                        </div>
                        <div class="card-body">
                            <h5>¿Cómo participar en un sorteo?</h5>
                            <ol>
                                <li>Ingresa al sistema con tu cédula y contraseña</li>
                                <li>Ve a "Ver Sorteos" o desde tu dashboard</li>
                                <li>Selecciona el sorteo en el que quieres participar</li>
                                <li>Elige tu número de balota favorito</li>
                                <li>Confirma tu selección</li>
                            </ol>
                            
                            <h5 class="mt-4">¿Cuántas balotas puedo elegir?</h5>
                            <p>Esto depende de la autorización que tengas para cada sorteo. Puedes ver cuántas elecciones tienes disponibles en tu dashboard.</p>
                            
                            <h5 class="mt-4">¿Puedo cambiar mi selección?</h5>
                            <p>No, una vez que confirmes una balota, no se puede cambiar. Asegúrate de elegir bien.</p>
                            
                            <h5 class="mt-4">¿Cuándo se cierran los sorteos?</h5>
                            <p>Cada sorteo tiene una fecha de cierre específica. Puedes verla en la información del sorteo.</p>
                            
                            <div class="alert alert-warning mt-4">
                                <strong>¿Necesitas más ayuda?</strong><br>
                                Contacta al administrador del sistema o al área de Recursos Humanos.
                            </div>
                            
                            <div class="text-center">
                                <a href="index.php?action=dashboard" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include VIEWS_PATH . '/layout/footer.php';
        break;
        
    default:
        // Página de inicio
        $homeFile = VIEWS_PATH . '/home.php';
        if (file_exists($homeFile)) {
            include $homeFile;
        } else {
            // Página básica de bienvenida adaptada para CMAICAO
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Sistema de Sorteos CMAICAO</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
                <link href="<?php echo ASSETS_PATH; ?>/css/styles.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white text-center">
                                    <h1><i class="fas fa-building me-2"></i>CMAICAO</h1>
                                    <h3>Sistema de Sorteos</h3>
                                </div>
                                <div class="card-body text-center">
                                    <h4>🎉 ¡El sistema está funcionando!</h4>
                                    <p class="lead">Bienvenido al sistema de sorteos interno de CMAICAO.</p>
                                    
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle me-2"></i>Estado del Sistema</h5>
                                        <ul class="list-unstyled">
                                            <li>✅ Configuración básica: OK</li>
                                            <li>✅ Base de datos: <?php echo isset($pdo) ? 'Conectada (bdd_sorteo_cmaicao)' : 'Error'; ?></li>
                                            <li>✅ Archivos principales: Cargados</li>
                                            <li>✅ Autenticación: Por cédula</li>
                                            <li>✅ Tipos de usuario: Concursante y Moderador</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="index.php?action=login" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                        </a>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <h6><i class="fas fa-user text-primary"></i> Concursante</h6>
                                            <small class="text-muted">
                                                Ingresa con tu cédula<br>
                                                y contraseña asignada<br>
                                                <strong>Ejemplo:</strong> 12345678
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <h6><i class="fas fa-user-shield text-warning"></i> Moderador</h6>
                                            <small class="text-muted">
                                                Administra sorteos<br>
                                                y empleados<br>
                                                <strong>Ejemplo:</strong> 11111111
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del sistema -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-cog me-2"></i>Información Técnica</h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">
                                        <strong>Base de Datos:</strong> bdd_sorteo_cmaicao<br>
                                        <strong>Acceso:</strong> http://localhost/sorteo_system/public/<br>
                                        <strong>Tablas principales:</strong><br>
                                        - empleados (<?php echo isset($pdo) ? '✅' : '❌'; ?>)<br>
                                        - apertura_sorteo (<?php echo isset($pdo) ? '✅' : '❌'; ?>)<br>
                                        - balotas (<?php echo isset($pdo) ? '✅' : '❌'; ?>)<br>
                                        - balota_concursante (<?php echo isset($pdo) ? '✅' : '❌'; ?>)<br>
                                        - usuario_concurso (<?php echo isset($pdo) ? '✅' : '❌'; ?>)<br>
                                        - usuario_moderador (<?php echo isset($pdo) ? '✅' : '❌'; ?>)
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Estadísticas básicas -->
                            <?php if (isset($pdo)): ?>
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-chart-bar me-2"></i>Estadísticas del Sistema</h6>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    try {
                                        $stats = getEstadisticasSistema();
                                        echo '<div class="row text-center">
                                                <div class="col-3">
                                                    <div class="h5 text-primary">' . $stats['total_empleados'] . '</div>
                                                    <small>Empleados</small>
                                                </div>
                                                <div class="col-3">
                                                    <div class="h5 text-success">' . $stats['sorteos_activos'] . '</div>
                                                    <small>Sorteos</small>
                                                </div>
                                                <div class="col-3">
                                                    <div class="h5 text-warning">' . $stats['total_participaciones'] . '</div>
                                                    <small>Participaciones</small>
                                                </div>
                                                <div class="col-3">
                                                    <div class="h5 text-info">' . $stats['total_balotas'] . '</div>
                                                    <small>Balotas</small>
                                                </div>
                                              </div>';
                                    } catch (Exception $e) {
                                        echo '<p class="text-muted">Error cargando estadísticas</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php
        }
        break;
}
?>