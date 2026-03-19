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

// Modelo: Grok (xAI) - Chunk 20: dashboard.php

require_once 'headergrok.php';

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
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
