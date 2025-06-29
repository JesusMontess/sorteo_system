<?php
$pageTitle = "Elegir Balota";
include VIEWS_PATH . '/layout/header.php';

// Obtener el sorteo
$sorteo_id = $_GET['sorteo'] ?? 0;

if (!$sorteo_id) {
    redirect('index.php?action=sorteos', 'Sorteo no especificado', 'error');
}

try {
    // Verificar que el empleado está autorizado para este sorteo
    $empleado_sorteo = getEmpleadoEnSorteo($_SESSION['empleado_id'], $sorteo_id);
    
    if (!$empleado_sorteo) {
        redirect('index.php?action=sorteos', 'No estás autorizado para participar en este sorteo', 'error');
    }
    
    // Obtener información del sorteo
    $stmt = $pdo->prepare("SELECT * FROM apertura_sorteo WHERE id = ? AND estado = 1");
    $stmt->execute([$sorteo_id]);
    $sorteo = $stmt->fetch();
    
    if (!$sorteo) {
        redirect('index.php?action=sorteos', 'Sorteo no encontrado o inactivo', 'error');
    }
    
    // Verificar que aún puede elegir balotas
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as usadas 
        FROM balota_concursante 
        WHERE id_empleado_sort = ?
    ");
    $stmt->execute([$empleado_sorteo['id']]);
    $elecciones_usadas = $stmt->fetchColumn();
    
    if ($elecciones_usadas >= $empleado_sorteo['cantidad_elecciones']) {
        redirect('index.php?action=dashboard', 'Ya agotaste todas tus elecciones para este sorteo', 'warning');
    }
    
    // Obtener balotas disponibles
    $balotas_disponibles = getBallotasDisponibles($sorteo_id, 100);
    
    // Obtener mis balotas ya elegidas para este sorteo
    $stmt = $pdo->prepare("
        SELECT numero_balota 
        FROM balota_concursante 
        WHERE id_empleado_sort = ?
    ");
    $stmt->execute([$empleado_sorteo['id']]);
    $mis_balotas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Procesar selección de balota
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_balota'])) {
        $numero_balota = str_pad($_POST['numero_balota'], 3, '0', STR_PAD_LEFT);
        
        $result = registrarEleccionBalota($empleado_sorteo['id'], $numero_balota);
        
        if ($result['success']) {
            logActivity($_SESSION['empleado_id'], 'BALOTA_ELEGIDA', "Balota {$numero_balota} en sorteo {$sorteo_id}");
            redirect('index.php?action=dashboard', $result['message'], 'success');
        } else {
            $error = $result['message'];
        }
    }
    
} catch (Exception $e) {
    error_log("Error en elegir balota: " . $e->getMessage());
    redirect('index.php?action=sorteos', 'Error interno del sistema', 'error');
}
?>

<div class="row">
    <div class="col-12">
        <!-- Información del sorteo -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-0">
                            <i class="fas fa-gift me-2"></i>
                            <?php echo htmlspecialchars($sorteo['descripcion']); ?>
                        </h4>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="index.php?action=dashboard" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-1">Mis Elecciones</h6>
                            <div class="display-6 text-primary"><?php echo $elecciones_usadas; ?> / <?php echo $empleado_sorteo['cantidad_elecciones']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-1">Disponibles</h6>
                            <div class="display-6 text-success"><?php echo $empleado_sorteo['cantidad_elecciones'] - $elecciones_usadas; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-1">Cierre</h6>
                            <div class="small text-warning">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($sorteo['fecha_cierre_sorteo']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-1">Estado</h6>
                            <span class="badge bg-success">Activo</span>
                        </div>
                    </div>
                </div>
                
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-primary" 
                         style="width: <?php echo ($elecciones_usadas / $empleado_sorteo['cantidad_elecciones']) * 100; ?>%">
                    </div>
                </div>
            </div>
        </div>

        <!-- Mis balotas elegidas -->
        <?php if (!empty($mis_balotas)): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-check me-2"></i>
                    Mis Balotas Elegidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($mis_balotas as $balota): ?>
                        <div class="balota selected">
                            <?php echo $balota; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Selección de balota -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="fas fa-hand-pointer me-2"></i>
                            Selecciona tu Balota
                        </h5>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button type="button" class="btn btn-primary btn-sm" onclick="generarNumeroAleatorio()">
                            <i class="fas fa-random me-1"></i>Número Aleatorio
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($balotas_disponibles)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-ban text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted">No hay balotas disponibles</h4>
                        <p class="text-muted">Todas las balotas han sido seleccionadas por otros participantes.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" id="balotaForm" onsubmit="return confirmarSeleccion()">
                        <input type="hidden" name="numero_balota" id="numero_balota_hidden">
                        
                        <div class="text-center mb-4">
                            <h6 class="text-muted">Haz clic en el número que deseas elegir:</h6>
                        </div>
                        
                        <div class="balotas-grid">
                            <?php foreach ($balotas_disponibles as $balota): ?>
                                <div class="balota" 
                                     data-numero="<?php echo $balota; ?>"
                                     onclick="selectBalota('<?php echo $balota; ?>', this)">
                                    <?php echo $balota; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" id="btnConfirmar" class="btn btn-primary btn-lg" disabled>
                                <i class="fas fa-check me-2"></i>
                                Confirmar Selección
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Selección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>¿Estás seguro de que quieres elegir la balota?</p>
                <div class="balota selected d-inline-flex" id="balotaConfirm" style="font-size: 2rem; width: 100px; height: 100px;">
                    ---
                </div>
                <p class="mt-3 text-muted">
                    <strong>¡Atención!</strong> Una vez confirmada, no podrás cambiar esta selección.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmarEleccion()">
                    <i class="fas fa-check me-1"></i>Sí, Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let balotaSeleccionada = null;

// Seleccionar balota
function selectBalota(numero, elemento) {
    // Remover selección anterior
    document.querySelectorAll('.balota').forEach(b => {
        if (!b.classList.contains('disabled')) {
            b.classList.remove('selected');
        }
    });
    
    // Agregar selección actual
    elemento.classList.add('selected');
    balotaSeleccionada = numero;
    
    // Habilitar botón
    const btnConfirmar = document.getElementById('btnConfirmar');
    btnConfirmar.disabled = false;
    btnConfirmar.innerHTML = '<i class="fas fa-check me-2"></i>Confirmar Balota ' + numero;
}

// Generar número aleatorio
function generarNumeroAleatorio() {
    const balotas = document.querySelectorAll('.balota:not(.disabled)');
    if (balotas.length === 0) {
        alert('No hay balotas disponibles');
        return;
    }
    
    const randomIndex = Math.floor(Math.random() * balotas.length);
    const randomBalota = balotas[randomIndex];
    
    selectBalota(randomBalota.getAttribute('data-numero'), randomBalota);
    
    // Efecto visual
    randomBalota.classList.add('pulse');
    setTimeout(() => {
        randomBalota.classList.remove('pulse');
    }, 2000);
}

// Confirmar selección
function confirmarSeleccion() {
    if (!balotaSeleccionada) {
        alert('Por favor selecciona una balota');
        return false;
    }
    
    document.getElementById('balotaConfirm').textContent = balotaSeleccionada;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
    
    return false; // Prevenir envío directo del formulario
}

// Confirmar elección final
function confirmarEleccion() {
    document.getElementById('numero_balota_hidden').value = balotaSeleccionada;
    document.getElementById('balotaForm').submit();
}

// Advertencia si el sorteo cierra pronto
<?php 
$dias_restantes = floor((strtotime($sorteo['fecha_cierre_sorteo']) - time()) / (60 * 60 * 24));
if ($dias_restantes <= 1): 
?>
window.addEventListener('load', function() {
    setTimeout(function() {
        showToast('¡Este sorteo cierra pronto! No olvides hacer tus elecciones.', 'warning');
    }, 1000);
});
<?php endif; ?>
</script>

<?php include VIEWS_PATH . '/layout/footer.php'; ?>