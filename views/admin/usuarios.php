

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