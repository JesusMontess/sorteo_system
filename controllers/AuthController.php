<?php
// ==================================================================
// CONTROLLERS/AUTHCONTROLLER.PHP
// ==================================================================
class AuthController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numeroDocumento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
            $clave = filter_input(INPUT_POST, 'clave', FILTER_SANITIZE_STRING);
            
            if (empty($numeroDocumento) || empty($clave)) {
                $error = "Todos los campos son obligatorios";
                include 'views/auth/login.php';
                return;
            }
            
            try {
                $usuario = $this->usuarioModel->autenticar($numeroDocumento, $clave);
                
                if ($usuario) {
                    session_start();
                    $_SESSION['usuario'] = $usuario;
                    $_SESSION['logged_in'] = true;
                    
                    // Redirigir según el tipo de usuario
                    if ($usuario['tipo_usuario'] === 'moderador') {
                        header('Location: ' . BASE_URL . 'admin/dashboard');
                    } else {
                        header('Location: ' . BASE_URL . 'sorteo/dashboard');
                    }
                    exit;
                } else {
                    $error = "Credenciales incorrectas";
                }
                
            } catch (Exception $e) {
                $error = "Error del sistema: " . $e->getMessage();
            }
        }
        
        include 'views/auth/login.php';
    }
    
    public function logout() {
        session_start();
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }
    
    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Implementar registro de nuevos usuarios
        }
        
        include 'views/auth/registro.php';
    }
}