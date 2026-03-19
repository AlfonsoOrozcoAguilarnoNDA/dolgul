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

// Modelo: Grok (xAI) - Chunk 9: catalogo_empresas.php

require_once 'headergrok.php';

if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

$items_por_pagina = 50;
$pagina = max(1, (int)($_POST['pagina'] ?? 1));
$offset = ($pagina - 1) * $items_por_pagina;

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'alta') {
        $name = trim($_POST['empresas_name'] ?? '');
        if ($name !== '') {
            $stmt = mysqli_prepare($link, "INSERT INTO cat_empresas (empresas_name) VALUES (?)");
            mysqli_stmt_bind_param($stmt, 's', $name);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name    = trim($_POST['empresas_name'] ?? '');
        $dbegin  = $_POST['empresas_dbegin']  ?: null;
        $dend    = $_POST['empresas_dend']    ?: null;
        $horasg  = (int)($_POST['empresas_horasg'] ?? 0);
        $correoc = trim($_POST['empresas_correoc'] ?? '');
        $recibec = $_POST['empresas_recibec'] ?? 'no';
        $comment = trim($_POST['empresas_comment'] ?? '');

        $stmt = mysqli_prepare($link, "UPDATE cat_empresas SET empresas_name = ?, empresas_dbegin = ?, empresas_dend = ?, empresas_horasg = ?, empresas_correoc = ?, empresas_recibec = ?, empresas_comment = ? WHERE empresas_id = ?");
        mysqli_stmt_bind_param($stmt, 'sssisssi', $name, $dbegin, $dend, $horasg, $correoc, $recibec, $comment, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $check_u = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM cat_usuarios WHERE empresas_id = $id"))['cnt'] ?? 0;
        $check_t = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE empresas_id = $id"))['cnt'] ?? 0;

        if ($check_u == 0 && $check_t == 0) {
            mysqli_query($link, "DELETE FROM cat_empresas WHERE empresas_id = $id");
        }
    }

    header("Location: catalogo_empresas.php?pagina=$pagina");
    exit;
}

// Conteo y listado
$total = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) total FROM cat_empresas"))['total'] ?? 0;
$paginas_total = max(1, ceil($total / $items_por_pagina));

$result = mysqli_query($link, "SELECT * FROM cat_empresas ORDER BY empresas_name LIMIT $offset, $items_por_pagina");
?>

<h2 class="mb-4">Catálogos → Empresas (Clientes)</h2>

<!-- Alta rápida -->
<form method="post" class="mb-4">
    <input type="hidden" name="action" value="alta">
    <div class="input-group">
        <input type="text" name="empresas_name" class="form-control" placeholder="Nombre de la empresa..." required>
        <div class="input-group-append">
            <button type="submit" class="btn btn-success">Agregar empresa</button>
        </div>
    </div>
</form>

<!-- Listado -->
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Nombre</th>
                <th>Inicio contrato</th>
                <th>Fin contrato</th>
                <th>Horas contratadas</th>
                <th>Correo contacto</th>
                <th>Recibe copia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <form method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $row['empresas_id'] ?>">
                    <input type="hidden" name="pagina" value="<?= $pagina ?>">
                    <td><input type="text" name="empresas_name" value="<?= htmlspecialchars($row['empresas_name']) ?>" class="form-control form-control-sm"></td>
                    <td><input type="datetime-local" name="empresas_dbegin" value="<?= $row['empresas_dbegin'] ? date('Y-m-d\TH:i', strtotime($row['empresas_dbegin'])) : '' ?>" class="form-control form-control-sm"></td>
                    <td><input type="datetime-local" name="empresas_dend" value="<?= $row['empresas_dend'] ? date('Y-m-d\TH:i', strtotime($row['empresas_dend'])) : '' ?>" class="form-control form-control-sm"></td>
                    <td><input type="number" name="empresas_horasg" value="<?= $row['empresas_horasg'] ?>" min="0" class="form-control form-control-sm text-center"></td>
                    <td><input type="email" name="empresas_correoc" value="<?= htmlspecialchars($row['empresas_correoc'] ?? '') ?>" class="form-control form-control-sm"></td>
                    <td>
                        <select name="empresas_recibec" class="form-control form-control-sm">
                            <option value="si"  <?= $row['empresas_recibec']=='si'?'selected':'' ?>>Sí</option>
                            <option value="no"  <?= $row['empresas_recibec']=='no'?'selected':'' ?>>No</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-action btn-edit btn-sm"><i class="fas fa-save"></i></button>

                        <?php
                        $en_u = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM cat_usuarios WHERE empresas_id = {$row['empresas_id']}"))['cnt'] ?? 0;
                        $en_t = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE empresas_id = {$row['empresas_id']}"))['cnt'] ?? 0;
                        if ($en_u == 0 && $en_t == 0):
                        ?>
                            <button type="button" class="btn btn-action btn-delete btn-sm" data-toggle="modal" data-target="#delEmp<?= $row['empresas_id'] ?>"><i class="fas fa-trash"></i></button>

                            <div class="modal fade" id="delEmp<?= $row['empresas_id'] ?>">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5>¿Eliminar empresa?</h5><button type="button" class="close" data-dismiss="modal">×</button></div>
                                        <div class="modal-body">"<strong><?= htmlspecialchars($row['empresas_name']) ?></strong>" se eliminará permanentemente.</div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['empresas_id'] ?>">
                                                <input type="hidden" name="pagina" value="<?= $pagina ?>">
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($result) == 0): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No hay empresas registradas.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($paginas_total > 1): ?>
<nav aria-label="Paginación empresas">
    <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $paginas_total; $p++): ?>
            <li class="page-item <?= $p == $pagina ? 'active' : '' ?>">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="pagina" value="<?= $p ?>">
                    <button type="submit" class="page-link"><?= $p ?></button>
                </form>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
