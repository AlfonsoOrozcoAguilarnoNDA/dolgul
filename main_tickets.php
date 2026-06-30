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

// Modelo: Grok (xAI) - 

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

// Modelo: Grok (xAI) - Chunk 4: footergrok.php
?>
function dolgul_footer(){
</div> <!-- cierre de #subcontainer -->

<footer class="footer fixed-bottom bg-dark text-white py-2">
    <div class="container-fluid text-center small">
        <span>PHP v<?php echo phpversion(); ?> • Sistema de Tickets • © 2026 vibecodingmexico.com</span>
        <span class="mx-3">IP: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '—'); ?></span>
        <span>Tiempo de carga: <?php
            $tiempo_fin = microtime(true);
            $tiempo_total = round($tiempo_fin - $tiempo_inicio, 3);
            echo $tiempo_total . ' seg';
        ?></span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
} // dolgul_footer
function catalogo_categorias(){
// Chunk 6: catalogo_categorias.php
global $link;
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

<?php require_once 'footergrok.php'; 
} //catalogo_categorias

function catalogo_empresas(){    
 // Chunk 9: catalogo_empresas.php    
global $link;
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

<?php require_once 'footergrok.php'; 
} // catalogo_empresas

function catalogo_prioridades(){
// Chunk 7: catalogo_prioridades.php
global $link;
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

<?php require_once 'footergrok.php';
} // catalogo_prioridades

function dashboard(){
// Chunk 20: dashboard.php
global $link;    
if (!in_array($_SESSION['user_role'] ?? '', ['Admin', 'Consultor'])) {
    echo '<div class="alert alert-danger">Acceso denegado. Solo para Administradores y Consultores.</div>';
    require_once 'footergrok.php';
    exit;
}

$rol = $_SESSION['user_role'];
$user_id = (int)($_SESSION['user_id'] ?? 0);
$empresa_usuario = (int)($_SESSION['empresa_id'] ?? 1);

// Filtros
$empresas_id      = (int)($_POST['empresas_id'] ?? 0);
$users_id         = (int)($_POST['users_id'] ?? 0);
$productos_id     = (int)($_POST['productos_id'] ?? 0);
$subproductos_id  = (int)($_POST['subproductos_id'] ?? 0);
$prioridades_id   = (int)($_POST['prioridades_id'] ?? 0);
$tickets_categories_id = (int)($_POST['tickets_categories_id'] ?? 0);
$estatus          = $_POST['estatus'] ?? 'abiertos';  // abiertos, cerrados, todos

$where = "WHERE t.tickets_child = 0 ";

if ($rol === 'Consultor') {
    $where .= " AND t.empresas_id IN (SELECT empresas_id FROM consultoresempresas WHERE users_id = $user_id) ";
}

if ($empresas_id > 0) {
    $where .= " AND t.empresas_id = $empresas_id ";
}
if ($users_id > 0) {
    $username = mysqli_fetch_assoc(mysqli_query($link, "SELECT users_username FROM cat_usuarios WHERE users_id = $users_id"))['users_username'] ?? '';
    if ($username) $where .= " AND t.tickets_username = '" . mysqli_real_escape_string($link, $username) . "' ";
}
if ($productos_id > 0) $where .= " AND t.productos_id = $productos_id ";
if ($subproductos_id > 0) $where .= " AND t.subproductos_id = $subproductos_id ";
if ($prioridades_id > 0) $where .= " AND t.prioridades_id = $prioridades_id ";
if ($tickets_categories_id > 0) $where .= " AND t.tickets_categories_id = $tickets_categories_id ";

if ($estatus === 'abiertos') {
    $where .= " AND t.tickets_status IN ('Open', 'In Progress') ";
} elseif ($estatus === 'cerrados') {
    $where .= " AND t.tickets_status = 'Closed' ";
}
// 'todos' no agrega filtro

$items_por_pagina = 50;
$pagina = max(1, (int)($_POST['pagina'] ?? 1));
$offset = ($pagina - 1) * $items_por_pagina;

$total_q = mysqli_query($link, "SELECT COUNT(*) total FROM tickets_tickets t $where");
$total = mysqli_fetch_assoc($total_q)['total'] ?? 0;
$paginas_total = max(1, ceil($total / $items_por_pagina));

$query = "SELECT t.tickets_id, t.tickets_idempresa, t.tickets_subject, t.tickets_timestamp, t.tickets_status,
                 e.empresas_name, u.users_name, pr.productos_name, sp.subproductos_name,
                 p.prioridades_name, p.prioridades_color, c.tickets_categories_name,
                 (SELECT COUNT(*) FROM tickets_tickets r WHERE r.tickets_child = t.tickets_id) AS respuestas,
                 (SELECT SUM(tickets_minutos) FROM tickets_tickets r WHERE r.tickets_child = t.tickets_id) AS total_minutos
          FROM tickets_tickets t
          LEFT JOIN cat_empresas e ON t.empresas_id = e.empresas_id
          LEFT JOIN cat_usuarios u ON t.tickets_username = u.users_username
          LEFT JOIN cat_productos pr ON t.productos_id = pr.productos_id
          LEFT JOIN cat_subproductos sp ON t.subproductos_id = sp.subproductos_id
          LEFT JOIN cat_prioridades p ON t.prioridades_id = p.prioridades_id
          LEFT JOIN cat_categorias c ON t.tickets_categories_id = c.tickets_categories_id
          $where 
          ORDER BY t.tickets_timestamp DESC 
          LIMIT $offset, $items_por_pagina";

$result = mysqli_query($link, $query);
?>

<h2 class="mb-4">Dashboard General de Tickets</h2>

<form method="post" class="card p-4 mb-5">
    <div class="form-row">
        <div class="form-group col-md-3">
            <label>Empresa</label>
            <select name="empresas_id" class="form-control">
                <option value="0">Todas</option>
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

        <div class="form-group col-md-3">
            <label>Usuario</label>
            <select name="users_id" class="form-control">
                <option value="0">Todos</option>
                <?php
                $usuarios = mysqli_query($link, "SELECT users_id, users_name FROM cat_usuarios ORDER BY users_name");
                while ($u = mysqli_fetch_assoc($usuarios)):
                ?>
                    <option value="<?php echo $u['users_id']; ?>" <?php echo ($users_id == $u['users_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['users_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group col-md-3">
            <label><?php echo htmlspecialchars($label_sistemas); ?></label>
            <select name="productos_id" class="form-control">
                <option value="0">Todos</option>
                <?php
                $sistemas = mysqli_query($link, "SELECT productos_id, productos_name FROM cat_productos ORDER BY productos_name");
                while ($s = mysqli_fetch_assoc($sistemas)):
                ?>
                    <option value="<?php echo $s['productos_id']; ?>" <?php echo ($productos_id == $s['productos_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['productos_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group col-md-3">
            <label><?php echo htmlspecialchars($label_procesos); ?></label>
            <select name="subproductos_id" class="form-control">
                <option value="0">Todos</option>
                <?php
                $procesos = mysqli_query($link, "SELECT subproductos_id, subproductos_name FROM cat_subproductos ORDER BY subproductos_name");
                while ($sp = mysqli_fetch_assoc($procesos)):
                ?>
                    <option value="<?php echo $sp['subproductos_id']; ?>" <?php echo ($subproductos_id == $sp['subproductos_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sp['subproductos_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="form-row mt-3">
        <div class="form-group col-md-3">
            <label>Prioridad</label>
            <select name="prioridades_id" class="form-control">
                <option value="0">Todas</option>
                <?php
                $prios = mysqli_query($link, "SELECT prioridades_id, prioridades_name FROM cat_prioridades ORDER BY prioridades_order");
                while ($p = mysqli_fetch_assoc($prios)):
                ?>
                    <option value="<?php echo $p['prioridades_id']; ?>" <?php echo ($prioridades_id == $p['prioridades_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['prioridades_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group col-md-3">
            <label>Categoría</label>
            <select name="tickets_categories_id" class="form-control">
                <option value="0">Todas</option>
                <?php
                $cats = mysqli_query($link, "SELECT tickets_categories_id, tickets_categories_name FROM cat_categorias ORDER BY tickets_categories_order");
                while ($c = mysqli_fetch_assoc($cats)):
                ?>
                    <option value="<?php echo $c['tickets_categories_id']; ?>" <?php echo ($tickets_categories_id == $c['tickets_categories_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['tickets_categories_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group col-md-3">
            <label>Estatus</label>
            <select name="estatus" class="form-control">
                <option value="abiertos" <?php echo ($estatus === 'abiertos') ? 'selected' : ''; ?>>Abiertos / En Progreso</option>
                <option value="cerrados" <?php echo ($estatus === 'cerrados') ? 'selected' : ''; ?>>Cerrados</option>
                <option value="todos" <?php echo ($estatus === 'todos') ? 'selected' : ''; ?>>Todos</option>
            </select>
        </div>

        <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID Global</th>
                <th>ID Empresa</th>
                <th>Respuestas</th>
                <th>Tiempo</th>
                <th>Empresa / Usuario / <?php echo htmlspecialchars($label_procesos); ?></th>
                <th>Asunto</th>
                <th>Fecha/Hora</th>
                <th>Prioridad</th>
                <th>Categoría</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr class="cursor-pointer" onclick="window.location='ver_ticket.php?id=<?php echo $row['tickets_id']; ?>'">
                <td><?php echo $row['tickets_id']; ?></td>
                <td><?php echo $row['tickets_idempresa']; ?></td>
                <td class="text-center"><?php echo $row['respuestas']; ?></td>
                <td class="text-center">
                    <?php
                    $min = (int)$row['total_minutos'];
                    $hh = floor($min / 60);
                    $mm = $min % 60;
                    echo sprintf('%d:%02d', $hh, $mm);
                    ?>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($row['empresas_name']); ?></strong><br>
                    <?php echo htmlspecialchars($row['users_name'] ?? $row['tickets_username']); ?><br>
                    <?php echo htmlspecialchars($row['subproductos_name']); ?>
                </td>
                <td><?php echo htmlspecialchars($row['tickets_subject']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['tickets_timestamp'])); ?></td>
                <td><span class="badge badge-priority" style="background-color:#<?php echo htmlspecialchars($row['prioridades_color']); ?>;"><?php echo htmlspecialchars($row['prioridades_name']); ?></span></td>
                <td><?php echo htmlspecialchars($row['tickets_categories_name']); ?></td>
                <td>
                    <?php
                    $status_class = ($row['tickets_status'] === 'Open') ? 'badge-status-open' : 
                                    (($row['tickets_status'] === 'In Progress') ? 'badge-status-progress' : 'badge-status-closed');
                    ?>
                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['tickets_status']); ?></span>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($result) == 0): ?>
            <tr><td colspan="10" class="text-center py-4 text-muted">No hay tickets que coincidan con los filtros seleccionados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($paginas_total > 1): ?>
<nav aria-label="Paginación dashboard">
    <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $paginas_total; $p++): ?>
            <li class="page-item <?php echo ($p == $pagina) ? 'active' : ''; ?>">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="pagina" value="<?php echo $p; ?>">
                    <?php
                    // Mantener filtros en paginación
                    foreach ($_POST as $key => $val) {
                        if ($key !== 'pagina') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
                        }
                    }
                    ?>
                    <button type="submit" class="page-link"><?php echo $p; ?></button>
                </form>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif;
require_once 'footergrok.php';
} // dashboard

function liga_sistema_proceso(){
// Chunk 14: liga_sistema_proceso.php    
global $link;
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
<?php endif;
require_once 'footergrok.php'; 
} 
function liga_sistema_empresa(){
// Chunk 12: liga_sistema_empresa.php
global $link;    
if ($_SESSION['user_role'] !== 'Admin') {
    echo '<div class="alert alert-danger">Acceso denegado.</div>';
    require_once 'footergrok.php';
    exit;
}

$empresas = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas ORDER BY empresas_name");
$empresa_seleccionada = (int)($_POST['empresas_id'] ?? 0);

// Procesar acción de asignar/remover
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_liga'])) {
    $empresa_id  = (int)$_POST['empresas_id'];
    $producto_id = (int)$_POST['productos_id'];
    $accion      = $_POST['accion_liga'];

    if ($empresa_id > 0 && $producto_id > 0) {
        if ($accion === 'asignar') {
            $stmt = mysqli_prepare($link, "INSERT IGNORE INTO productosempresas (empresas_id, productos_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'ii', $empresa_id, $producto_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($accion === 'remover') {
            mysqli_query($link, "DELETE FROM productosempresas WHERE empresas_id = $empresa_id AND productos_id = $producto_id");
        }
    }
    // Recargar con la empresa seleccionada
    header("Location: liga_sistema_empresa.php?empresas_id=$empresa_id");
    exit;
}

// Obtener sistemas disponibles
$sistemas = mysqli_query($link, "SELECT productos_id, productos_name FROM cat_productos ORDER BY productos_name");

// Obtener ligas actuales de la empresa seleccionada
$ligas_actuales = [];
if ($empresa_seleccionada > 0) {
    $res = mysqli_query($link, "SELECT productos_id FROM productosempresas WHERE empresas_id = $empresa_seleccionada");
    while ($r = mysqli_fetch_assoc($res)) {
        $ligas_actuales[$r['productos_id']] = true;
    }
}
?>

<h2 class="mb-4">Ligas → <?= htmlspecialchars($label_sistemas) ?> ↔ Empresa</h2>

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

    <h4 class="mt-4">Asignación de <?= htmlspecialchars($label_sistemas) ?> para la empresa seleccionada</h4>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><?= htmlspecialchars($label_sistemas) ?></th>
                    <th style="width:180px;">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($s = mysqli_fetch_assoc($sistemas)): ?>
                <tr>
                    <td><?= htmlspecialchars($s['productos_name']) ?></td>
                    <td class="text-center">
                        <?php if (isset($ligas_actuales[$s['productos_id']])): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="empresas_id" value="<?= $empresa_seleccionada ?>">
                                <input type="hidden" name="productos_id" value="<?= $s['productos_id'] ?>">
                                <input type="hidden" name="accion_liga" value="remover">
                                <button type="submit" class="btn btn-action btn-remover btn-sm" onclick="return confirm('¿Remover este <?= strtolower($label_sistemas) ?> de la empresa?');">
                                    <i class="fas fa-minus-circle"></i> Remover
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="empresas_id" value="<?= $empresa_seleccionada ?>">
                                <input type="hidden" name="productos_id" value="<?= $s['productos_id'] ?>">
                                <input type="hidden" name="accion_liga" value="asignar">
                                <button type="submit" class="btn btn-action btn-asignar btn-sm">
                                    <i class="fas fa-plus-circle"></i> Asignar
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($sistemas) == 0): ?>
                <tr><td colspan="2" class="text-center py-4 text-muted">No hay <?= strtolower($label_sistemas) ?> registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <div class="alert alert-info">Selecciona una empresa para ver y modificar las asignaciones de <?= strtolower($label_sistemas) ?>.</div>
<?php endif;
require_once 'footergrok.php'; 
} // liga_sistema_proceso

function liga_consultor_empresa(){
// Chunk 13: liga_consultor_empresa.php
    global $link;
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
<?php endif;
require_once 'footergrok.php'; 
} // liga_consultor_empresa

?>
