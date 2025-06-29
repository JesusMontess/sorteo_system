
<?php
// ==================================================================
// CONTROLLERS/ADMINCONTROLLER.PHP
// ==================================================================
class AdminController {
    private $sorteoModel;
    private $empleadoModel;
    private $balotaModel;
    
    public function __construct() {
        $this->sorteoModel = new Sorteo();
        $this->empleadoModel = new Empleado();
        $this->balotaModel = new Balota();
        $this->verificarSesion();
    }
    
    private function verificarSesion() {
        session_start();
        if (!isset($_SESSION['logged_in']) || $_SESSION['usuario']['tipo_usuario'] !== 'moderador') {
            header('Location: ' . BASE_URL);
            exit;
        }
    }
    
    public function dashboard() {
        $sorteos = $this->sorteoModel->obtenerTodosSorteos();
        $sorteoActivo = $this->sorteoModel->obtenerSorteoActivo();
        
        $estadisticas = [];
        if ($sorteoActivo) {
            $participantes = $this->sorteoModel->obtenerParticipantesSorteo($sorteoActivo['id']);
            $balotas = $this->balotaModel->obtenerResumenBalotas($sorteoActivo['id']);
            
            $estadisticas = [
                'total_participantes' => count($participantes),
                'total_balotas_elegidas' => count($balotas),
                'balotas_disponibles' => count($this->balotaModel->obtenerBalotasDisponibles($sorteoActivo['id']))
            ];
        }
        
        include 'views/admin/dashboard.php';
    }
    
    public function sorteos() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
            
            try {
                switch ($accion) {
                    case 'crear':
                        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
                        $fechaInicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
                        $fechaCierre = filter_input(INPUT_POST, 'fecha_cierre', FILTER_SANITIZE_STRING);
                        
                        $this->sorteoModel->crearSorteo($descripcion, $fechaInicio, $fechaCierre);
                        $success = "Sorteo creado exitosamente";
                        break;
                        
                    case 'cerrar':
                        $idSorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
                        $this->sorteoModel->cerrarSorteo($idSorteo);
                        $success = "Sorteo cerrado exitosamente";
                        break;
                        
                    case 'pausar':
                        $idSorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
                        $this->sorteoModel->pausarSorteo($idSorteo);
                        $success = "Sorteo pausado exitosamente";
                        break;
                        
                    case 'reanudar':
                        $idSorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
                        $this->sorteoModel->reanudarSorteo($idSorteo);
                        $success = "Sorteo reanudado exitosamente";
                        break;
                }
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        $sorteos = $this->sorteoModel->obtenerTodosSorteos();
        include 'views/admin/sorteos.php';
    }
    
    public function usuarios() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
            
            try {
                if ($accion === 'inscribir') {
                    $idSorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
                    $idEmpleado = filter_input(INPUT_POST, 'id_empleado', FILTER_VALIDATE_INT);
                    $cantidadElecciones = filter_input(INPUT_POST, 'cantidad_elecciones', FILTER_VALIDATE_INT) ?: 1;
                    
                    $this->empleadoModel->inscribirEnSorteo($idSorteo, $idEmpleado, $cantidadElecciones);
                    $success = "Empleado inscrito en el sorteo exitosamente";
                }
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        $empleados = $this->empleadoModel->obtenerTodos();
        $sorteoActivo = $this->sorteoModel->obtenerSorteoActivo();
        $participantes = [];
        
        if ($sorteoActivo) {
            $participantes = $this->sorteoModel->obtenerParticipantesSorteo($sorteoActivo['id']);
        }
        
        include 'views/admin/usuarios.php';
    }
}
