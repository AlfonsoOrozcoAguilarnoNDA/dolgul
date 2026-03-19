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

// Modelo: Grok (xAI) - Chunk 15: nuevo_ticket.php

require_once 'headergrok.php';

$rol = $_SESSION['user_role'] ?? 'User';
$empresa_usuario = (int)($_SESSION['empresa_id'] ?? 1);
$es_admin = ($rol === 'Admin');
$es_consultor = ($rol === 'Consultor');
$es_master = ($rol === 'Master');

// Empresas disponibles según rol
if ($es_admin) {
    $empresas_q = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas ORDER BY empresas_name");
} elseif ($es_consultor) {
    $empresas_q = mysqli_query($link, "SELECT e.empresas_id, e.empresas_name 
                                       FROM cat_empresas e 
                                       INNER JOIN consultoresempresas ce ON e.empresas_id = ce.empresas_id 
                                       WHERE ce.users_id = " . (int)$_SESSION['user_id'] . " ORDER BY e.empresas_name");
} else {
    // Master o User: solo su empresa
    $empresas_q = mysqli_query($link, "SELECT empresas_id, empresas_name FROM cat_empresas WHERE empresas_id = $empresa_usuario");
}

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresas_id      = (int)$_POST['empresas_id'];
    $productos_id     = (int)$_POST['productos_id'];
    $subproductos_id  = (int)$_POST['subproductos_id'];
    $prioridades_id   = (int)$_POST['prioridades_id'];
    $tickets_categories_id = (int)$_POST['tickets_categories_id'];
    $tickets_subject  = trim($_POST['tickets_subject'] ?? '');
    $tickets_question = trim($_POST['tickets_question'] ?? '');

    if ($empresas_id > 0 && $productos_id > 0 && $subproductos_id > 0 && $prioridades_id > 0 &&
        $tickets_categories_id > 0 && $tickets_subject !== '' && $tickets_question !== '') {

        // Calcular consecutivo por empresa
        $res_max = mysqli_query($link, "SELECT MAX(tickets_idempresa) AS max_id FROM tickets_tickets WHERE empresas_id = $empresas_id");
        $max_row = mysqli_fetch_assoc($res_max);
        $tickets_idempresa = ($max_row['max_id'] ?? 0) + 1;

        $now_mx = mysqli_fetch_assoc(mysqli_query($link, "SELECT CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City') AS fecha"))['fecha'];

        $stmt = mysqli_prepare($link, "INSERT INTO tickets_tickets 
            (tickets_idempresa, empresas_id, productos_id, subproductos_id, tickets_username, tickets_subject, 
             tickets_timestamp, tickets_status, tickets_name, tickets_email, prioridades_id, tickets_categories_id, 
             tickets_escritopor, tickets_child, tickets_question) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Open', ?, ?, ?, ?, ?, 0, ?)");
        
        $username = $_SESSION['user_name'] ?? 'system';
        $name = $_SESSION['user_name'] ?? 'Usuario';
        $email = $_SESSION['user_email'] ?? '';  // si lo tienes en sesión

        mysqli_stmt_bind_param($stmt, 'iiiisssssssis', 
            $tickets_idempresa, $empresas_id, $productos_id, $subproductos_id, $username, 
            $tickets_subject, $now_mx, $name, $email, $prioridades_id, $tickets_categories_id, 
            $rol === 'Client' ? 'Client' : 'Consultor', $tickets_question);

        if (mysqli_stmt_execute($stmt)) {
            $nuevo_id = mysqli_insert_id($link);
            mysqli_stmt_close($stmt);
            header("Location: ver_ticket.php?id=" . $nuevo_id);
            exit;
        }
    }
}
?>

<h2 class="mb-4">Nuevo Ticket</h2>

<form method="post" class="card p-4">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Empresa</label>
            <select name="empresas_id" class="form-control" required>
                <?php while ($e = mysqli_fetch_assoc($empresas_q)): ?>
                    <option value="<?php echo $e['empresas_id']; ?>" <?php echo ($empresa_usuario == $e['empresas_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($e['empresas_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group col-md-6">
            <label><?php echo htmlspecialchars($label_sistemas); ?></label>
            <select name="productos_id" id="productos_id" class="form-control" required>
                <option value="">— Selecciona —</option>
                <!-- Se llenará con JS o recarga -->
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label><?php echo htmlspecialchars($label_procesos); ?></label>
            <select name="subproductos_id" id="subproductos_id" class="form-control" required>
                <option value="">— Selecciona primero un <?php echo strtolower($label_sistemas); ?> —</option>
            </select>
        </div>

        <div class="form-group col-md-3">
            <label>Prioridad</label>
            <select name="prioridades_id" class="form-control" required>
                <?php
                $prios = mysqli_query($link, "SELECT prioridades_id, prioridades_name FROM cat_prioridades ORDER BY prioridades_order");
                while ($p = mysqli_fetch_assoc($prios)):
                ?>
                    <option value="<?php echo $p['prioridades_id']; ?>"><?php echo htmlspecialchars($p['prioridades_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group col-md-3">
            <label>Categoría</label>
            <select name="tickets_categories_id" class="form-control" required>
                <?php
                $cats = mysqli_query($link, "SELECT tickets_categories_id, tickets_categories_name FROM cat_categorias ORDER BY tickets_categories_order");
                while ($c = mysqli_fetch_assoc($cats)):
                ?>
                    <option value="<?php echo $c['tickets_categories_id']; ?>"><?php echo htmlspecialchars($c['tickets_categories_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Asunto</label>
        <input type="text" name="tickets_subject" class="form-control" maxlength="100" required>
    </div>

    <div class="form-group">
        <label>Mensaje / Descripción</label>
        <textarea name="tickets_question" class="form-control" rows="8" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Abrir Ticket</button>
</form>

<?php require_once 'footergrok.php'; ?>
