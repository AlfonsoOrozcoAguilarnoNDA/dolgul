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

// Modelo: Grok (xAI) - Chunk 19: reporte_minutos.php

require_once 'headergrok.php';

if (!in_array($_SESSION['user_role'] ?? '', ['Admin', 'Consultor'])) {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

$modo = $_POST['modo'] ?? 'cliente';
$empresas_id = (int)($_POST['empresas_id'] ?? 0);
$users_id    = (int)($_POST['users_id'] ?? 0);
$fecha_ini   = $_POST['fecha_ini'] ?? date('Y-m-01');
$fecha_fin   = $_POST['fecha_fin'] ?? date('Y-m-d');

// Validar fechas
if ($fecha_ini > $fecha_fin) {
    $fecha_ini = $fecha_fin;
}

$where = "WHERE r.tickets_child > 0 AND r.tickets_timestamp BETWEEN '$fecha_ini 00:00:00' AND '$fecha_fin 23:59:59' ";
$where_consultor = "";

if ($modo === 'cliente' && $empresas_id > 0) {
    $where .= " AND r.empresas_id = $empresas_id ";
} elseif ($modo === 'consultor' && $users_id > 0) {
    $where_consultor = " AND r.tickets_username IN (SELECT users_username FROM cat_usuarios WHERE users_id = $users_id) ";
    $where .= $where_consultor;
}

if ($_SESSION['user_role'] === 'Consultor') {
    $empresas_consultor = "SELECT empresas_id FROM consultoresempresas WHERE users_id = " . (int)$_SESSION['user_id'];
    $where .= " AND r.empresas_id IN ($empresas_consultor) ";
}

// Consultas
$total_minutos = mysqli_fetch_assoc(mysqli_query($link, "SELECT COALESCE(SUM(r.tickets_minutos), 0) AS total 
                                                         FROM tickets_tickets r $where"))['total'] ?? 0;

$total_tickets = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(DISTINCT r.tickets_child) AS cnt 
                                                         FROM tickets_tickets r $where"))['cnt'] ?? 0;

$horas_totales = floor($total_minutos / 60);
$minutos_restantes = $total_minutos % 60;

$horas_contratadas = 0;
$saldo_horas = 0;
$saldo_minutos = 0;

if ($modo === 'cliente' && $empresas_id > 0) {
    $horas_contratadas = mysqli_fetch_assoc(mysqli_query($link, "SELECT empresas_horasg FROM cat_empresas WHERE empresas_id = $empresas_id"))['empresas_horasg'] ?? 0;
    $minutos_contratados = $horas_contratadas * 60;
    $saldo_minutos_total = $minutos_contratados - $total_minutos;
    $saldo_horas = floor($saldo_minutos_total / 60);
    $saldo_minutos = $saldo_minutos_total % 60;
    if ($saldo_minutos_total < 0) {
        $saldo_horas = -$saldo_horas;
        $saldo_minutos = abs($saldo_minutos);
    }
}
?>

<h2 class="mb-4">Reporte de Minutos</h2>

<form method="post" class="card p-4 mb-5">
    <div class="form-row mb-4">
        <div class="col-md-6">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="modo_cliente" name="modo" value="cliente" class="custom-control-input" <?php echo ($modo === 'cliente') ? 'checked' : ''; ?>>
                <label class="custom-control-label" for="modo_cliente">Por Cliente</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="modo_consultor" name="modo" value="consultor" class="custom-control-input" <?php echo ($modo === 'consultor') ? 'checked' : ''; ?>>
                <label class="custom-control-label" for="modo_consultor">Por Consultor</label>
            </div>
        </div>
    </div>

    <div class="form-row">
        <?php if ($modo === 'cliente'): ?>
            <div class="form-group col-md-4">
                <label>Empresa</label>
                <select name="empresas_id" class="form-control" required>
                    <option value="">— Selecciona —</option>
                    <?php
                    $empresas = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas ORDER BY empresas_name");
                    while ($e = mysqli_fetch_assoc($empresas)):
                    ?>
                        <option value="<?php echo $e['empresas_id']; ?>" <?php echo ($empresas_id == $e['empresas_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e['empresas_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="form-group col-md-4">
                <label>Consultor</label>
                <select name="users_id" class="form-control" required>
                    <option value="">— Selecciona —</option>
                    <?php
                    $consultores = mysqli_query($link, "SELECT users_id, users_name FROM cat_usuarios WHERE users_admin = 'Consultor' ORDER BY users_name");
                    while ($c = mysqli_fetch_assoc($consultores)):
                    ?>
                        <option value="<?php echo $c['users_id']; ?>" <?php echo ($users_id == $c['users_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['users_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="form-group col-md-4">
            <label>Desde</label>
            <input type="date" name="fecha_ini" class="form-control" value="<?php echo htmlspecialchars($fecha_ini); ?>" required>
        </div>
        <div class="form-group col-md-4">
            <label>Hasta</label>
            <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Generar Reporte</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="card">
        <div class="card-body">
            <h4>Resumen del período: <?php echo date('d/m/Y', strtotime($fecha_ini)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></h4>

            <?php if ($modo === 'cliente' && $empresas_id > 0): ?>
                <p><strong>Total tickets atendidos:</strong> <?php echo $total_tickets; ?></p>
                <p><strong>Total minutos registrados:</strong> <?php echo $total_minutos; ?> min (<?php echo $horas_totales; ?> h <?php echo $minutos_restantes; ?> min)</p>
                <p><strong>Horas contratadas:</strong> <?php echo $horas_contratadas; ?> h (<?php echo $minutos_contratados; ?> min)</p>
                <p><strong>Saldo restante:</strong> 
                    <?php
                    if ($saldo_minutos_total >= 0) {
                        echo "<span class='text-success'>$saldo_horas h $saldo_minutos min</span>";
                    } else {
                        echo "<span class='text-danger'>-$saldo_horas h $saldo_minutos min (excedido)</span>";
                    }
                    ?>
                </p>
            <?php elseif ($modo === 'consultor' && $users_id > 0): ?>
                <p><strong>Total tickets atendidos:</strong> <?php echo $total_tickets; ?></p>
                <p><strong>Total minutos registrados:</strong> <?php echo $total_minutos; ?> min (<?php echo $horas_totales; ?> h <?php echo $minutos_restantes; ?> min)</p>

                <h5 class="mt-4">Desglose por empresa</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Tickets atendidos</th>
                                <th>Minutos</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $desglose = mysqli_query($link, "SELECT e.empresas_name, COUNT(DISTINCT r.tickets_child) AS tickets, SUM(r.tickets_minutos) AS minutos 
                                                         FROM tickets_tickets r 
                                                         LEFT JOIN cat_empresas e ON r.empresas_id = e.empresas_id 
                                                         $where 
                                                         GROUP BY r.empresas_id ORDER BY minutos DESC");
                        while ($d = mysqli_fetch_assoc($desglose)):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['empresas_name']); ?></td>
                                <td class="text-center"><?php echo $d['tickets']; ?></td>
                                <td class="text-center"><?php echo $d['minutos']; ?> min</td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($desglose) == 0): ?>
                            <tr><td colspan="3" class="text-center py-3">No hay datos en este período.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
