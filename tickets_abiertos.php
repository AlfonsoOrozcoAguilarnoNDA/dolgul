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

// Modelo: Grok (xAI) - Chunk 17: tickets_abiertos.php

require_once 'headergrok.php';

$rol = $_SESSION['user_role'] ?? 'User';
$empresa_usuario = (int)($_SESSION['empresa_id'] ?? 1);
$user_id = (int)($_SESSION['user_id'] ?? 0);

$where = "WHERE t.tickets_status IN ('Open', 'In Progress') AND t.tickets_child = 0 ";

if ($rol === 'Admin') {
    // ve todos
} elseif ($rol === 'Consultor') {
    $where .= " AND t.empresas_id IN (SELECT empresas_id FROM consultoresempresas WHERE users_id = $user_id) ";
} else {
    // Master/User
    $where .= " AND t.empresas_id = $empresa_usuario ";
    if ($rol === 'User') {
        $where .= " AND t.tickets_username = '" . mysqli_real_escape_string($link, $_SESSION['user_name'] ?? '') . "' ";
    }
}

$items_por_pagina = 50;
$pagina = max(1, (int)($_POST['pagina'] ?? 1));
$offset = ($pagina - 1) * $items_por_pagina;

$total_q = mysqli_query($link, "SELECT COUNT(*) total FROM tickets_tickets t $where");
$total = mysqli_fetch_assoc($total_q)['total'] ?? 0;
$paginas_total = max(1, ceil($total / $items_por_pagina));

$query = "SELECT t.tickets_id, t.tickets_idempresa, t.empresas_id, t.tickets_subject, t.tickets_timestamp, 
                 t.prioridades_id, t.tickets_status, t.tickets_username,
                 e.empresas_name, pr.productos_name, sp.subproductos_name,
                 (SELECT COUNT(*) FROM tickets_tickets r WHERE r.tickets_child = t.tickets_id) AS respuestas,
                 (SELECT SUM(tickets_minutos) FROM tickets_tickets r WHERE r.tickets_child = t.tickets_id) AS total_minutos,
                 p.prioridades_name, p.prioridades_color, c.tickets_categories_name
          FROM tickets_tickets t
          LEFT JOIN cat_empresas e ON t.empresas_id = e.empresas_id
          LEFT JOIN cat_productos pr ON t.productos_id = pr.productos_id
          LEFT JOIN cat_subproductos sp ON t.subproductos_id = sp.subproductos_id
          LEFT JOIN cat_prioridades p ON t.prioridades_id = p.prioridades_id
          LEFT JOIN cat_categorias c ON t.tickets_categories_id = c.tickets_categories_id
          $where 
          ORDER BY t.tickets_timestamp DESC 
          LIMIT $offset, $items_por_pagina";

$result = mysqli_query($link, $query);
?>

<h2 class="mb-4">Tickets Abiertos / En Progreso</h2>

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
                    <?php echo htmlspecialchars($row['tickets_username']); ?><br>
                    <?php echo htmlspecialchars($row['subproductos_name']); ?>
                </td>
                <td><?php echo htmlspecialchars($row['tickets_subject']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['tickets_timestamp'])); ?></td>
                <td><span class="badge badge-priority" style="background-color:#<?php echo htmlspecialchars($row['prioridades_color']); ?>;"><?php echo htmlspecialchars($row['prioridades_name']); ?></span></td>
                <td><?php echo htmlspecialchars($row['tickets_categories_name']); ?></td>
                <td>
                    <?php
                    $status_class = ($row['tickets_status'] === 'Open') ? 'badge-status-open' : 'badge-status-progress';
                    ?>
                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['tickets_status']); ?></span>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($result) == 0): ?>
            <tr><td colspan="10" class="text-center py-4 text-muted">No hay tickets abiertos en este momento.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($paginas_total > 1): ?>
<nav aria-label="Paginación">
    <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $paginas_total; $p++): ?>
            <li class="page-item <?php echo ($p == $pagina) ? 'active' : ''; ?>">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="pagina" value="<?php echo $p; ?>">
                    <button type="submit" class="page-link"><?php echo $p; ?></button>
                </form>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
