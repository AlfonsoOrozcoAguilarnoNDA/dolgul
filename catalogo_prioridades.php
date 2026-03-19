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

// Modelo: Grok (xAI) - Chunk 7: catalogo_prioridades.php

require_once 'headergrok.php';

// Solo Admin
if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'alta') {
        $name  = trim($_POST['prioridades_name'] ?? '');
        $order = (int)($_POST['prioridades_order'] ?? 1);
        $color = trim($_POST['prioridades_color'] ?? '');

        if ($name !== '' && preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
            $stmt = mysqli_prepare($link, "INSERT INTO cat_prioridades (prioridades_name, prioridades_order, prioridades_color) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sis', $name, $order, $color);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'edit') {
        $id    = (int)$_POST['id'];
        $name  = trim($_POST['prioridades_name'] ?? '');
        $order = (int)($_POST['prioridades_order'] ?? 1);
        $color = trim($_POST['prioridades_color'] ?? '');

        if ($id > 0 && $name !== '' && preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
            $stmt = mysqli_prepare($link, "UPDATE cat_prioridades SET prioridades_name = ?, prioridades_order = ?, prioridades_color = ? WHERE prioridades_id = ?");
            mysqli_stmt_bind_param($stmt, 'sisi', $name, $order, $color, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $check = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE prioridades_id = $id"))['cnt'] ?? 0;

        if ($check == 0 && $id > 0) {
            mysqli_query($link, "DELETE FROM cat_prioridades WHERE prioridades_id = $id");
        }
    }

    header("Location: catalogo_prioridades.php");
    exit;
}

// Listado ordenado
$result = mysqli_query($link, "SELECT * FROM cat_prioridades ORDER BY prioridades_order ASC, prioridades_name");
?>

<h2 class="mb-4">Catálogos → Prioridades</h2>

<!-- Alta -->
<form method="post" class="mb-4">
    <input type="hidden" name="action" value="alta">
    <div class="form-row">
        <div class="col-md-5">
            <input type="text" name="prioridades_name" class="form-control" placeholder="Nombre de prioridad..." required>
        </div>
        <div class="col-md-2">
            <input type="number" name="prioridades_order" class="form-control" placeholder="Orden" min="1" required>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">#</span>
                </div>
                <input type="text" name="prioridades_color" class="form-control" placeholder="Hex (ej: 28a745)" pattern="[0-9a-fA-F]{6}" maxlength="6" required>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success btn-block">Agregar</button>
        </div>
    </div>
</form>

<!-- Listado -->
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr>
                <th style="width:60px;">Orden</th>
                <th>Nombre</th>
                <th style="width:120px;">Color</th>
                <th style="width:160px;">Vista previa</th>
                <th style="width:140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr data-id="<?= $row['prioridades_id'] ?>">
                <form method="post" class="inline-edit">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $row['prioridades_id'] ?>">
                    <td><input type="number" name="prioridades_order" value="<?= $row['prioridades_order'] ?>" min="1" class="form-control form-control-sm"></td>
                    <td><input type="text" name="prioridades_name" value="<?= htmlspecialchars($row['prioridades_name']) ?>" class="form-control form-control-sm"></td>
                    <td>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text">#</span></div>
                            <input type="text" name="prioridades_color" value="<?= htmlspecialchars($row['prioridades_color']) ?>" class="form-control" pattern="[0-9a-fA-F]{6}" maxlength="6">
                        </div>
                    </td>
                    <td><div class="swatch-color" style="background-color: #<?= htmlspecialchars($row['prioridades_color']) ?>;"></div></td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-action btn-edit btn-sm" title="Guardar"><i class="fas fa-save"></i></button>

                        <?php
                        $en_uso = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE prioridades_id = {$row['prioridades_id']}"))['cnt'] ?? 0;
                        if ($en_uso == 0):
                        ?>
                            <button type="button" class="btn btn-action btn-delete btn-sm" data-toggle="modal" data-target="#modalDelPrio<?= $row['prioridades_id'] ?>" title="Eliminar"><i class="fas fa-trash"></i></button>

                            <div class="modal fade" id="modalDelPrio<?= $row['prioridades_id'] ?>">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5>¿Eliminar prioridad?</h5>
                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                        </div>
                                        <div class="modal-body">
                                            "<strong><?= htmlspecialchars($row['prioridades_name']) ?></strong>" se eliminará permanentemente.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['prioridades_id'] ?>">
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
            <tr><td colspan="5" class="text-center text-muted py-4">No hay prioridades registradas.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footergrok.php'; ?>
