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

// Modelo: Grok (xAI) - Chunk 8: catalogo_usuarios.php

require_once 'headergrok.php';

if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

$items_por_pagina = 50;
$pagina = max(1, (int)($_POST['pagina'] ?? 1));
$offset = ($pagina - 1) * $items_por_pagina;

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'alta') {
        $name     = trim($_POST['users_name'] ?? '');
        $username = trim($_POST['users_username'] ?? '');
        $password = trim($_POST['users_password'] ?? '');
        $email    = trim($_POST['users_email'] ?? '');
        $empresa  = (int)($_POST['empresas_id'] ?? 1);
        $rol      = $_POST['users_admin'] ?? 'User';
        $status   = (int)($_POST['users_status'] ?? 1);
        $cel      = trim($_POST['telcelular'] ?? '');

        if ($name && $username && $password && $email) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($link, "INSERT INTO cat_usuarios (users_name, users_username, users_password, users_email, empresas_id, users_admin, users_status, telcelular) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssssissi', $name, $username, $hash, $email, $empresa, $rol, $status, $cel);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name     = trim($_POST['users_name'] ?? '');
        $username = trim($_POST['users_username'] ?? '');
        $email    = trim($_POST['users_email'] ?? '');
        $empresa  = (int)($_POST['empresas_id'] ?? 1);
        $rol      = $_POST['users_admin'] ?? 'User';
        $status   = (int)($_POST['users_status'] ?? 1);
        $cel      = trim($_POST['telcelular'] ?? '');

        $password_sql = '';
        $params = [$name, $username, $email, $empresa, $rol, $status, $cel, $id];
        $types = 'sssisisi';

        if (!empty($_POST['users_password'])) {
            $hash = password_hash($_POST['users_password'], PASSWORD_DEFAULT);
            $password_sql = ", users_password = ?";
            array_splice($params, 2, 0, $hash);
            $types = 'sss' . $types;
        }

        $sql = "UPDATE cat_usuarios SET users_name = ?, users_username = ?, users_email = ?, empresas_id = ?, users_admin = ?, users_status = ?, telcelular = ? $password_sql WHERE users_id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    elseif ($action === 'toggle_status') {
        $id = (int)$_POST['id'];
        mysqli_query($link, "UPDATE cat_usuarios SET users_status = NOT users_status WHERE users_id = $id");
    }

    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $check_t = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE tickets_username = (SELECT users_username FROM cat_usuarios WHERE users_id = $id)"))['cnt'] ?? 0;
        $check_e = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM cat_empresas WHERE empresas_id IN (SELECT empresas_id FROM cat_usuarios WHERE users_id = $id)"))['cnt'] ?? 0;

        if ($check_t == 0 && $check_e == 0) {
            mysqli_query($link, "DELETE FROM cat_usuarios WHERE users_id = $id");
        }
    }

    header("Location: catalogo_usuarios.php?pagina=$pagina");
    exit;
}

// Listado paginado
$total = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) total FROM cat_usuarios"))['total'] ?? 0;
$paginas_total = max(1, ceil($total / $items_por_pagina));

$result = mysqli_query($link, "SELECT u.*, e.empresas_name 
                               FROM cat_usuarios u 
                               LEFT JOIN cat_empresas e ON u.empresas_id = e.empresas_id 
                               ORDER BY u.users_name 
                               LIMIT $offset, $items_por_pagina");
?>

<h2 class="mb-4">Catálogos → Usuarios</h2>

<!-- Formulario de alta -->
<form method="post" class="mb-5 card p-4">
    <input type="hidden" name="action" value="alta">
    <div class="form-row">
        <div class="col-md-3"><input type="text" name="users_name" class="form-control" placeholder="Nombre completo" required></div>
        <div class="col-md-2"><input type="text" name="users_username" class="form-control" placeholder="Usuario" required></div>
        <div class="col-md-2"><input type="password" name="users_password" class="form-control" placeholder="Contraseña" required></div>
        <div class="col-md-3"><input type="email" name="users_email" class="form-control" placeholder="Email" required></div>
    </div>
    <div class="form-row mt-3">
        <div class="col-md-3">
            <select name="empresas_id" class="form-control" required>
                <?php
                $emp = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas ORDER BY empresas_name");
                while ($e = mysqli_fetch_assoc($emp)) {
                    echo "<option value='{$e['empresas_id']}'>" . htmlspecialchars($e['empresas_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="users_admin" class="form-control" required>
                <option value="Admin">Admin</option>
                <option value="Consultor">Consultor</option>
                <option value="Master">Master</option>
                <option value="User" selected>User</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="users_status" class="form-control">
                <option value="1" selected>Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
        <div class="col-md-3"><input type="text" name="telcelular" class="form-control" placeholder="Tel. celular (10 dígitos)"></div>
        <div class="col-md-2 mt-3 mt-md-0">
            <button type="submit" class="btn btn-primary btn-block">Crear usuario</button>
        </div>
    </div>
</form>

<!-- Listado -->
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Empresa</th>
                <th>Rol</th>
                <th>Status</th>
                <th>Celular</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <form method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $row['users_id'] ?>">
                    <input type="hidden" name="pagina" value="<?= $pagina ?>">
                    <td><input type="text" name="users_name" value="<?= htmlspecialchars($row['users_name']) ?>" class="form-control form-control-sm"></td>
                    <td><input type="text" name="users_username" value="<?= htmlspecialchars($row['users_username']) ?>" class="form-control form-control-sm"></td>
                    <td><input type="email" name="users_email" value="<?= htmlspecialchars($row['users_email']) ?>" class="form-control form-control-sm"></td>
                    <td>
                        <select name="empresas_id" class="form-control form-control-sm">
                            <?php
                            $emp = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas ORDER BY empresas_name");
                            while ($e = mysqli_fetch_assoc($emp)) {
                                $sel = $e['empresas_id'] == $row['empresas_id'] ? 'selected' : '';
                                echo "<option value='{$e['empresas_id']}' $sel>" . htmlspecialchars($e['empresas_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <select name="users_admin" class="form-control form-control-sm">
                            <option value="Admin"    <?= $row['users_admin']=='Admin'?'selected':'' ?>>Admin</option>
                            <option value="Consultor"<?= $row['users_admin']=='Consultor'?'selected':'' ?>>Consultor</option>
                            <option value="Master"   <?= $row['users_admin']=='Master'?'selected':'' ?>>Master</option>
                            <option value="User"     <?= $row['users_admin']=='User'?'selected':'' ?>>User</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="submit" name="action" value="toggle_status" class="btn btn-sm <?= $row['users_status']?'btn-success':'btn-secondary' ?>">
                            <?= $row['users_status'] ? 'Activo' : 'Inactivo' ?>
                        </button>
                    </td>
                    <td><input type="text" name="telcelular" value="<?= htmlspecialchars($row['telcelular']) ?>" class="form-control form-control-sm"></td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-action btn-edit btn-sm" title="Guardar cambios"><i class="fas fa-save"></i></button>

                        <?php
                        $en_tickets = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) cnt FROM tickets_tickets WHERE tickets_username = '{$row['users_username']}'"))['cnt'] ?? 0;
                        if ($en_tickets == 0):
                        ?>
                            <button type="button" class="btn btn-action btn-delete btn-sm" data-toggle="modal" data-target="#delUser<?= $row['users_id'] ?>" title="Eliminar"><i class="fas fa-trash"></i></button>

                            <div class="modal fade" id="delUser<?= $row['users_id'] ?>">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5>¿Eliminar usuario?</h5><button type="button" class="close" data-dismiss="modal">×</button></div>
                                        <div class="modal-body">Se eliminará "<strong><?= htmlspecialchars($row['users_name']) ?></strong>" permanentemente.</div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['users_id'] ?>">
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
            <tr><td colspan="8" class="text-center py-4 text-muted">No hay usuarios registrados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($paginas_total > 1): ?>
<nav aria-label="Paginación usuarios">
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
