<?php
$pageTitle = "Inicio";
include VIEWS_PATH . '/layout/header.php';

// Obtener sorteos activos
$sorteosActivos = getSorteosActivos();

// Obtener estadísticas básicas
$stats = getEstadisticasSistema();
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white text-center">
            <div class="card-body py-5">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-building me-3"></i>
                    Sistema de Sorteos CMAICAO
                </h1>
                <p class="lead mb-4">
                    Participa en los sorteos internos de manera transparente y segura. 
                    Sistema exclusivo para empleados de CMAICAO.
                </p>
                <?php if (!isLoggedIn()): ?>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="index.php?action=login" class="btn btn-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </a>
                    </div>
                    <p class="mt-3 opacity-75">
                        <small>Ingresa con tu número de cédula y contraseña asignada</small>
                    </p>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="btn btn-light btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>Ir al Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Sistema -->
<div class="row mb-5">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total_empleados']; ?></h3>
                <p class="stats-label">Empleados Registrados</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card info">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['sorteos_activos']; ?></h3>
                <p class="stats-label">Sorteos Activos</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total_participaciones']; ?></h3>
                <p class="stats-label">Participaciones</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="stats-icon">
                    <i class="fas fa-hashtag"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total_balotas']; ?></h3>
                <p class="stats-label">Balotas Disponibles</p>
            </div>
        </div>
    </div>
</div>

<!-- Sorteos Activos -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="fas fa-fire text-danger me-2"></i>
            Sorteos Activos
        </h2>
        
        <?php if (empty($sorteosActivos)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-muted">No hay sorteos activos en este momento</h4>
                    <p class="text-muted">Mantente atento a los anuncios internos para nuevos sorteos.</p>
                    <?php if (isLoggedIn() && isModerador()): ?>
                        <a href="index.php?action=admin&page=sorteos" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Crear Nuevo Sorteo
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($sorteosActivos as $sorteo): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 fade-in-up">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-gift me-2"></i>
                                    <?php echo htmlspecialchars($sorteo['descripcion']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Inicio</small>
                                        <div class="fw-bold">
                                            <?php echo formatDate($sorteo['fecha_inicio_sorteo'], 'd/m/Y'); ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Cierre</small>
                                        <div class="fw-bold">
                                            <?php echo formatDate($sorteo['fecha_cierre_sorteo'], 'd/m/Y'); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mb-3">
                                    <?php
                                    try {
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM balota_concursante bc JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id WHERE es.id_sorteo = ?");
                                        $stmt->execute([$sorteo['id']]);
                                        $participaciones = $stmt->fetchColumn();
                                    } catch (Exception $e) {
                                        $participaciones = 0;
                                    }
                                    ?>
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $participaciones; ?> participaciones
                                    </span>
                                </div>
                                
                                <?php
                                $dias_restantes = floor((strtotime($sorteo['fecha_cierre_sorteo']) - time()) / (60 * 60 * 24));
                                if ($dias_restantes <= 3 && $dias_restantes >= 0):
                                ?>
                                    <div class="alert alert-warning py-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <small>¡Quedan <?php echo $dias_restantes; ?> día(s)!</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <?php if (isLoggedIn()): ?>
                                    <?php if (!isModerador()): ?>
                                        <?php if (isEmpleadoInSorteo($_SESSION['empleado_id'], $sorteo['id'])): ?>
                                            <a href="index.php?action=participar&sorteo=<?php echo $sorteo['id']; ?>" 
                                               class="btn btn-primary w-100">
                                                <i class="fas fa-ticket-alt me-2"></i>Participar
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="fas fa-lock me-2"></i>No Autorizado
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="index.php?action=admin&page=sorteos" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-cog me-2"></i>Administrar
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="index.php?action=login" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>Inicia Sesión
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cómo Funciona -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-5">
            <i class="fas fa-question-circle text-primary me-2"></i>
            ¿Cómo Funciona el Sistema?
        </h2>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-id-card fa-2x"></i>
                        </div>
                        <h5>1. Autorización</h5>
                        <p class="card-text">
                            Recursos Humanos autoriza tu participación en los sorteos vigentes.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-sign-in-alt fa-2x"></i>
                        </div>
                        <h5>2. Ingresar</h5>
                        <p class="card-text">
                            Accede con tu cédula y la contraseña proporcionada por el administrador.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-ticket-alt fa-2x"></i>
                        </div>
                        <h5>3. Elegir Balota</h5>
                        <p class="card-text">
                            Selecciona tu número de balota favorito dentro de las opciones disponibles.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                        <h5>4. Esperar Resultados</h5>
                        <p class="card-text">
                            El sorteo se realiza en la fecha establecida de manera transparente.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ganadores Recientes -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-5">
            <i class="fas fa-crown text-warning me-2"></i>
            Últimos Ganadores
        </h2>
        
        <div class="card">
            <div class="card-body">
                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT hg.*, bc.numero_balota, e.nombre_completo,
                               aps.descripcion as sorteo_nombre
                        FROM historico_ganadores hg
                        JOIN balota_concursante bc ON hg.id_balota_concursante = bc.id
                        JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                        JOIN empleados e ON es.id_empleado = e.id
                        JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
                        ORDER BY hg.fecha_loteria DESC
                        LIMIT 5
                    ");
                    $ganadores = $stmt->fetchAll();
                    
                    if (empty($ganadores)):
                ?>
                    <div class="text-center py-4">
                        <i class="fas fa-trophy text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-muted">Aún no hay ganadores registrados</h5>
                        <p class="text-muted">Los ganadores aparecerán aquí una vez se registren los resultados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-trophy me-1"></i>Sorteo</th>
                                    <th><i class="fas fa-user me-1"></i>Ganador</th>
                                    <th><i class="fas fa-hashtag me-1"></i>Balota</th>
                                    <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                    <th><i class="fas fa-dollar-sign me-1"></i>Premio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ganadores as $ganador): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ganador['sorteo_nombre']); ?></td>
                                        <td>
                                            <i class="fas fa-crown text-warning me-1"></i>
                                            <?php echo htmlspecialchars($ganador['nombre_completo']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $ganador['numero_balota']; ?></span>
                                        </td>
                                        <td><?php echo formatDate($ganador['fecha_loteria'], 'd/m/Y'); ?></td>
                                        <td>
                                            <?php if ($ganador['valor_premio']): ?>
                                                $<?php echo number_format($ganador['valor_premio'], 0, ',', '.'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">No especificado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php 
                    endif;
                } catch (Exception $e) {
                    echo '<div class="alert alert-warning">Error al cargar los ganadores</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . '/layout/footer.php'; ?>