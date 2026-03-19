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

// Modelo: Grok (xAI) - Chunk 14: liga_sistema_proceso.php

require_once 'headergrok.php';

if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

$sistemas = mysqli_query($link, "SELECT productos_id, productos_name FROM cat_productos ORDER BY productos_name");
$sistema_seleccionado = (int)($_POST['productos_id'] ?? 0);

// Procesar asignar/remover
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_liga'])) {
    $producto_id     = (int)$_POST['productos_id'];
    $subproducto_id  = (int)$_POST['subproductos_id'];
    $accion          = $_POST['accion_liga'];

    if ($producto_id > 0 && $subproducto_id > 0) {
        if ($accion === 'asignar') {
            $stmt = mysqli_prepare($link, "INSERT IGNORE INTO productosmodulos (productos_id, subproductos_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'ii', $producto_id, $subproducto_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($accion === 'remover') {
            mysqli_query($link, "DELETE FROM productosmodulos WHERE productos_id = $producto_id AND subproductos_id = $subproducto_id");
        }
    }
    header("Location: liga_sistema_proceso.php?productos_id=$producto_id");
    exit;
}

// Procesos disponibles
$procesos = mysqli_query($link, "SELECT subproductos_id, subproductos_name FROM cat_subproductos ORDER BY subproductos_name");

// Ligas actuales
$ligas_actuales = [];
if ($sistema_seleccionado > 0) {
    $res = mysqli_query($link, "SELECT subproductos_id FROM productosmodulos WHERE productos_id = $sistema_seleccionado");
    while ($r = mysqli_fetch_assoc($res)) {
        $ligas_actuales[$r['subproductos_id']] = true;
    }
}
?>

<h2 class="mb-4">Ligas → <?= htmlspecialchars($label_sistemas) ?> ↔ <?= htmlspecialchars($label_procesos) ?></h2>

<form method="post" class="mb-5">
    <div class="form-group">
        <label for="productos_id">Seleccionar <?= htmlspecialchars($label_sistemas) ?>:</label>
        <select name="productos_id" id="productos_id" class="form-control" onchange="this.form.submit()">
            <option value="0">— Selecciona un <?= strtolower($label_sistemas) ?> —</option>
            <?php while ($s = mysqli_fetch_assoc($sistemas)): ?>
                <option value="<?= $s['productos_id'] ?>" <?= $sistema_seleccionado == $s['productos_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['productos_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
</form>

<?php if ($sistema_seleccionado > 0): ?>

    <h4 class="mt-4">Asignación de <?= htmlspecialchars($label_procesos) ?> para el <?= strtolower($label_sistemas) ?> seleccionado</h4>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><?= htmlspecialchars($label_procesos) ?></th>
                    <th style="width:180px;">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = mysqli_fetch_assoc($procesos)): ?>
                <tr>
                    <td><?= htmlspecialchars($p['subproductos_name']) ?></td>
                    <td class="text-center">
                        <?php if (isset($ligas_actuales[$p['subproductos_id']])): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="productos_id" value="<?= $sistema_seleccionado ?>">
                                <input type="hidden" name="subproductos_id" value="<?= $p['subproductos_id'] ?>">
                                <input type="hidden" name="accion_liga" value="remover">
                                <button type="submit" class="btn btn-action btn-remover btn-sm" onclick="return confirm('¿Remover este <?= strtolower($label_procesos) ?> del <?= strtolower($label_sistemas) ?>?');">
                                    <i class="fas fa-minus-circle"></i> Remover
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="productos_id" value="<?= $sistema_seleccionado ?>">
                                <input type="hidden" name="subproductos_id" value="<?= $p['subproductos_id'] ?>">
                                <input type="hidden" name="accion_liga" value="asignar">
                                <button type="submit" class="btn btn-action btn-asignar btn-sm">
                                    <i class="fas fa-plus-circle"></i> Asignar
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($procesos) == 0): ?>
                <tr><td colspan="2" class="text-center py-4 text-muted">No hay <?= strtolower($label_procesos) ?> registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <div class="alert alert-info">Selecciona un <?= strtolower($label_sistemas) ?> para ver y modificar las asignaciones de <?= strtolower($label_procesos) ?>.</div>
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
