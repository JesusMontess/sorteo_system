
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