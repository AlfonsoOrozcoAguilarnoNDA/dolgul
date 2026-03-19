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

// Modelo: Grok (xAI) - Chunk 10: catalogo_sistemas.php

require_once 'headergrok.php';

if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'alta') {
        $name = trim($_POST['productos_name'] ?? '');
        $comment = trim($_POST['productos_comment'] ?? '');

        if ($name !== '') {
            $stmt = mysqli_prepare($link, "INSERT INTO cat_productos (productos_name, productos_comment) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'ss', $name, $comment);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['productos_name'] ?? '');
        $comment = trim($_POST['productos_comment'] ?? '');

        if ($id > 0 && $name !== '') {
            $stmt = mysqli_prepare($link, "UPDATE cat_productos SET productos_name = ?, productos_comment = ? WHERE productos_id = ?");
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $comment, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $check_pe = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM productosempresas WHERE productos_id = $id"))['cnt'] ?? 0;
        $check_pm = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM productosmodulos WHERE productos_id = $id"))['cnt'] ?? 0;
        $check_t  = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE productos_id = $id"))['cnt'] ?? 0;

        if ($check_pe == 0 && $check_pm == 0 && $check_t == 0) {
            mysqli_query($link, "DELETE FROM cat_productos WHERE productos_id = $id");
        }
    }

    header("Location: catalogo_sistemas.php");
    exit;
}

// Listado
$result = mysqli_query($link, "SELECT * FROM cat_productos ORDER BY productos_name");
?>

<h2 class="mb-4">Catálogos → <?= htmlspecialchars($label_sistemas) ?></h2>

<!-- Alta -->
<form method="post" class="mb-4">
    <input type="hidden" name="action" value="alta">
    <div class="input-group">
        <input type="text" name="productos_name" class="form-control" placeholder="Nombre del <?= strtolower($label_sistemas) ?>..." required>
        <input type="text" name="productos_comment" class="form-control" placeholder="Comentario / descripción (opcional)">
        <div class="input-group-append">
            <button type="submit" class="btn btn-success">Agregar</button>
        </div>
    </div>
</form>

<!-- Listado -->
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr>
                <th><?= htmlspecialchars($label_sistemas) ?></th>
                <th>Comentario</th>
                <th style="width:140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <form method="post" class="inline-edit">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $row['productos_id'] ?>">
                    <td><input type="text" name="productos_name" value="<?= htmlspecialchars($row['productos_name']) ?>" class="form-control form-control-sm" required></td>
                    <td><input type="text" name="productos_comment" value="<?= htmlspecialchars($row['productos_comment'] ?? '') ?>" class="form-control form-control-sm"></td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-action btn-edit btn-sm" title="Guardar"><i class="fas fa-save"></i></button>

                        <?php
                        $en_pe = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM productosempresas WHERE productos_id = {$row['productos_id']}"))['cnt'] ?? 0;
                        $en_pm = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM productosmodulos WHERE productos_id = {$row['productos_id']}"))['cnt'] ?? 0;
                        $en_t  = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE productos_id = {$row['productos_id']}"))['cnt'] ?? 0;
                        if ($en_pe == 0 && $en_pm == 0 && $en_t == 0):
                        ?>
                            <button type="button" class="btn btn-action btn-delete btn-sm" data-toggle="modal" data-target="#delSistema<?= $row['productos_id'] ?>" title="Eliminar"><i class="fas fa-trash"></i></button>

                            <div class="modal fade" id="delSistema<?= $row['productos_id'] ?>">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5>¿Eliminar <?= strtolower($label_sistemas) ?>?</h5><button type="button" class="close" data-dismiss="modal">×</button></div>
                                        <div class="modal-body">"<strong><?= htmlspecialchars($row['productos_name']) ?></strong>" se eliminará permanentemente.</div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['productos_id'] ?>">
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
            <tr><td colspan="3" class="text-center text-muted py-4">No hay <?= strtolower($label_sistemas) ?> registrados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footergrok.php'; ?>
