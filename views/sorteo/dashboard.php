<?php
$pageTitle = "Mi Dashboard";
include VIEWS_PATH . '/layout/header.php';

// Obtener estadísticas del empleado
$empleado_id = $_SESSION['empleado_id'];

try {
    // Mis participaciones
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM balota_concursante bc
        JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
        WHERE es.id_empleado = ?
    ");
    $stmt->execute([$empleado_id]);
    $mis_participaciones = $stmt->fetchColumn();

    // Sorteos disponibles para mí
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM empleados_en_sorteo es
        JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
        WHERE es.id_empleado = ? AND es.estado = 1 AND aps.estado = 1
        AND aps.fecha_cierre_sorteo >= CURDATE()
    ");
    $stmt->execute([$empleado_id]);
    $sorteos_disponibles = $stmt->fetchColumn();

    // Mis sorteos activos con participaciones
    $stmt = $pdo->prepare("
        SELECT 
            aps.*,
            es.cantidad_elecciones,
            es.id as empleado_sorteo_id,
            (SELECT COUNT(*) FROM balota_concursante bc WHERE bc.id_empleado_sort = es.id) as elecciones_usadas
        FROM empleados_en_sorteo es
        JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
        WHERE es.id_empleado = ? AND es.estado = 1 AND aps.estado = 1
        AND aps.fecha_cierre_sorteo >= CURDATE()
        ORDER BY aps.fecha_cierre_sorteo ASC
    ");
    $stmt->execute([$empleado_id]);
    $sorteos_activos = $stmt->fetchAll();

    // Mis balotas elegidas recientemente
    $stmt = $pdo->prepare("
        SELECT 
            bc.*,
            aps.descripcion as sorteo_descripcion,
            aps.fecha_cierre_sorteo
        FROM balota_concursante bc
        JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
        JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
        WHERE es.id_empleado = ?
        ORDER BY bc.fecha_eleccion DESC
        LIMIT 10
    ");
    $stmt->execute([$empleado_id]);
    $mis_balotas = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $mis_participaciones = 0;
    $sorteos_disponibles = 0;
    $sorteos_activos = [];
    $mis_balotas = [];
}
?>

<!-- Header del Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-1">
                            <i class="fas fa-user me-2"></i>
                            ¡Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>!
                        </h1>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-id-card me-1"></i>
                            Cédula: <?php echo htmlspecialchars($_SESSION['numero_documento']); ?> | 
                            <i class="fas fa-briefcase me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['cargo']); ?>
                            <?php if (!empty($_SESSION['area'])): ?>
                                - <?php echo htmlspecialchars($_SESSION['area']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="index.php?action=sorteos" class="btn btn-light">
                            <i class="fas fa-list me-2"></i>Ver Sorteos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Usuario -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card info">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="stats-number"><?php echo $mis_participaciones; ?></h3>
                <p class="stats-label">Balotas Elegidas</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="stats-number"><?php echo $sorteos_disponibles; ?></h3>
                <p class="stats-label">Sorteos Disponibles</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="stats-number">
                    <?php 
                    $elecciones_pendientes = 0;
                    foreach ($sorteos_activos as $sorteo) {
                        $elecciones_pendientes += ($sorteo['cantidad_elecciones'] - $sorteo['elecciones_usadas']);
                    }
                    echo $elecciones_pendientes;
                    ?>
                </h3>
                <p class="stats-label">Elecciones Pendientes</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 class="stats-number">
                    <?php 
                    $total_posibles = 0;
                    $total_usadas = 0;
                    foreach ($sorteos_activos as $sorteo) {
                        $total_posibles += $sorteo['cantidad_elecciones'];
                        $total_usadas += $sorteo['elecciones_usadas'];
                    }
                    echo $total_posibles > 0 ? round(($total_usadas / $total_posibles) * 100, 1) : 0; 
                    ?>%
                </h3>
                <p class="stats-label">Progreso</p>
            </div>
        </div>
    </div>
</div>

<!-- Sorteos Activos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-fire me-2"></i>
                    Mis Sorteos Activos
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($sorteos_activos)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-muted">No tienes sorteos activos</h5>
                        <p class="text-muted">Cuando seas autorizado para participar en sorteos, aparecerán aquí.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($sorteos_activos as $sorteo): ?>
                            <div class="col-lg-6 col-xl-4 mb-3">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-gift me-1"></i>
                                            <?php echo htmlspecialchars($sorteo['descripcion']); ?>
                                        </h6>
                                        
                                        <div class="row text-center mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Disponibles</small>
                                                <div class="fw-bold text-success">
                                                    <?php echo ($sorteo['cantidad_elecciones'] - $sorteo['elecciones_usadas']); ?>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Usadas</small>
                                                <div class="fw-bold text-info">
                                                    <?php echo $sorteo['elecciones_usadas']; ?> / <?php echo $sorteo['cantidad_elecciones']; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="progress mb-3" style="height: 10px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: <?php echo $sorteo['cantidad_elecciones'] > 0 ? ($sorteo['elecciones_usadas'] / $sorteo['cantidad_elecciones']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        
                                        <div class="text-muted small mb-3">
                                            <i class="fas fa-calendar me-1"></i>
                                            Cierre: <?php echo formatDate($sorteo['fecha_cierre_sorteo']); ?>
                                        </div>
                                        
                                        <?php
                                        $dias_restantes = floor((strtotime($sorteo['fecha_cierre_sorteo']) - time()) / (60 * 60 * 24));
                                        if ($dias_restantes <= 3 && $dias_restantes >= 0):
                                        ?>
                                            <div class="alert alert-warning alert-sm py-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <small>¡Quedan <?php echo $dias_restantes; ?> días!</small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($sorteo['elecciones_usadas'] < $sorteo['cantidad_elecciones']): ?>
                                            <a href="index.php?action=participar&sorteo=<?php echo $sorteo['id']; ?>" 
                                               class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-plus me-1"></i>
                                                Elegir Balota
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-success btn-sm w-100" disabled>
                                                <i class="fas fa-check me-1"></i>
                                                Completado
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mis Últimas Balotas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Mis Últimas Balotas
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($mis_balotas)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-ticket-alt text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-muted">Aún no has elegido balotas</h5>
                        <p class="text-muted">¡Participa en un sorteo para ver tus números aquí!</p>
                        <a href="index.php?action=sorteos" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ver Sorteos Disponibles
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($mis_balotas as $balota): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <div class="card border-success h-100">
                                    <div class="card-body text-center">
                                        <div class="balota selected d-inline-flex mb-2">
                                            <?php echo $balota['numero_balota']; ?>
                                        </div>
                                        
                                        <h6 class="card-title small">
                                            <?php echo htmlspecialchars($balota['sorteo_descripcion']); ?>
                                        </h6>
                                        
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo formatDate($balota['fecha_eleccion'], 'd/m/Y H:i'); ?>
                                        </small>
                                        
                                        <div class="mt-2">
                                            <?php
                                            $estado_sorteo = strtotime($balota['fecha_cierre_sorteo']) > time() ? 'activo' : 'cerrado';
                                            $badge_class = $estado_sorteo === 'activo' ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?> small">
                                                <?php echo ucfirst($estado_sorteo); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($mis_balotas) >= 10): ?>
                        <div class="text-center">
                            <a href="index.php?action=mis_balotas" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Ver Todas Mis Balotas
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?action=sorteos" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-eye d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Ver Sorteos</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?action=mis_balotas" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-history d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Mis Balotas</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <button class="btn btn-outline-success w-100 h-100 d-flex flex-column justify-content-center" 
                                onclick="generarNumeroAleatorio()">
                            <i class="fas fa-random d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Número Aleatorio</span>
                        </button>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?action=ayuda" class="btn btn-outline-warning w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-question-circle d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Ayuda</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para número aleatorio -->
<div class="modal fade" id="randomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-random me-2"></i>Número Aleatorio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Tu número de la suerte es:</p>
                <div class="balota selected d-inline-flex" id="randomNumber" style="font-size: 2rem; width: 100px; height: 100px;">
                    ---
                </div>
                <p class="mt-3 text-muted">¡Usa este número en tus próximas participaciones!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="generarOtroNumero()">
                    <i class="fas fa-redo me-1"></i>Generar Otro
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar datos en tiempo real cada 30 segundos
setInterval(function() {
    // Aquí se puede implementar actualización AJAX
    // Por ejemplo, verificar si hay nuevos sorteos disponibles
}, 30000);

// Generar número aleatorio
function generarNumeroAleatorio() {
    const randomNum = String(Math.floor(Math.random() * 100) + 1).padStart(3, '0');
    document.getElementById('randomNumber').textContent = randomNum;
    
    const modal = new bootstrap.Modal(document.getElementById('randomModal'));
    modal.show();
}

function generarOtroNumero() {
    generarNumeroAleatorio();
}

// Mostrar notificaciones si hay sorteos por cerrar
<?php foreach ($sorteos_activos as $sorteo): ?>
    <?php 
    $dias_restantes = floor((strtotime($sorteo['fecha_cierre_sorteo']) - time()) / (60 * 60 * 24));
    if ($dias_restantes <= 1 && $dias_restantes >= 0 && $sorteo['elecciones_usadas'] < $sorteo['cantidad_elecciones']): 
    ?>
    setTimeout(function() {
        showToast('¡Sorteo "<?php echo addslashes($sorteo['descripcion']); ?>" cierra pronto! Tienes elecciones pendientes.', 'warning');
    }, 2000);
    <?php endif; ?>
<?php endforeach; ?>
</script>

<?php include VIEWS_PATH . '/layout/footer.php'; ?>