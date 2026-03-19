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

// Modelo: Grok (xAI) - Chunk 13: liga_consultor_empresa.php

require_once 'headergrok.php';

if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

$empresas = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas ORDER BY empresas_name");
$empresa_seleccionada = (int)($_POST['empresas_id'] ?? 0);

// Procesar asignar/remover
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_liga'])) {
    $empresa_id = (int)$_POST['empresas_id'];
    $user_id    = (int)$_POST['users_id'];
    $accion     = $_POST['accion_liga'];

    if ($empresa_id > 0 && $user_id > 0) {
        if ($accion === 'asignar') {
            $stmt = mysqli_prepare($link, "INSERT IGNORE INTO consultoresempresas (empresas_id, users_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'ii', $empresa_id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($accion === 'remover') {
            mysqli_query($link, "DELETE FROM consultoresempresas WHERE empresas_id = $empresa_id AND users_id = $user_id");
        }
    }
    header("Location: liga_consultor_empresa.php?empresas_id=$empresa_id");
    exit;
}

// Consultores disponibles
$consultores = mysqli_query($link, "SELECT users_id, users_name, users_username FROM cat_usuarios WHERE users_admin = 'Consultor' ORDER BY users_name");

// Ligas actuales
$ligas_actuales = [];
if ($empresa_seleccionada > 0) {
    $res = mysqli_query($link, "SELECT users_id FROM consultoresempresas WHERE empresas_id = $empresa_seleccionada");
    while ($r = mysqli_fetch_assoc($res)) {
        $ligas_actuales[$r['users_id']] = true;
    }
}
?>

<h2 class="mb-4">Ligas → Consultor ↔ Empresa</h2>

<form method="post" class="mb-5">
    <div class="form-group">
        <label for="empresas_id">Seleccionar Empresa:</label>
        <select name="empresas_id" id="empresas_id" class="form-control" onchange="this.form.submit()">
            <option value="0">— Selecciona una empresa —</option>
            <?php while ($e = mysqli_fetch_assoc($empresas)): ?>
                <option value="<?= $e['empresas_id'] ?>" <?= $empresa_seleccionada == $e['empresas_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['empresas_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
</form>

<?php if ($empresa_seleccionada > 0): ?>

    <h4 class="mt-4">Asignación de Consultores para la empresa seleccionada</h4>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Consultor</th>
                    <th>Usuario</th>
                    <th style="width:180px;">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($c = mysqli_fetch_assoc($consultores)): ?>
                <tr>
                    <td><?= htmlspecialchars($c['users_name']) ?></td>
                    <td><?= htmlspecialchars($c['users_username']) ?></td>
                    <td class="text-center">
                        <?php if (isset($ligas_actuales[$c['users_id']])): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="empresas_id" value="<?= $empresa_seleccionada ?>">
                                <input type="hidden" name="users_id" value="<?= $c['users_id'] ?>">
                                <input type="hidden" name="accion_liga" value="remover">
                                <button type="submit" class="btn btn-action btn-remover btn-sm" onclick="return confirm('¿Remover este consultor de la empresa?');">
                                    <i class="fas fa-minus-circle"></i> Remover
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="empresas_id" value="<?= $empresa_seleccionada ?>">
                                <input type="hidden" name="users_id" value="<?= $c['users_id'] ?>">
                                <input type="hidden" name="accion_liga" value="asignar">
                                <button type="submit" class="btn btn-action btn-asignar btn-sm">
                                    <i class="fas fa-plus-circle"></i> Asignar
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($consultores) == 0): ?>
                <tr><td colspan="3" class="text-center py-4 text-muted">No hay consultores registrados (users_admin = 'Consultor').</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <div class="alert alert-info">Selecciona una empresa para ver y modificar las asignaciones de consultores.</div>
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
