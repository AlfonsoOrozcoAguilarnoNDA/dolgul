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

// Modelo: Grok (xAI) - Chunk 6: catalogo_categorias.php

require_once 'headergrok.php';

// Solo Admin
if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'alta') {
        $name  = trim($_POST['tickets_categories_name'] ?? '');
        $order = (int)($_POST['tickets_categories_order'] ?? 1);

        if ($name !== '') {
            $stmt = mysqli_prepare($link, "INSERT INTO cat_categorias (tickets_categories_name, tickets_categories_order) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'si', $name, $order);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'edit') {
        $id    = (int)$_POST['id'];
        $name  = trim($_POST['tickets_categories_name'] ?? '');
        $order = (int)$_POST['tickets_categories_order'] ?? 1;

        if ($id > 0 && $name !== '') {
            $stmt = mysqli_prepare($link, "UPDATE cat_categorias SET tickets_categories_name = ?, tickets_categories_order = ? WHERE tickets_categories_id = ?");
            mysqli_stmt_bind_param($stmt, 'sii', $name, $order, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];

        // Verificar si está en uso
        $check = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE tickets_categories_id = $id"))['cnt'] ?? 0;

        if ($check == 0 && $id > 0) {
            mysqli_query($link, "DELETE FROM cat_categorias WHERE tickets_categories_id = $id");
        }
    }

    // Redirigir para evitar reenvío
    header("Location: catalogo_categorias.php");
    exit;
}

// Listado
$result = mysqli_query($link, "SELECT * FROM cat_categorias ORDER BY tickets_categories_order, tickets_categories_name");
?>

<h2 class="mb-4">Catálogos → Categorías</h2>

<!-- Alta -->
<form method="post" class="mb-4">
    <input type="hidden" name="action" value="alta">
    <div class="input-group">
        <input type="text" name="tickets_categories_name" class="form-control" placeholder="Nueva categoría..." required>
        <input type="number" name="tickets_categories_order" class="form-control" style="width:120px;" value="1" min="1" required>
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
                <th style="width:50px;">Orden</th>
                <th>Nombre</th>
                <th style="width:140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr data-id="<?= $row['tickets_categories_id'] ?>">
                <form method="post" class="inline-edit">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $row['tickets_categories_id'] ?>">
                    <td>
                        <input type="number" name="tickets_categories_order" value="<?= $row['tickets_categories_order'] ?>" min="1" class="form-control form-control-sm">
                    </td>
                    <td>
                        <input type="text" name="tickets_categories_name" value="<?= htmlspecialchars($row['tickets_categories_name']) ?>" class="form-control form-control-sm">
                    </td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-action btn-edit btn-sm" title="Guardar cambios"><i class="fas fa-save"></i></button>

                        <?php
                        $en_uso = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE tickets_categories_id = {$row['tickets_categories_id']}"))['cnt'] ?? 0;
                        if ($en_uso == 0):
                        ?>
                            <button type="button" class="btn btn-action btn-delete btn-sm" data-toggle="modal" data-target="#modalDelete<?= $row['tickets_categories_id'] ?>" title="Eliminar"><i class="fas fa-trash"></i></button>

                            <!-- Modal confirmación -->
                            <div class="modal fade" id="modalDelete<?= $row['tickets_categories_id'] ?>">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">¿Eliminar categoría?</h5>
                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                        </div>
                                        <div class="modal-body">
                                            Se eliminará permanentemente la categoría "<strong><?= htmlspecialchars($row['tickets_categories_name']) ?></strong>".
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['tickets_categories_id'] ?>">
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
            <tr><td colspan="3" class="text-center text-muted py-4">No hay categorías registradas aún.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footergrok.php'; ?>
