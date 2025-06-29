<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Sorteo - Clínica Maicao</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary-color), #2980b9);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-success {
            background: linear-gradient(45deg, var(--success-color), #229954);
            border: none;
            border-radius: 25px;
        }

        .btn-danger {
            background: linear-gradient(45deg, var(--danger-color), #c0392b);
            border: none;
            border-radius: 25px;
        }

        .balota {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            color: white;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        }

        .balota:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .balota.seleccionada {
            background: linear-gradient(45deg, var(--success-color), #229954);
            transform: scale(1.2);
        }

        .balota.no-disponible {
            background: #bdc3c7;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .sorteo-container {
            min-height: 400px;
            border: 3px dashed #3498db;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .balotas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            padding: 20px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .animation-container {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
        }

        .balota-animada {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            25% { transform: translateY(-20px) translateX(10px); }
            50% { transform: translateY(-10px) translateX(-10px); }
            75% { transform: translateY(-15px) translateX(5px); }
        }

        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding-top: 10vh;
        }

        .form-floating label {
            color: #6c757d;
        }

        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .badge {
            font-size: 0.8em;
            padding: 8px 12px;
            border-radius: 15px;
        }

        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 10px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .progress {
            height: 10px;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .balotas-grid {
                grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
                gap: 5px;
            }
            
            .balota {
                width: 50px;
                height: 50px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<!-- =================================== -->
<!-- LAYOUT: HEADER.PHP -->
<!-- =================================== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <i class="fas fa-dice"></i> Sistema de Sorteo - Clínica Maicao
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($_SESSION['logged_in'])): ?>
                <ul class="navbar-nav me-auto">
                    <?php if ($_SESSION['usuario']['tipo_usuario'] === 'concursante'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>sorteo/dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>sorteo/elegir_balota">
                                <i class="fas fa-hand-pointer"></i> Elegir Balota
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>sorteo/listado">
                                <i class="fas fa-list"></i> Listado General
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/sorteos">
                                <i class="fas fa-cogs"></i> Gestionar Sorteos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/usuarios">
                                <i class="fas fa-users"></i> Gestionar Usuarios
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= escaparHtml($_SESSION['usuario']['nombre_completo']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- =================================== -->
<!-- AUTH/LOGIN.PHP -->
<!-- =================================== -->
<div class="container login-container">
    <div class="card">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-dice fa-3x text-primary mb-3"></i>
                <h2 class="card-title">Iniciar Sesión</h2>
                <p class="text-muted">Sistema de Sorteo - Clínica Maicao</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= escaparHtml($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>auth/login">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                           placeholder="Número de documento" required maxlength="15" pattern="[0-9]+">
                    <label for="numero_documento">
                        <i class="fas fa-id-card"></i> Número de Documento
                    </label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="clave" name="clave" 
                           placeholder="Contraseña" required>
                    <label for="clave">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    ¿No tienes acceso? Contacta al administrador del sistema.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- =================================== -->
<!-- SORTEO/DASHBOARD.PHP -->
<!-- =================================== -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h1 class="card-title">
                                <i class="fas fa-home"></i> Dashboard - Concursante
                            </h1>
                            <p class="card-text">
                                Bienvenido, <strong><?= escaparHtml($usuario['nombre_completo']) ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card">
                                <div class="stats-number"><?= count($balotas) ?></div>
                                <div>Balotas Elegidas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= escaparHtml($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> 
            <?php
                switch($_GET['error']) {
                    case 'no_sorteo': echo 'No hay sorteos activos en este momento.'; break;
                    case 'no_participacion': echo 'No estás inscrito en el sorteo actual.'; break;
                    default: echo escaparHtml($_GET['error']);
                }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Información del Sorteo</h5>
                </div>
                <div class="card-body">
                    <?php if ($sorteoActivo): ?>
                        <h6><?= escaparHtml($sorteoActivo['descripcion']) ?></h6>
                        <p><strong>Fecha de inicio:</strong> <?= formatearFecha($sorteoActivo['fecha_inicio_sorteo']) ?></p>
                        <p><strong>Fecha de cierre:</strong> <?= formatearFecha($sorteoActivo['fecha_cierre_sorteo']) ?></p>
                        
                        <?php if ($empleadoSort): ?>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: <?= (count($balotas) / $empleadoSort['cantidad_elecciones']) * 100 ?>%">
                                    <?= count($balotas) ?> / <?= $empleadoSort['cantidad_elecciones'] ?>
                                </div>
                            </div>
                            
                            <?php if ($puedeElegir): ?>
                                <a href="<?= BASE_URL ?>sorteo/elegir_balota" class="btn btn-primary">
                                    <i class="fas fa-hand-pointer"></i> Elegir Nueva Balota
                                </a>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Has utilizado todas tus elecciones disponibles.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                No estás inscrito en este sorteo. Contacta al administrador.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay sorteos activos en este momento.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Mis Balotas</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($balotas)): ?>
                        <div class="row g-2">
                            <?php foreach ($balotas as $balota): ?>
                                <div class="col-4">
                                    <div class="balota seleccionada" title="Elegida el <?= formatearFechaHora($balota['fecha_eleccion']) ?>">
                                        <?= escaparHtml($balota['numero_balota']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">
                            <i class="fas fa-inbox"></i><br>
                            Aún no has elegido ninguna balota.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =================================== -->
<!-- SORTEO/ELEGIR_BALOTA.PHP -->
<!-- =================================== -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">
                        <i class="fas fa-hand-pointer"></i> Elegir Balota
                    </h1>
                    <p class="card-text">Selecciona tu número de la suerte o déjalo al azar.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= escaparHtml($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!$puedeElegir): ?>
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> Ya has utilizado todas tus elecciones disponibles.
            <a href="<?= BASE_URL ?>sorteo/dashboard" class="btn btn-primary ms-3">Volver al Dashboard</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-dice"></i> Balotas Disponibles</h5>
                        <div>
                            <button class="btn btn-success" onclick="elegirAleatoria()">
                                <i class="fas fa-random"></i> Elegir al Azar
                            </button>
                            <button class="btn btn-info" onclick="iniciarAnimacion()">
                                <i class="fas fa-play"></i> Ver Animación
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Contenedor de animación -->
                        <div id="animationContainer" class="animation-container mb-4" style="display: none;">
                            <div class="text-center">
                                <h6>¡Las balotas están mezclándose!</h6>
                                <button class="btn btn-danger" onclick="detenerAnimacion()">
                                    <i class="fas fa-stop"></i> Detener y Elegir
                                </button>
                            </div>
                        </div>

                        <!-- Grid de balotas -->
                        <div id="balotasGrid" class="balotas-grid">
                            <?php foreach ($balotasDisponibles as $balota): ?>
                                <div class="balota" 
                                     data-numero="<?= $balota['numero_balota'] ?>"
                                     onclick="seleccionarBalota('<?= $balota['numero_balota'] ?>')">
                                    <?= $balota['numero_balota'] ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-check-circle"></i> Confirmar Elección</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="balotaSeleccionada" class="mb-3" style="display: none;">
                            <div class="balota seleccionada mx-auto mb-3" id="balotaPreview"></div>
                            <p>Has seleccionado la balota: <strong id="numeroSeleccionado"></strong></p>
                        </div>

                        <form method="POST" id="formElegir" style="display: none;">
                            <input type="hidden" name="accion" value="elegir">
                            <input type="hidden" name="numero_balota" id="inputNumeroBalota">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Confirmar Elección
                            </button>
                        </form>

                        <form method="POST" id="formAleatoria" style="display: none;">
                            <input type="hidden" name="accion" value="aleatoria">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-random"></i> Confirmar Elección Aleatoria
                            </button>
                        </form>

                        <div id="noSeleccion">
                            <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Selecciona una balota para continuar</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle"></i> Información</h6>
                        <small class="text-muted">
                            • Una vez elegida, no podrás cambiar tu balota<br>
                            • Puedes elegir manualmente o al azar<br>
                            • La animación te ayuda a elegir de forma divertida
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- =================================== -->
<!-- SORTEO/LISTADO.PHP -->
<!-- =================================== -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">
                        <i class="fas fa-list"></i> Listado General de Balotas
                    </h1>
                    <p class="card-text">Todas las balotas elegidas en el sorteo actual.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($sorteoActivo): ?>
        <div class="card">
            <div class="card-header">
                <h5><?= escaparHtml($sorteoActivo['descripcion']) ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($balotas)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Balota</th>
                                    <th>Participante</th>
                                    <th>Documento</th>
                                    <th>Fecha de Elección</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($balotas as $balota): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                <?= escaparHtml($balota['numero_balota']) ?>
                                            </span>
                                        </td>
                                        <td><?= escaparHtml($balota['nombre_completo']) ?></td>
                                        <td><?= escaparHtml($balota['numero_documento']) ?></td>
                                        <td><?= formatearFechaHora($balota['fecha_eleccion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number"><?= count($balotas) ?></div>
                                <div>Balotas Elegidas</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                                <div class="stats-number"><?= count(array_unique(array_column($balotas, 'numero_documento'))) ?></div>
                                <div>Participantes Activos</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No hay balotas elegidas aún</h5>
                        <p class="text-muted">Sé el primero en participar en este sorteo.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No hay sorteos activos en este momento.
        </div>
    <?php endif; ?>
</div>

<!-- =================================== -->
<!-- ADMIN/DASHBOARD.PHP -->
<!-- =================================== -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="sidebar">
                <div class="p-3">
                    <h5><i class="fas fa-user-shield"></i> Panel de Administración</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="<?= BASE_URL ?>admin/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/sorteos">
                        <i class="fas fa-cogs"></i> Gestionar Sorteos
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/usuarios">
                        <i class="fas fa-users"></i> Gestionar Usuarios
                    </a>
                </nav>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Administrativo
                    </h1>
                    <p class="card-text">
                        Bienvenido, <strong><?= escaparHtml($_SESSION['usuario']['nombre_completo']) ?></strong>
                    </p>
                </div>
            </div>

            <!-- Estadísticas generales -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number"><?= count($sorteos) ?></div>
                        <div>Total Sorteos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <div class="stats-number"><?= isset($estadisticas['total_participantes']) ? $estadisticas['total_participantes'] : 0 ?></div>
                        <div>Participantes Activos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <div class="stats-number"><?= isset($estadisticas['total_balotas_elegidas']) ? $estadisticas['total_balotas_elegidas'] : 0 ?></div>
                        <div>Balotas Elegidas</div>
                    </div>
                </div>
            </div>

            <!-- Sorteo actual -->
            <?php if ($sorteoActivo): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-play-circle"></i> Sorteo Activo</h5>
                    </div>
                    <div class="card-body">
                        <h6><?= escaparHtml($sorteoActivo['descripcion']) ?></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha de inicio:</strong> <?= formatearFecha($sorteoActivo['fecha_inicio_sorteo']) ?></p>
                                <p><strong>Fecha de cierre:</strong> <?= formatearFecha($sorteoActivo['fecha_cierre_sorteo']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: <?= isset($estadisticas['total_balotas_elegidas']) ? ($estadisticas['total_balotas_elegidas'] / 800) * 100 : 0 ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    <?= isset($estadisticas['total_balotas_elegidas']) ? $estadisticas['total_balotas_elegidas'] : 0 ?> de 800 balotas elegidas
                                </small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-warning me-2" onclick="pausarSorteo(<?= $sorteoActivo['id'] ?>)">
                                <i class="fas fa-pause"></i> Pausar Sorteo
                            </button>
                            <button class="btn btn-danger" onclick="cerrarSorteo(<?= $sorteoActivo['id'] ?>)">
                                <i class="fas fa-stop"></i> Cerrar Sorteo
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Listado de sorteos recientes -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> Sorteos Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($sorteos)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Cierre</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($sorteos, 0, 5) as $sorteo): ?>
                                        <tr>
                                            <td><?= escaparHtml($sorteo['descripcion']) ?></td>
                                            <td><?= formatearFecha($sorteo['fecha_inicio_sorteo']) ?></td>
                                            <td><?= formatearFecha($sorteo['fecha_cierre_sorteo']) ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = '';
                                                switch($sorteo['estado']) {
                                                    case 1: $badgeClass = 'bg-success'; break;
                                                    case 0: $badgeClass = 'bg-secondary'; break;
                                                    default: $badgeClass = 'bg-warning';
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= $sorteo['estado_texto'] ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>admin/sorteos?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay sorteos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =================================== -->
<!-- ADMIN/SORTEOS.PHP -->
<!-- =================================== -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="sidebar">
                <div class="p-3">
                    <h5><i class="fas fa-user-shield"></i> Panel de Administración</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="<?= BASE_URL ?>admin/sorteos">
                        <i class="fas fa-cogs"></i> Gestionar Sorteos
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/usuarios">
                        <i class="fas fa-users"></i> Gestionar Usuarios
                    </a>
                </nav>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="card-title">
                                <i class="fas fa-cogs"></i> Gestionar Sorteos
                            </h1>
                            <p class="card-text">Crear, modificar y controlar sorteos</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoSorteo">
                            <i class="fas fa-plus"></i> Nuevo Sorteo
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= escaparHtml($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= escaparHtml($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Lista de Sorteos</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($sorteos)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Cierre</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sorteos as $sorteo): ?>
                                        <tr>
                                            <td><?= $sorteo['id'] ?></td>
                                            <td><?= escaparHtml($sorteo['descripcion']) ?></td>
                                            <td><?= formatearFecha($sorteo['fecha_inicio_sorteo']) ?></td>
                                            <td><?= formatearFecha($sorteo['fecha_cierre_sorteo']) ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = '';
                                                switch($sorteo['estado']) {
                                                    case 1: $badgeClass = 'bg-success'; break;
                                                    case 0: $badgeClass = 'bg-secondary'; break;
                                                    default: $badgeClass = 'bg-warning';
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= $sorteo['estado_texto'] ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($sorteo['estado'] == 1): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="pausarSorteo(<?= $sorteo['id'] ?>)">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="cerrarSorteo(<?= $sorteo['id'] ?>)">
                                                            <i class="fas fa-stop"></i>
                                                        </button>
                                                    <?php elseif ($sorteo['estado'] == 2): ?>
                                                        <button class="btn btn-sm btn-success" onclick="reanudarSorteo(<?= $sorteo['id'] ?>)">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="cerrarSorteo(<?= $sorteo['id'] ?>)">
                                                            <i class="fas fa-stop"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-info" onclick="verDetalles(<?= $sorteo['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No hay sorteos registrados</h5>
                            <p class="text-muted">Crea tu primer sorteo para comenzar.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Sorteo -->
<div class="modal fade" id="modalNuevoSorteo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Crear Nuevo Sorteo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción del Sorteo</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_cierre" class="form-label">Fecha de Cierre</label>
                        <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Sorteo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================== -->
<!-- ADMIN/USUARIOS.PHP -->
<!-- =================================== -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="sidebar">
                <div class="p-3">
                    <h5><i class="fas fa-user-shield"></i> Panel de Administración</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/sorteos">
                        <i class="fas fa-cogs"></i> Gestionar Sorteos
                    </a>
                    <a class="nav-link active" href="<?= BASE_URL ?>admin/usuarios">
                        <i class="fas fa-users"></i> Gestionar Usuarios
                    </a>
                </nav>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">
                        <i class="fas fa-users"></i> Gestionar Usuarios
                    </h1>
                    <p class="card-text">Administrar participantes y permisos del sistema</p>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= escaparHtml($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= escaparHtml($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Participantes del sorteo actual -->
            <?php if ($sorteoActivo): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-users"></i> Participantes - <?= escaparHtml($sorteoActivo['descripcion']) ?></h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalInscribirEmpleado">
                                <i class="fas fa-user-plus"></i> Inscribir Empleado
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($participantes)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Documento</th>
                                            <th>Nombre</th>
                                            <th>Elecciones Permitidas</th>
                                            <th>Balotas Jugadas</th>
                                            <th>Fecha Inscripción</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participantes as $participante): ?>
                                            <tr>
                                                <td><?= escaparHtml($participante['numero_documento']) ?></td>
                                                <td><?= escaparHtml($participante['nombre_completo']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $participante['cantidad_elecciones'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?= $participante['balotas_jugadas'] ?></span>
                                                </td>
                                                <td><?= formatearFecha($participante['fecha_autorizacion']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" onclick="removerParticipante(<?= $participante['id_empleado'] ?>)">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No hay participantes inscritos</h5>
                                <p class="text-muted">Inscribe empleados para que puedan participar en el sorteo.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Lista de todos los empleados -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Todos los Empleados</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($empleados)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped" id="tablaEmpleados">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tipo Doc.</th>
                                        <th>Documento</th>
                                        <th>Nombre</th>
                                        <th>Cargo</th>
                                        <th>Área</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($empleados as $empleado): ?>
                                        <tr>
                                            <td><?= escaparHtml($empleado['tipo_documento']) ?></td>
                                            <td><?= escaparHtml($empleado['numero_documento']) ?></td>
                                            <td><?= escaparHtml($empleado['nombre_completo']) ?></td>
                                            <td><?= escaparHtml($empleado['cargo']) ?></td>
                                            <td><?= escaparHtml($empleado['area'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge bg-success">Activo</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay empleados registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Inscribir Empleado -->
<div class="modal fade" id="modalInscribirEmpleado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Inscribir Empleado al Sorteo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="inscribir">
                <input type="hidden" name="id_sorteo" value="<?= $sorteoActivo['id'] ?? '' ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_empleado" class="form-label">Empleado</label>
                        <select class="form-select" id="id_empleado" name="id_empleado" required>
                            <option value="">Seleccionar empleado...</option>
                            <?php foreach ($empleados as $empleado): ?>
                                <option value="<?= $empleado['id'] ?>">
                                    <?= escaparHtml($empleado['numero_documento']) ?> - <?= escaparHtml($empleado['nombre_completo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad_elecciones" class="form-label">Cantidad de Elecciones Permitidas</label>
                        <input type="number" class="form-control" id="cantidad_elecciones" name="cantidad_elecciones" 
                               value="1" min="1" max="10" required>
                        <div class="form-text">Número máximo de balotas que puede elegir este empleado.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Inscribir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================== -->
<!-- JAVASCRIPT COMPLETO -->
<!-- =================================== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    // Variables globales
    let balotaSeleccionada = null;
    let animacionActiva = false;
    let intervalAnimacion = null;

    // Función para seleccionar balota
    function seleccionarBalota(numero) {
        if (animacionActiva) return;
        
        // Remover selección anterior
        document.querySelectorAll('.balota').forEach(b => b.classList.remove('seleccionada'));
        
        // Seleccionar nueva balota
        const balota = document.querySelector(`[data-numero="${numero}"]`);
        if (balota && !balota.classList.contains('no-disponible')) {
            balota.classList.add('seleccionada');
            balotaSeleccionada = numero;
            
            // Actualizar preview
            document.getElementById('balotaPreview').textContent = numero;
            document.getElementById('numeroSeleccionado').textContent = numero;
            document.getElementById('inputNumeroBalota').value = numero;
            
            // Mostrar/ocultar elementos
            document.getElementById('balotaSeleccionada').style.display = 'block';
            document.getElementById('formElegir').style.display = 'block';
            document.getElementById('noSeleccion').style.display = 'none';
            document.getElementById('formAleatoria').style.display = 'none';
        }
    }

    // Función para elegir balota aleatoria
    function elegirAleatoria() {
        document.getElementById('formAleatoria').style.display = 'block';
        document.getElementById('formElegir').style.display = 'none';
        document.getElementById('balotaSeleccionada').style.display = 'none';
        document.getElementById('noSeleccion').style.display = 'none';
    }

    // Función para iniciar animación
    function iniciarAnimacion() {
        const container = document.getElementById('animationContainer');
        const grid = document.getElementById('balotasGrid');
        
        container.style.display = 'block';
        grid.style.display = 'none';
        animacionActiva = true;
        
        // Crear balotas animadas
        const balotas = Array.from(document.querySelectorAll('.balota')).map(b => b.dataset.numero);
        const containerRect = container.getBoundingClientRect();
        
        // Limpiar animaciones anteriores
        container.querySelectorAll('.balota-animada').forEach(b => b.remove());
        
        // Crear 20 balotas aleatorias para la animación
        for (let i = 0; i < 20; i++) {
            const balotaDiv = document.createElement('div');
            balotaDiv.className = 'balota-animada';
            balotaDiv.textContent = balotas[Math.floor(Math.random() * balotas.length)];
            balotaDiv.style.left = Math.random() * (container.offsetWidth - 40) + 'px';
            balotaDiv.style.top = Math.random() * (container.offsetHeight - 40) + 'px';
            balotaDiv.style.animationDelay = Math.random() * 2 + 's';
            container.appendChild(balotaDiv);
        }
        
        // Cambiar números cada 200ms
        intervalAnimacion = setInterval(() => {
            container.querySelectorAll('.balota-animada').forEach(b => {
                b.textContent = balotas[Math.floor(Math.random() * balotas.length)];
            });
        }, 200);
    }

    // Función para detener animación
    function detenerAnimacion() {
        if (!animacionActiva) return;
        
        animacionActiva = false;
        clearInterval(intervalAnimacion);
        
        const container = document.getElementById('animationContainer');
        const grid = document.getElementById('balotasGrid');
        const balotasAnimadas = container.querySelectorAll('.balota-animada');
        
        // Elegir una balota aleatoria de las que están en pantalla
        if (balotasAnimadas.length > 0) {
            const balotaElegida = balotasAnimadas[Math.floor(Math.random() * balotasAnimadas.length)];
            const numero = balotaElegida.textContent;
            
            // Verificar que la balota esté disponible
            const balotaReal = document.querySelector(`[data-numero="${numero}"]`);
            if (balotaReal && !balotaReal.classList.contains('no-disponible')) {
                seleccionarBalota(numero);
            } else {
                // Si no está disponible, elegir una aleatoria disponible
                elegirAleatoria();
            }
        }
        
        container.style.display = 'none';
        grid.style.display = 'grid';
        container.querySelectorAll('.balota-animada').forEach(b => b.remove());
    }

    // Funciones para administrador
    function pausarSorteo(id) {
        if (confirm('¿Estás seguro de que deseas pausar este sorteo?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion" value="pausar">
                <input type="hidden" name="id_sorteo" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function cerrarSorteo(id) {
        if (confirm('¿Estás seguro de que deseas cerrar este sorteo? Esta acción no se puede deshacer.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion" value="cerrar">
                <input type="hidden" name="id_sorteo" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function reanudarSorteo(id) {
        if (confirm('¿Estás seguro de que deseas reanudar este sorteo?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion" value="reanudar">
                <input type="hidden" name="id_sorteo" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function removerParticipante(idEmpleado) {
        if (confirm('¿Estás seguro de que deseas remover este participante del sorteo?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion" value="remover">
                <input type="hidden" name="id_empleado" value="${idEmpleado}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function verDetalles(id) {
        // Implementar modal con detalles del sorteo
        alert('Función de detalles en desarrollo');
    }

    // Verificación en tiempo real de balotas disponibles
    function verificarBalotaDisponible(numero) {
        fetch(`<?= BASE_URL ?>sorteo/verificarBalota?numero=${numero}`)
            .then(response => response.json())
            .then(data => {
                const balota = document.querySelector(`[data-numero="${numero}"]`);
                if (balota && !data.disponible) {
                    balota.classList.add('no-disponible');
                    balota.onclick = null;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Validaciones de formularios
    document.addEventListener('DOMContentLoaded', function() {
        // Validar fechas en formulario de sorteo
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaCierre = document.getElementById('fecha_cierre');
        
        if (fechaInicio && fechaCierre) {
            fechaInicio.addEventListener('change', function() {
                fechaCierre.min = this.value;
            });
            
            fechaCierre.addEventListener('change', function() {
                if (this.value < fechaInicio.value) {
                    alert('La fecha de cierre no puede ser anterior a la fecha de inicio.');
                    this.value = '';
                }
            });
        }

        // Búsqueda en tabla de empleados
        const tablaEmpleados = document.getElementById('tablaEmpleados');
        if (tablaEmpleados) {
            const inputBusqueda = document.createElement('input');
            inputBusqueda.type = 'text';
            inputBusqueda.className = 'form-control mb-3';
            inputBusqueda.placeholder = 'Buscar empleado por nombre o documento...';
            
            tablaEmpleados.parentNode.insertBefore(inputBusqueda, tablaEmpleados);
            
            inputBusqueda.addEventListener('keyup', function() {
                const filtro = this.value.toLowerCase();
                const filas = tablaEmpleados.getElementsByTagName('tr');
                
                for (let i = 1; i < filas.length; i++) {
                    const fila = filas[i];
                    const texto = fila.textContent || fila.innerText;
                    
                    if (texto.toLowerCase().indexOf(filtro) > -1) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                }
            });
        }

        // Auto-actualización de balotas cada 30 segundos (solo en página de elección)
        if (document.getElementById('balotasGrid')) {
            setInterval(() => {
                document.querySelectorAll('.balota').forEach(balota => {
                    const numero = balota.dataset.numero;
                    if (numero && !balota.classList.contains('seleccionada')) {
                        verificarBalotaDisponible(numero);
                    }
                });
            }, 30000);
        }

        // Confirmar envío de formularios importantes
        document.querySelectorAll('form').forEach(form => {
            if (form.querySelector('input[name="accion"][value="cerrar"]') ||
                form.querySelector('input[name="accion"][value="remover"]')) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('¿Estás seguro de realizar esta acción?')) {
                        e.preventDefault();
                    }
                });
            }
        });

        // Tooltips para balotas
        document.querySelectorAll('.balota').forEach(balota => {
            balota.addEventListener('mouseenter', function() {
                if (this.classList.contains('no-disponible')) {
                    this.title = 'Esta balota ya no está disponible';
                } else if (this.classList.contains('seleccionada')) {
                    this.title = 'Balota ya elegida por ti';
                } else {
                    this.title = `Balota número ${this.dataset.numero}`;
                }
            });
        });
    });

    // Función para mostrar notificaciones toast
    function mostrarNotificacion(mensaje, tipo = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        const container = document.getElementById('toast-container') || crearContainerToast();
        container.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    function crearContainerToast() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }

    // Función para copiar número de balota al portapapeles
    function copiarNumero(numero) {
        navigator.clipboard.writeText(numero).then(() => {
            mostrarNotificacion(`Número ${numero} copiado al portapapeles`);
        }).catch(() => {
            mostrarNotificacion('Error al copiar el número', 'danger');
        });
    }

    // Función para exportar lista de participantes
    function exportarParticipantes() {
        const tabla = document.querySelector('#participantesTable');
        if (!tabla) return;
        
        let csv = 'Documento,Nombre,Elecciones Permitidas,Balotas Jugadas,Fecha Inscripción\n';
        
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            const linea = Array.from(celdas).slice(0, 5).map(celda => 
                `"${celda.textContent.trim().replace(/"/g, '""')}"`
            ).join(',');
            csv += linea + '\n';
        });
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `participantes_sorteo_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Función para generar reporte de sorteo
    function generarReporte(idSorteo) {
        window.open(`${BASE_URL}admin/reporte?id=${idSorteo}`, '_blank');
    }

    // Efectos visuales adicionales
    function animarEstadisticas() {
        document.querySelectorAll('.stats-number').forEach(elemento => {
            const valorFinal = parseInt(elemento.textContent);
            let valorActual = 0;
            const incremento = Math.ceil(valorFinal / 20);
            
            const timer = setInterval(() => {
                valorActual += incremento;
                if (valorActual >= valorFinal) {
                    valorActual = valorFinal;
                    clearInterval(timer);
                }
                elemento.textContent = valorActual;
            }, 50);
        });
    }

    // Ejecutar animaciones al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        animarEstadisticas();
        
        // Agregar efectos hover a las cards
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });

    // Función para validar conexión en tiempo real
    function verificarConexion() {
        fetch(`${BASE_URL}api/ping`)
            .then(response => {
                if (response.ok) {
                    document.body.classList.remove('sin-conexion');
                } else {
                    document.body.classList.add('sin-conexion');
                }
            })
            .catch(() => {
                document.body.classList.add('sin-conexion');
            });
    }

    // Verificar conexión cada minuto
    setInterval(verificarConexion, 60000);

    // Funciones de accesibilidad
    document.addEventListener('keydown', function(e) {
        // ESC para cerrar modales
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                bootstrap.Modal.getInstance(modal)?.hide();
            });
        }
        
        // Enter para seleccionar balota si está en foco
        if (e.key === 'Enter' && e.target.classList.contains('balota')) {
            e.target.click();
        }
    });

    // Lazy loading para imágenes si las hay
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Función para modo oscuro/claro (opcional)
    function toggleTema() {
        document.body.classList.toggle('tema-oscuro');
        localStorage.setItem('tema', document.body.classList.contains('tema-oscuro') ? 'oscuro' : 'claro');
    }

    // Aplicar tema guardado
    document.addEventListener('DOMContentLoaded', function() {
        const temaGuardado = localStorage.getItem('tema');
        if (temaGuardado === 'oscuro') {
            document.body.classList.add('tema-oscuro');
        }
    });

    // Service Worker para funcionalidad offline (opcional)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
</script>

<!-- CSS adicional para temas y efectos -->
<style>
    /* Tema oscuro */
    .tema-oscuro {
        --primary-color: #1a1a1a;
        --secondary-color: #2c3e50;
        --light-bg: #2c3e50;
        --dark-text: #ecf0f1;
        background: linear-gradient(135deg, #2c3e50 0%, #1a1a1a 100%);
        color: var(--dark-text);
    }

    .tema-oscuro .card {
        background: rgba(44, 62, 80, 0.9);
        color: var(--dark-text);
    }

    .tema-oscuro .table {
        background: rgba(44, 62, 80, 0.9);
        color: var(--dark-text);
    }

    /* Efectos de conexión */
    .sin-conexion::before {
        content: 'Sin conexión a internet';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: #e74c3c;
        color: white;
        text-align: center;
        padding: 10px;
        z-index: 9999;
        font-weight: bold;
    }

    .sin-conexion {
        padding-top: 50px;
    }

    /* Animaciones de carga */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        margin: -15px 0 0 -15px;
        border: 3px solid #3498db;
        border-top: 3px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Efectos de hover mejorados */
    .balota:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .balota:active {
        transform: scale(0.95);
    }

    /* Responsive mejorado */
    @media (max-width: 576px) {
        .container, .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            height: 100vh;
            transition: left 0.3s ease;
            z-index: 1050;
        }
        
        .sidebar.show {
            left: 0;
        }
        
        .stats-card {
            margin-bottom: 15px;
        }
        
        .btn-group .btn {
            padding: 5px 8px;
            font-size: 12px;
        }
    }

    /* Transiciones suaves */
    * {
        transition: all 0.3s ease;
    }

    /* Print styles */
    @media print {
        .sidebar, .navbar, .btn, .modal {
            display: none !important;
        }
        
        .container-fluid {
            margin: 0;
            padding: 0;
        }
        
        .card {
            border: 1px solid #000;
            box-shadow: none;
        }
    }
</style>

<!-- =================================== -->
<!-- FOOTER.PHP -->
<!-- =================================== -->
<footer class="mt-5 py-4 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-dice"></i> Sistema de Sorteo</h5>
                <p class="mb-0">Clínica Maicao - <?= date('Y') ?></p>
                <small class="text-muted">Versión 1.0.0</small>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0">
                    <i class="fas fa-code"></i> Desarrollado con PHP & Bootstrap
                </p>
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Sistema seguro y confiable
                </small>
            </div>
        </div>
        <hr class="my-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    &copy; <?= date('Y') ?> Clínica Maicao. Todos los derechos reservados.
                </small>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-outline-light btn-sm me-2" onclick="toggleTema()">
                    <i class="fas fa-adjust"></i> Cambiar Tema
                </button>
                <button class="btn btn-outline-light btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</footer>

</body>
</html>