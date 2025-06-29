<?php
// ==================================================================
// UTILS/REPORTES.PHP
// ==================================================================
class GeneradorReportes {
    
    public static function generarReporteSorteo($idSorteo) {
        $sorteoModel = new Sorteo();
        $balotaModel = new Balota();
        
        $sorteo = $sorteoModel->obtenerSorteoActivo(); // O por ID específico
        $participantes = $sorteoModel->obtenerParticipantesSorteo($idSorteo);
        $balotas = $balotaModel->obtenerResumenBalotas($idSorteo);
        
        $html = self::generarHTMLReporte($sorteo, $participantes, $balotas);
        
        return $html;
    }
    
    private static function generarHTMLReporte($sorteo, $participantes, $balotas) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reporte de Sorteo</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .logo { width: 100px; height: auto; }
                .info-box { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .stats { display: flex; justify-content: space-around; margin: 20px 0; }
                .stat-box { text-align: center; padding: 15px; background: #e3f2fd; border-radius: 5px; }
                .stat-number { font-size: 24px; font-weight: bold; color: #1976d2; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Sorteo</h1>
                <h2>Clínica Maicao</h2>
                <p>Generado el: <?= date('d/m/Y H:i:s') ?></p>
            </div>
            
            <div class="info-box">
                <h3>Información del Sorteo</h3>
                <p><strong>Descripción:</strong> <?= escaparHtml($sorteo['descripcion']) ?></p>
                <p><strong>Fecha de inicio:</strong> <?= formatearFecha($sorteo['fecha_inicio_sorteo']) ?></p>
                <p><strong>Fecha de cierre:</strong> <?= formatearFecha($sorteo['fecha_cierre_sorteo']) ?></p>
                <p><strong>Estado:</strong> <?= $sorteo['estado'] == 1 ? 'Activo' : 'Cerrado' ?></p>
            </div>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?= count($participantes) ?></div>
                    <div>Participantes</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= count($balotas) ?></div>
                    <div>Balotas Elegidas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format((count($balotas) / 800) * 100, 1) ?>%</div>
                    <div>Completado</div>
                </div>
            </div>
            
            <h3>Lista de Participantes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Elecciones Permitidas</th>
                        <th>Balotas Jugadas</th>
                        <th>Fecha Inscripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participantes as $participante): ?>
                    <tr>
                        <td><?= escaparHtml($participante['numero_documento']) ?></td>
                        <td><?= escaparHtml($participante['nombre_completo']) ?></td>
                        <td><?= $participante['cantidad_elecciones'] ?></td>
                        <td><?= $participante['balotas_jugadas'] ?></td>
                        <td><?= formatearFecha($participante['fecha_autorizacion']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h3>Balotas Elegidas</h3>
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Participante</th>
                        <th>Documento</th>
                        <th>Fecha de Elección</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($balotas as $balota): ?>
                    <tr>
                        <td><strong><?= escaparHtml($balota['numero_balota']) ?></strong></td>
                        <td><?= escaparHtml($balota['nombre_completo']) ?></td>
                        <td><?= escaparHtml($balota['numero_documento']) ?></td>
                        <td><?= formatearFechaHora($balota['fecha_eleccion']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="no-print" style="margin-top: 30px; text-align: center;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Imprimir Reporte
                </button>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    public static function exportarCSV($datos, $nombreArchivo) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($datos)) {
            // Escribir headers
            fputcsv($output, array_keys($datos[0]));
            
            // Escribir datos
            foreach ($datos as $fila) {
                fputcsv($output, $fila);
            }
        }
        
        fclose($output);
        exit;
    }
}