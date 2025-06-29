<?php
$pageTitle = "Panel de Administración";
include VIEWS_PATH . '/layout/header.php';

// Verificar que es moderador
if (!isModerador()) {
    redirect('index.php?action=dashboard', 'No tienes permisos para acceder a esta sección', 'error');
}

// Obtener estadísticas administrativas
try {
    // Total empleados
    $stmt = $pdo->query("SELECT COUNT(*) FROM empleados WHERE estado_emplado = 1");
    $total_empleados = $stmt->fetchColumn();

    // Total sorteos
    $stmt = $pdo->query("SELECT COUNT(*) FROM apertura_sorteo");
    $total_sorteos = $stmt->fetchColumn();

    // Sorteos activos
    $stmt = $pdo->query("SELECT COUNT(*) FROM apertura_sorteo WHERE estado = 1");
    $sorteos_activos = $stmt->fetchColumn();

    // Total participaciones
    $stmt = $pdo->query("SELECT COUNT(*) FROM balota_concursante");
    $total_participaciones = $stmt->fetchColumn();

    // Empleados autorizados en sorteos activos
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT es.id_empleado) 
        FROM empleados_en_sorteo es 
        JOIN apertura_sorteo aps ON es.id_sorteo = aps.id 
        WHERE es.estado = 1 AND aps.estado = 1
    ");
    $empleados_autorizados = $stmt->fetchColumn();

    // Sorteos recientes
    $stmt = $pdo->query("
        SELECT aps.*, 
               (SELECT COUNT(*) FROM empleados_en_sorteo WHERE id_sorteo = aps.id AND estado = 1) as empleados_autorizados,
               (SELECT COUNT(*) FROM balota_concursante bc 
                JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id 
                WHERE es.id_sorteo = aps.id) as participaciones
        FROM apertura_sorteo aps 
        ORDER BY aps.id DESC 
        LIMIT 5
    ");
    $sorteos_recientes = $stmt->fetchAll();

    // Últimas participaciones
    $stmt = $pdo->query("
        SELECT bc.*, e.nombre_completo, e.numero_documento, aps.descripcion as sorteo_nombre
        FROM balota_concursante bc
        JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
        JOIN empleados e ON es.id_empleado = e.id
        JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
        ORDER BY bc.fecha_eleccion DESC
        LIMIT 10
    ");
    $participaciones_recientes = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error en dashboard admin: " . $e->getMessage());
    $total_empleados = $total_sorteos = $sorteos_activos = $total_participaciones = $empleados_autorizados = 0;
    $sorteos_recientes = $participaciones_recientes = [];
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
                            <i class="fas fa-user-shield me-2"></i>
                            Panel de Administración
                        </h1>
                        <p class="mb-0 opacity-75">
                            Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?> - 
                            Nivel de permiso: <?php echo $_SESSION['nivel_permiso'] ?? 'N/A'; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="btn-group" role="group">
                            <a href="index.php?action=admin&page=sorteos" class="btn btn-light btn-sm">
                                <i class="fas fa-gift me-1"></i>Sorteos
                            </a>
                            <a href="index.php?action=admin&page=empleados" class="btn btn-light btn-sm">
                                <i class="fas fa-users me-1"></i>Empleados
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card success">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="stats-number"><?php echo $total_empleados; ?></h3>
                <p class="stats-label">Empleados Activos</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card info">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <h3 class="stats-number"><?php echo $total_sorteos; ?></h3>
                <p class="stats-label">Total Sorteos</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card warning">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <h3 class="stats-number"><?php echo $sorteos_activos; ?></h3>
                <p class="stats-label">Sorteos Activos</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card danger">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="stats-number"><?php echo $total_participaciones; ?></h3>
                <p class="stats-label">Participaciones</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card success">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="stats-number"><?php echo $empleados_autorizados; ?></h3>
                <p class="stats-label">Autorizados</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card info">
            <div class="card-body text-center">
                <div class="stats-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 class="stats-number">
                    <?php echo $empleados_autorizados > 0 ? round(($total_participaciones / $empleados_autorizados), 1) : 0; ?>
                </h3>
                <p class="stats-label">Promedio Participación</p>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row mb-4">
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
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <a href="index.php?action=admin&page=sorteos&sub=nuevo" class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-plus d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Nuevo Sorteo</span>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <a href="index.php?action=admin&page=empleados&sub=autorizar" class="btn btn-success w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-user-check d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Autorizar Empleados</span>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <a href="index.php?action=admin&page=reportes" class="btn btn-info w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-chart-bar d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Ver Reportes</span>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <button class="btn btn-warning w-100 h-100 d-flex flex-column justify-content-center" onclick="ejecutarSorteoModal()">
                            <i class="fas fa-trophy d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Ejecutar Sorteo</span>
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <a href="index.php?action=admin&page=empleados" class="btn btn-secondary w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-users-cog d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Gestionar Empleados</span>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <a href="index.php?action=admin&page=configuracion" class="btn btn-dark w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-cog d-block mb-2" style="font-size: 2rem;"></i>
                            <span>Configuración</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sorteos Recientes -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-gift me-2"></i>
                    Sorteos Recientes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($sorteos_recientes)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-gift text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">No hay sorteos registrados</p>
                        <a href="index.php?action=admin&page=sorteos&sub=nuevo" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Crear Primer Sorteo
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sorteo</th>
                                    <th>Estado</th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sorteos_recientes as $sorteo): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($sorteo['descripcion']); ?></div>
                                            <small class="text-muted">
                                                <?php echo formatDate($sorteo['fecha_inicio_sorteo'], 'd/m/Y'); ?> - 
                                                <?php echo formatDate($sorteo['fecha_cierre_sorteo'], 'd/m/Y'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $sorteo['estado'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $sorteo['estado'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $sorteo['participaciones']; ?> / <?php echo $sorteo['empleados_autorizados']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="index.php?action=admin&page=sorteos&id=<?php echo $sorteo['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($sorteo['estado']): ?>
                                                    <button class="btn btn-outline-warning btn-sm" 
                                                            onclick="ejecutarSorteo(<?php echo $sorteo['id']; ?>)">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <a href="index.php?action=admin&page=sorteos" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>Ver Todos los Sorteos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Últimas Participaciones -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Últimas Participaciones
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($participaciones_recientes)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-ticket-alt text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">No hay participaciones registradas</p>
                    </div>
                <?php else: ?>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($participaciones_recientes as $participacion): ?>
                            <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                <div class="balota selected me-3" style="width: 40px; height: 40px; font-size: 0.8rem;">
                                    <?php echo $participacion['numero_balota']; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small"><?php echo htmlspecialchars($participacion['nombre_completo']); ?></div>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($participacion['sorteo_nombre']); ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        <?php echo formatDate($participacion['fecha_eleccion'], 'd/m H:i'); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Función para mostrar modal de ejecutar sorteo
function ejecutarSorteoModal() {
    // Por ahora un alert simple, después se puede hacer un modal
    alert('Funcionalidad en desarrollo. Ve a Gestionar Sorteos para ejecutar sorteos específicos.');
}

// Función para ejecutar sorteo específico
function ejecutarSorteo(sorteoId) {
    if (confirm('¿Estás seguro de que quieres ejecutar este sorteo? Esta acción no se puede deshacer.')) {
        // Aquí iría la llamada AJAX para ejecutar el sorteo
        alert('Funcionalidad en desarrollo. Sorteo ID: ' + sorteoId);
    }
}

// Actualizar estadísticas cada 30 segundos
setInterval(function() {
    // Aquí se puede implementar actualización en tiempo real
}, 30000);
</script>

<?php include VIEWS_PATH . '/layout/footer.php'; ?>