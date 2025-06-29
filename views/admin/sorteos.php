
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
