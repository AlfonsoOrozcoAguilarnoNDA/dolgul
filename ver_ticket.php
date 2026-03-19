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

// Modelo: Grok (xAI) - Chunk 16: ver_ticket.php

require_once 'headergrok.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);  // Permitimos GET solo aquí para enlace directo

if ($id <= 0) {
    echo '<div class="alert alert-danger">Ticket no encontrado.</div>';
    require_once 'footergrok.php';
    exit;
}

// Obtener ticket principal
$ticket = mysqli_fetch_assoc(mysqli_query($link, "SELECT t.*, p.prioridades_name, p.prioridades_color, c.tickets_categories_name, 
                                                 e.empresas_name, pr.productos_name, sp.subproductos_name 
                                          FROM tickets_tickets t 
                                          LEFT JOIN cat_prioridades p ON t.prioridades_id = p.prioridades_id 
                                          LEFT JOIN cat_categorias c ON t.tickets_categories_id = c.tickets_categories_id 
                                          LEFT JOIN cat_empresas e ON t.empresas_id = e.empresas_id 
                                          LEFT JOIN cat_productos pr ON t.productos_id = pr.productos_id 
                                          LEFT JOIN cat_subproductos sp ON t.subproductos_id = sp.subproductos_id 
                                          WHERE t.tickets_id = $id AND t.tickets_child = 0"));

if (!$ticket) {
    echo '<div class="alert alert-danger">Ticket no encontrado o no tienes acceso.</div>';
    require_once 'footergrok.php';
    exit;
}

// Respuestas ordenadas
$respuestas = mysqli_query($link, "SELECT * FROM tickets_tickets WHERE tickets_child = $id ORDER BY tickets_timestamp ASC");

// Procesar respuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
    $respuesta = trim($_POST['tickets_question'] ?? '');
    $minutos   = (int)($_POST['tickets_minutos'] ?? 0);

    if ($respuesta !== '') {
        $now_mx = mysqli_fetch_assoc(mysqli_query($link, "SELECT CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City') AS fecha"))['fecha'];
        $escritopor = ($_SESSION['user_role'] === 'Client') ? 'Client' : 'Consultor';

        $stmt = mysqli_prepare($link, "INSERT INTO tickets_tickets 
            (tickets_idempresa, empresas_id, productos_id, subproductos_id, tickets_username, tickets_subject, 
             tickets_timestamp, tickets_status, tickets_name, tickets_email, prioridades_id, tickets_categories_id, 
             tickets_escritopor, tickets_minutos, tickets_child, tickets_question) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $tickets_idempresa = $ticket['tickets_idempresa'];
        $empresas_id = $ticket['empresas_id'];
        $productos_id = $ticket['productos_id'];
        $subproductos_id = $ticket['subproductos_id'];
        $username = $_SESSION['user_name'] ?? 'system';
        $subject = 'Respuesta a ticket #' . $tickets_idempresa;
        $status = $ticket['tickets_status'];
        $name = $_SESSION['user_name'] ?? 'Usuario';
        $email = $_SESSION['user_email'] ?? '';
        $pri_id = $ticket['prioridades_id'];
        $cat_id = $ticket['tickets_categories_id'];

        mysqli_stmt_bind_param($stmt, 'iiiisssssssiiis', 
            $tickets_idempresa, $empresas_id, $productos_id, $subproductos_id, $username, $subject, 
            $now_mx, $status, $name, $email, $pri_id, $cat_id, $escritopor, $minutos, $id, $respuesta);

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Opcional: cambiar a In Progress si era Open
        if ($ticket['tickets_status'] === 'Open') {
            mysqli_query($link, "UPDATE tickets_tickets SET tickets_status = 'In Progress' WHERE tickets_id = $id");
        }

        header("Location: ver_ticket.php?id=$id");
        exit;
    }
}

// Cerrar ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar'])) {
    $now_mx = mysqli_fetch_assoc(mysqli_query($link, "SELECT CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City') AS fecha"))['fecha'];
    mysqli_query($link, "UPDATE tickets_tickets SET tickets_status = 'Closed', horacierre = '$now_mx' WHERE tickets_id = $id");
    header("Location: ver_ticket.php?id=$id");
    exit;
}
?>

<h2 class="mb-4">Ticket #<?php echo $ticket['tickets_idempresa']; ?> - <?php echo htmlspecialchars($ticket['tickets_subject']); ?></h2>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Empresa:</strong> <?php echo htmlspecialchars($ticket['empresas_name']); ?></p>
                <p><strong><?php echo htmlspecialchars($label_sistemas); ?>:</strong> <?php echo htmlspecialchars($ticket['productos_name']); ?></p>
                <p><strong><?php echo htmlspecialchars($label_procesos); ?>:</strong> <?php echo htmlspecialchars($ticket['subproductos_name']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Prioridad:</strong> <span class="badge badge-priority" style="background-color:#<?php echo htmlspecialchars($ticket['prioridades_color']); ?>;"><?php echo htmlspecialchars($ticket['prioridades_name']); ?></span></p>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($ticket['tickets_categories_name']); ?></p>
                <p><strong>Estatus:</strong> 
                    <?php
                    $status_class = ($ticket['tickets_status'] === 'Open') ? 'badge-status-open' : 
                                    (($ticket['tickets_status'] === 'In Progress') ? 'badge-status-progress' : 'badge-status-closed');
                    ?>
                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($ticket['tickets_status']); ?></span>
                </p>
                <p><strong>Apertura:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['tickets_timestamp'])); ?></p>
            </div>
        </div>
    </div>
</div>

<h4>Hilo de conversación</h4>

<?php
// Mensaje original
?>
<div class="card mb-3 border-left-primary">
    <div class="card-header bg-light">
        <strong><?php echo htmlspecialchars($ticket['tickets_name']); ?> (<?php echo $ticket['tickets_escritopor']; ?>)</strong> 
        - <?php echo date('d/m/Y H:i', strtotime($ticket['tickets_timestamp'])); ?>
    </div>
    <div class="card-body">
        <?php echo nl2br(htmlspecialchars($ticket['tickets_question'])); ?>
    </div>
</div>

<?php while ($resp = mysqli_fetch_assoc($respuestas)): ?>
    <div class="card mb-3 <?php echo ($resp['tickets_escritopor'] === 'Consultor') ? 'border-left-success' : 'border-left-info'; ?>">
        <div class="card-header bg-light">
            <strong><?php echo htmlspecialchars($resp['tickets_name']); ?> (<?php echo $resp['tickets_escritopor']; ?>)</strong> 
            - <?php echo date('d/m/Y H:i', strtotime($resp['tickets_timestamp'])); ?>
            <?php if ($resp['tickets_minutos'] > 0): ?>
                <span class="float-right">Tiempo: <?php echo $resp['tickets_minutos']; ?> min</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php echo nl2br(htmlspecialchars($resp['tickets_question'])); ?>
        </div>
    </div>
<?php endwhile; ?>

<?php if ($ticket['tickets_status'] !== 'Closed'): ?>
    <h4 class="mt-5">Responder</h4>
    <form method="post">
        <div class="form-group">
            <textarea name="tickets_question" class="form-control" rows="6" required placeholder="Escribe tu respuesta aquí..."></textarea>
        </div>

        <?php if ($es_consultor || $es_admin): ?>
            <div class="form-group">
                <label>Minutos empleados (opcional)</label>
                <input type="number" name="tickets_minutos" class="form-control" min="0" value="0" style="width:150px;">
            </div>
        <?php endif; ?>

        <button type="submit" name="responder" class="btn btn-success">Enviar Respuesta</button>

        <?php if ($es_consultor || $es_admin): ?>
            <button type="button" class="btn btn-danger float-right" data-toggle="modal" data-target="#modalCerrar">Cerrar Ticket</button>

            <div class="modal fade" id="modalCerrar">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">¿Cerrar este ticket?</h5>
                            <button type="button" class="close" data-dismiss="modal">×</button>
                        </div>
                        <div class="modal-body">
                            Esta acción es irreversible. El ticket pasará a estatus Closed.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <form method="post" style="display:inline;">
                                <button type="submit" name="cerrar" class="btn btn-danger">Cerrar Ticket</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
<?php else: ?>
    <div class="alert alert-secondary mt-4">Este ticket ya está cerrado.</div>
<?php endif; ?>

<?php require_once 'footergrok.php'; ?>
