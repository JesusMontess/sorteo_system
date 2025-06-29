<?php
$pageTitle = "Iniciar Sesión";
include VIEWS_PATH . '/layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </h4>
                <small>Sistema de Sorteos CMAICAO</small>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?action=login">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="numero_documento" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Número de Documento
                        </label>
                        <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                               placeholder="Ingresa tu cédula" 
                               pattern="[0-9]{6,15}"
                               value="<?php echo isset($_POST['numero_documento']) ? htmlspecialchars($_POST['numero_documento']) : ''; ?>" 
                               required>
                        <div class="form-text">Ingresa tu número de cédula sin puntos ni espacios</div>
                    </div>

                    <div class="mb-3">
                        <label for="clave" class="form-label">
                            <i class="fas fa-lock me-1"></i>Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="clave" name="clave" 
                                   placeholder="Ingresa tu contraseña"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Recordarme
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </button>
                    </div>
                </form>

                <hr>
                
                <div class="text-center">
                    <p class="mb-2"><i class="fas fa-info-circle text-info me-1"></i> <strong>Tipos de Usuario</strong></p>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-user text-primary"></i> <strong>Concursante</strong><br>
                                Participa en sorteos
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="fas fa-user-shield text-warning"></i> <strong>Moderador</strong><br>
                                Administra el sistema
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        ¿Problemas para acceder? Contacta al administrador del sistema
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Información del sistema -->
        <div class="card mt-3">
            <div class="card-body text-center">
                <h6 class="card-title">
                    <i class="fas fa-building me-2"></i>CMAICAO
                </h6>
                <p class="card-text small text-muted">
                    Sistema de Sorteos Interno<br>
                    Versión 1.0
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('clave');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Validar solo números en el campo de documento
document.getElementById('numero_documento').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php include VIEWS_PATH . '/layout/footer.php'; ?>