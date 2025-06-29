<?php
// ==================================================================
// CONTROLLERS/SORTEOCONTROLLER.PHP
// ==================================================================
class SorteoController {
    private $sorteoModel;
    private $balotaModel;
    private $usuarioModel;
    
    public function __construct() {
        $this->sorteoModel = new Sorteo();
        $this->balotaModel = new Balota();
        $this->usuarioModel = new Usuario();
        $this->verificarSesion();
    }
    
    private function verificarSesion() {
        session_start();
        if (!isset($_SESSION['logged_in']) || $_SESSION['usuario']['tipo_usuario'] !== 'concursante') {
            header('Location: ' . BASE_URL);
            exit;
        }
    }
    
    public function dashboard() {
        $usuario = $_SESSION['usuario'];
        $sorteoActivo = $this->sorteoModel->obtenerSorteoActivo();
        
        $balotas = [];
        $puedeElegir = false;
        $empleadoSort = null;
        
        if ($sorteoActivo) {
            // Obtener información del empleado en el sorteo
            $empleadoSort = $this->usuarioModel->verificarParticipacionSorteo(
                $usuario['id_empleado'], 
                $sorteoActivo['id']
            );
            
            if ($empleadoSort) {
                $balotas = $this->balotaModel->obtenerBalotasUsuario($empleadoSort['id']);
                $puedeElegir = $this->balotaModel->puedeElegirBalota($empleadoSort['id']);
            }
        }
        
        include 'views/sorteo/dashboard.php';
    }
    
    public function elegirBalota() {
        $sorteoActivo = $this->sorteoModel->obtenerSorteoActivo();
        
        if (!$sorteoActivo) {
            header('Location: ' . BASE_URL . 'sorteo/dashboard?error=no_sorteo');
            exit;
        }
        
        $usuario = $_SESSION['usuario'];
        $empleadoSort = $this->usuarioModel->verificarParticipacionSorteo(
            $usuario['id_empleado'], 
            $sorteoActivo['id']
        );
        
        if (!$empleadoSort) {
            header('Location: ' . BASE_URL . 'sorteo/dashboard?error=no_participacion');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
            
            try {
                if ($accion === 'elegir') {
                    $numeroBalota = filter_input(INPUT_POST, 'numero_balota', FILTER_SANITIZE_STRING);
                    $this->balotaModel->elegirBalota($empleadoSort['id'], $numeroBalota);
                    $success = "Balota $numeroBalota elegida exitosamente";
                    
                } elseif ($accion === 'aleatoria') {
                    $this->balotaModel->elegirBalotaAleatoria($empleadoSort['id'], $sorteoActivo['id']);
                    $success = "Balota aleatoria asignada exitosamente";
                }
                
                header('Location: ' . BASE_URL . 'sorteo/dashboard?success=' . urlencode($success));
                exit;
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        $balotasDisponibles = $this->balotaModel->obtenerBalotasDisponibles($sorteoActivo['id']);
        $puedeElegir = $this->balotaModel->puedeElegirBalota($empleadoSort['id']);
        
        include 'views/sorteo/elegir_balota.php';
    }
    
    public function listado() {
        $sorteoActivo = $this->sorteoModel->obtenerSorteoActivo();
        $balotas = [];
        
        if ($sorteoActivo) {
            $balotas = $this->balotaModel->obtenerResumenBalotas($sorteoActivo['id']);
        }
        
        include 'views/sorteo/listado.php';
    }
    
    public function verificarBalota() {
        header('Content-Type: application/json');
        
        $numeroBalota = filter_input(INPUT_GET, 'numero', FILTER_SANITIZE_STRING);
        $sorteoActivo = $this->sorteoModel->obtenerSorteoActivo();
        
        if (!$sorteoActivo || !$numeroBalota) {
            echo json_encode(['disponible' => false]);
            return;
        }
        
        $disponible = $this->balotaModel->verificarBalotaDisponible($numeroBalota, $sorteoActivo['id']);
        echo json_encode(['disponible' => $disponible]);
    }
}
