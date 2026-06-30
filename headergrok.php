<?php
// Modelo: Grok (xAI) - Chunk 3: headergrok.php
// modificado a mano y por Kimi  por problemas de Grok
// Define variables globales y navbar
/*
 * DOLGUL - Sistema de Tickets (Vigilancia)
 * Laboratorio 4: vibecodingmexico.com
 * * Copyright (C) 2026 Alfonso Orozco Aguilar
 * * Este programa es software libre: puedes redistribuirlo y/o modificarlo 
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
// Modelo: Grok (xAI) - Chunk 3: headergrok.php
// modificado a mano y por Kimi  por problemas de Grok
// Define variables globales y navbar

$tiempo_inicio = microtime(true);
$session_usuario = 'YO';  // placeholder - se sobreescribe en login real

require_once 'config.php';
mysqli_query($link, "SET sql_mode = ''");

// Variables configurables de etiquetas (usadas en menús)
$label_sistemas = 'Sistemas';
$label_procesos = 'Procesos';

// Rol del usuario (de sesión - fallback si no hay sesión)
$rol = $_SESSION['user_role'] ?? 'User';
$es_admin     = ($rol === 'Admin');
$es_consultor = ($rol === 'Consultor' || $es_admin);
$es_master    = ($rol === 'Master');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tickets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="ticketsgrok.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="index.php"><i class="fas fa-headset mr-2"></i>Sistema de Tickets</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?php if ($es_admin): ?>
                <!-- Menú Administrador -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDrop" role="button" data-toggle="dropdown">
                        <i class="fas fa-user-tie mr-1 text-primary"></i>Admin
                    </a>
                    <div class="dropdown-menu">
                        <h6 class="dropdown-header"><i class="fas fa-book mr-2"></i>Catálogos</h6>
                        <a class="dropdown-item" href="catalogo_categorias.php"><i class="fas fa-tags mr-2 text-info"></i>Categorías</a>
                        <a class="dropdown-item" href="catalogo_prioridades.php"><i class="fas fa-exclamation-triangle mr-2 text-warning"></i>Prioridades</a>
                        <a class="dropdown-item" href="catalogo_usuarios.php"><i class="fas fa-users mr-2 text-primary"></i>Usuarios</a>
                        <a class="dropdown-item" href="catalogo_empresas.php"><i class="fas fa-building mr-2 text-secondary"></i>Empresas</a>

                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header"><i class="fas fa-sliders-h mr-2"></i>Configuración</h6>
                        <a class="dropdown-item" href="catalogo_sistemas.php"><i class="fas fa-server mr-2 text-success"></i><?php echo $label_sistemas; ?></a>
                        <a class="dropdown-item" href="catalogo_procesos.php"><i class="fas fa-project-diagram mr-2 text-purple"></i><?php echo $label_procesos; ?></a>

                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header"><i class="fas fa-link mr-2"></i>Ligas</h6>
                        <a class="dropdown-item" href="liga_sistema_empresa.php"><i class="fas fa-plug mr-2 text-dark"></i><?php echo $label_sistemas; ?> ↔ Empresa</a>
                        <a class="dropdown-item" href="liga_consultor_empresa.php"><i class="fas fa-user-tie mr-2 text-info"></i>Consultor ↔ Empresa</a>
                        <a class="dropdown-item" href="liga_sistema_proceso.php"><i class="fas fa-project-diagram mr-2 text-success"></i><?php echo $label_sistemas; ?> ↔ <?php echo $label_procesos; ?></a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reporte_minutos.php"><i class="fas fa-chart-line mr-1"></i>Reportes</a>
                </li>
            <?php endif; ?>

            <!-- Menú común a todos -->
            <li class="nav-item">
                <a class="nav-link" href="index.php"><i class="fas fa-home mr-1"></i>Inicio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="nuevo_ticket.php"><i class="fas fa-plus-circle mr-1 text-warning"></i>Nuevo Ticket</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="tickets_abiertos.php"><i class="fas fa-folder-open mr-1 text-danger"></i>Abiertos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="tickets_cerrados.php"><i class="fas fa-folder mr-1 text-success"></i>Cerrados</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-chart-pie mr-1"></i>Reportes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-balance-scale mr-1"></i>Licencia</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link text-muted small"><i class="fas fa-robot mr-1"></i>Grok (xAI)</span>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm" href="#" data-toggle="modal" data-target="#modalSalir">
                    <i class="fas fa-sign-out-alt mr-1"></i>Salir
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Modal confirmar salida -->
<div class="modal fade" id="modalSalir" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-door-open mr-2"></i>Confirmar salida</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <i class="fas fa-question-circle mr-2 text-info"></i>¿Realmente deseas cerrar sesión?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cancelar</button>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt mr-1"></i>Salir</a>
            </div>
        </div>
    </div>
</div>

<?php
/*    <!-- Jumbotron (visible solo desktop) -->
    <div class="jumbotron jumbotron-fluid d-none d-md-block bg-primary text-white mb-0">
    <div class="container">
        <h1 class="display-4"><i class="fas fa-headset mr-3"></i>Sistema de Soporte Técnico</h1>
        <p class="lead"><i class="fas fa-clipboard-list mr-2"></i>Registro y seguimiento de tickets de consultoría</p>
    </div>
</div>
*/
?>

<div id="subcontainer" class="container-fluid pt-5 pb-5">
