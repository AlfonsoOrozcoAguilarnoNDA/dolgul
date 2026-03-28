<?php
// Modelo: Grok (xAI) - Chunk 3: headergrok.php
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
    <a class="navbar-brand" href="index.php">Sistema de Tickets</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?php if ($es_admin): ?>
                <!-- Menú Administrador -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="catDrop" role="button" data-toggle="dropdown">Catálogos</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="catalogo_categorias.php">Categorías</a>
                        <a class="dropdown-item" href="catalogo_prioridades.php">Prioridades</a>
                        <a class="dropdown-item" href="catalogo_usuarios.php">Usuarios</a>
                        <a class="dropdown-item" href="catalogo_empresas.php">Empresas</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="configDrop" role="button" data-toggle="dropdown">Configuración</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="catalogo_sistemas.php"><?php echo $label_sistemas; ?></a>
                        <a class="dropdown-item" href="catalogo_procesos.php"><?php echo $label_procesos; ?></a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="ligasDrop" role="button" data-toggle="dropdown">Ligas</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="liga_sistema_empresa.php"><?php echo $label_sistemas; ?> ↔ Empresa</a>
                        <a class="dropdown-item" href="liga_consultor_empresa.php">Consultor ↔ Empresa</a>
                        <a class="dropdown-item" href="liga_sistema_proceso.php"><?php echo $label_sistemas; ?> ↔ <?php echo $label_procesos; ?></a>
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="reporte_minutos.php">Reportes</a></li>
            <?php endif; ?>

            <!-- Menú común a todos -->
            <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="nuevo_ticket.php">Nuevo Ticket</a></li>
            <li class="nav-item"><a class="nav-link" href="tickets_abiertos.php">Abiertos</a></li>
            <li class="nav-item"><a class="nav-link" href="tickets_cerrados.php">Cerrados</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Reportes</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Licencia</a></li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link text-muted small">Grok (xAI)</span>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm" href="#" data-toggle="modal" data-target="#modalSalir">
                    <i class="fas fa-sign-out-alt"></i> Salir
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
                <h5 class="modal-title">Confirmar salida</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Realmente deseas cerrar sesión?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </div>
</div>

<!-- Jumbotron (visible solo desktop) -->
<div class="jumbotron jumbotron-fluid d-none d-md-block bg-primary text-white mb-0">
    <div class="container">
        <h1 class="display-4">Sistema de Soporte Técnico</h1>
        <p class="lead">Registro y seguimiento de tickets de consultoría</p>
    </div>
</div>

<div id="subcontainer" class="container-fluid pt-5 pb-5">
