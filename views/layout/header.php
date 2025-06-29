<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Sistema de Sorteos CMAICAO</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <link href="<?php echo defined('ASSETS_PATH') ? ASSETS_PATH : '../assets'; ?>/css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-building me-2"></i>CMAICAO - Sistema de Sorteos
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=sorteos">
                                <i class="fas fa-list me-1"></i>Sorteos
                            </a>
                        </li>
                        
                        <?php if (function_exists('isModerador') && isModerador()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i>Administración
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?action=admin&page=empleados">
                                        <i class="fas fa-users me-1"></i>Gestionar Empleados
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?action=admin&page=sorteos">
                                        <i class="fas fa-gift me-1"></i>Gestionar Sorteos
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?action=admin&page=reportes">
                                        <i class="fas fa-chart-bar me-1"></i>Reportes
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?action=admin">
                                        <i class="fas fa-tachometer-alt me-1"></i>Panel Admin
                                    </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php 
                                echo isset($_SESSION['nombre_completo']) ? 
                                     htmlspecialchars($_SESSION['nombre_completo']) : 
                                     'Usuario'; 
                                ?>
                                <?php if (function_exists('isModerador') && isModerador()): ?>
                                    <span class="badge bg-warning text-dark ms-1">MOD</span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">
                                    <small class="text-muted">
                                        <i class="fas fa-id-card me-1"></i>
                                        <?php echo isset($_SESSION['numero_documento']) ? $_SESSION['numero_documento'] : ''; ?>
                                    </small>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?action=dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i>Mi Dashboard
                                </a></li>
                                
                                <?php if (!function_exists('isModerador') || !isModerador()): ?>
                                    <li><a class="dropdown-item" href="index.php?action=mis_balotas">
                                        <i class="fas fa-ticket-alt me-1"></i>Mis Balotas
                                    </a></li>
                                <?php endif; ?>
                                
                                <li><a class="dropdown-item" href="index.php?action=ayuda">
                                    <i class="fas fa-question-circle me-1"></i>Ayuda
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="index.php?action=logout">
                                    <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=login">
                                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container-fluid mt-4">
        <!-- Mensajes flash -->
        <?php 
        if (function_exists('getFlashMessage')) {
            $flash = getFlashMessage();
            if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php 
            endif;
        }
        ?>

        <!-- Información del usuario logueado (solo para debugging, se puede quitar después) -->
        <?php if (function_exists('isLoggedIn') && isLoggedIn() && isset($_GET['debug'])): ?>
            <div class="alert alert-info alert-dismissible">
                <h6><i class="fas fa-bug me-1"></i>Debug Info:</h6>
                <small>
                    <strong>Usuario:</strong> <?php echo $_SESSION['nombre_completo'] ?? 'N/A'; ?><br>
                    <strong>Cédula:</strong> <?php echo $_SESSION['numero_documento'] ?? 'N/A'; ?><br>
                    <strong>Rol:</strong> <?php echo $_SESSION['user_role'] ?? 'N/A'; ?><br>
                    <strong>Cargo:</strong> <?php echo $_SESSION['cargo'] ?? 'N/A'; ?><br>
                    <?php if (isset($_SESSION['area'])): ?>
                    <strong>Área:</strong> <?php echo $_SESSION['area']; ?><br>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['nivel_permiso'])): ?>
                    <strong>Nivel Permiso:</strong> <?php echo $_SESSION['nivel_permiso']; ?>
                    <?php endif; ?>
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Breadcrumb opcional -->
        <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php"><i class="fas fa-home"></i></a>
                    </li>
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if (isset($item['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo $item['title']; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>