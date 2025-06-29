</main>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-building me-2"></i>CMAICAO</h5>
                    <p class="mb-0">Sistema de Sorteos Interno</p>
                    <small class="text-muted">Transparente, seguro y confiable</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> CMAICAO. 
                        Todos los derechos reservados.
                    </p>
                    <small class="text-muted">
                        Versión 1.0 | 
                        Sistema desarrollado para uso interno
                    </small>
                    <div class="mt-2">
                        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Conectado como: <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?>
                                <?php if (function_exists('isModerador') && isModerador()): ?>
                                    <span class="badge bg-warning text-dark ms-1">MODERADOR</span>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <!-- JavaScript personalizado -->
    <script>
        // Funciones globales del sistema
        
        // Mostrar toast notifications
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remover el toast después de que se oculte
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Crear contenedor de toasts
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }

        // Confirmar acciones peligrosas
        function confirmAction(message, callback) {
            if (confirm(message || '¿Estás seguro de realizar esta acción?')) {
                if (typeof callback === 'function') {
                    callback();
                } else if (typeof callback === 'string') {
                    window.location.href = callback;
                }
            }
        }

        // Formatear números
        function formatNumber(num) {
            return new Intl.NumberFormat('es-CO').format(num);
        }

        // Copiar al portapapeles
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showToast('Copiado al portapapeles', 'success');
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                showToast('Error al copiar', 'danger');
            });
        }

        // Auto-ocultar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    if (bootstrap.Alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Inicializar popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        // Validar formularios básico
        function validateForm(form) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }

        // Prevenir envío múltiple de formularios
        document.addEventListener('submit', function(e) {
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                setTimeout(function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                }, 100);
            }
        });
    </script>
    
    <!-- Scripts adicionales por página -->
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Script personalizado si existe -->
    <?php if (defined('ASSETS_PATH')): ?>
        <script src="<?php echo ASSETS_PATH; ?>/js/scripts.js"></script>
    <?php endif; ?>
</body>
</html>