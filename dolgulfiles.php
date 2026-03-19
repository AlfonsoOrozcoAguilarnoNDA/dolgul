<?php
/*
 * DOLGUL - Sistema de Tickets (Vigilancia)
 * Laboratorio 4: vibecodingmexico.com
 * Copyright (C) 2026 Alfonso Orozco Aguilar
 * Este programa es software libre: puedes redistribuirlo y/o modificarlo
 * bajo los términos de la Licencia Pública General de GNU según lo publicado
 * por la Fundación para el Software Libre, ya sea la versión 3 de la Licencia,
 * o (a tu elección) cualquier versión posterior.
 *
 * Este programa se distribuye con la esperanza de que sea útil,
 * pero SIN NINGUNA GARANTÍA; sin incluso la garantía implícita de
 * MERCANTILIDAD o APTITUD PARA UN PROPÓSITO PARTICULAR.
 * Vea la Licencia Pública General de GNU para más detalles.
 *
 * Deberías haber recibido una copia de la Licencia Pública General de GNU
 * junto con este programa. Si no es así, consulta <https://www.gnu.org/licenses/>.
 */

// Modelo: Grok (xAI) - Archivo de auditoría e integridad: dolgulfiles.php

require_once 'headergrok.php';

// Solo Administradores pueden ver esta página de auditoría
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger mt-5">Acceso denegado. Solo administradores pueden acceder a esta herramienta de auditoría.</div>';
    require_once 'footergrok.php';
    exit;
}

// Lista de archivos que componen el núcleo del sistema DOLGUL
$archivos_nucleo = [
    'config.php',
    'index.php',
    'login.php',
    'headergrok.php',
    'footergrok.php',
    'ticketsgrok.css',
    'catalogo_categorias.php',
    'catalogo_prioridades.php',
    'catalogo_usuarios.php',
    'catalogo_empresas.php',
    'catalogo_sistemas.php',
    'catalogo_procesos.php',
    'liga_sistema_empresa.php',
    'liga_consultor_empresa.php',
    'liga_sistema_proceso.php',
    'nuevo_ticket.php',
    'ver_ticket.php',
    'tickets_abiertos.php',
    'tickets_cerrados.php',
    'reporte_minutos.php',
    'dashboard.php',
    'dolgulfiles.php'  // se incluye a sí mismo para auto-auditoría
];

?>

<h2 class="mb-4">Auditoría de Integridad - DOLGUL</h2>

<p class="text-muted mb-4">
    Verificación de presencia, tamaño (líneas), última modificación y hash SHA-1 de los archivos núcleo del sistema.<br>
    Última ejecución: <?php echo date('d/m/Y H:i:s'); ?>
</p>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Archivo</th>
                <th>Estado</th>
                <th>Líneas de código</th>
                <th>Última modificación</th>
                <th>Hash SHA-1</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($archivos_nucleo as $archivo):
            $ruta = __DIR__ . '/' . $archivo;
            $existe = file_exists($ruta);

            if ($existe):
                $lineas = count(file($ruta));
                $modificacion_ts = filemtime($ruta);
                $modificacion = date('d/m/Y H:i:s', $modificacion_ts);
                $hash = sha1_file($ruta);
                $clase_fila = 'table-success';
                $estado_texto = 'OK';
            else:
                $lineas = '—';
                $modificacion = '—';
                $hash = '—';
                $clase_fila = 'table-danger';
                $estado_texto = 'MISSING';
            endif;
        ?>
            <tr class="<?php echo $clase_fila; ?>">
                <td><code><?php echo htmlspecialchars($archivo); ?></code></td>
                <td class="font-weight-bold"><?php echo $estado_texto; ?></td>
                <td class="text-center"><?php echo $lineas; ?></td>
                <td class="text-center"><?php echo $modificacion; ?></td>
                <td><small class="text-monospace"><?php echo $hash; ?></small></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle mr-2"></i>
    <strong>Recomendación:</strong> Ejecuta esta auditoría después de cada despliegue o actualización importante. 
    Guarda el resultado (captura o exporta la tabla) como evidencia de integridad en cada release.
</div>

<?php require_once 'footergrok.php'; ?>
