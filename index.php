<?php
session_start();
// Modelo: Claude Sonnet 4.6
// Licencia GPL
// Módulo: Index / Punto de entrada
require_once 'headergrok.php';
?>
<link rel="stylesheet" href="ticketsgrok.css">
<div id="subcontainer">
  <div class="container-fluid pt-3">

    <div class="row mb-4">
      <div class="col-12">
        <h4 class="text-primary">
          <i class="fas fa-tachometer-alt mr-2"></i>Bienvenido, <?php echo htmlspecialchars($_SESSION['users_name'] ?? $session_usuario); ?>
        </h4>
        <hr>
      </div>
    </div>

    <div class="row">

      <!-- Nuevo Ticket -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">
            <i class="fas fa-plus-circle fa-3x text-success mb-3"></i>
            <h5 class="card-title">Nuevo Ticket</h5>
            <p class="card-text text-muted">Abrir una nueva solicitud de soporte.</p>
            <a href="nuevo_ticket.php" class="btn btn-success btn-block">
              <i class="fas fa-plus mr-1"></i> Abrir Ticket
            </a>
          </div>
        </div>
      </div>

      <!-- Tickets Abiertos -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">
            <i class="fas fa-folder-open fa-3x text-warning mb-3"></i>
            <h5 class="card-title">Tickets Abiertos</h5>
            <p class="card-text text-muted">Ver tickets pendientes y en proceso.</p>
            <a href="tickets_abiertos.php" class="btn btn-warning btn-block">
              <i class="fas fa-list mr-1"></i> Ver Abiertos
            </a>
          </div>
        </div>
      </div>

      <!-- Tickets Cerrados -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">
            <i class="fas fa-folder fa-3x text-secondary mb-3"></i>
            <h5 class="card-title">Tickets Cerrados</h5>
            <p class="card-text text-muted">Consultar el historial de tickets resueltos.</p>
            <a href="tickets_cerrados.php" class="btn btn-secondary btn-block">
              <i class="fas fa-archive mr-1"></i> Ver Cerrados
            </a>
          </div>
        </div>
      </div>

    </div>

    <?php if (isset($_SESSION['users_admin']) && in_array($_SESSION['users_admin'], ['Admin', 'Consultor'])): ?>
    <div class="row">

      <!-- Dashboard -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">
            <i class="fas fa-chart-bar fa-3x text-primary mb-3"></i>
            <h5 class="card-title">Dashboard General</h5>
            <p class="card-text text-muted">Vista global con filtros multidimensionales.</p>
            <a href="dashboard.php" class="btn btn-primary btn-block">
              <i class="fas fa-tachometer-alt mr-1"></i> Ir al Dashboard
            </a>
          </div>
        </div>
      </div>

      <!-- Reporte de Minutos -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">
            <i class="fas fa-clock fa-3x text-info mb-3"></i>
            <h5 class="card-title">Reporte de Minutos</h5>
            <p class="card-text text-muted">Consumo de horas por cliente o por consultor.</p>
            <a href="reporte_minutos.php" class="btn btn-info btn-block">
              <i class="fas fa-stopwatch mr-1"></i> Ver Reporte
            </a>
          </div>
        </div>
      </div>

    </div>
    <?php endif; ?>

  </div>
</div>
<?php require_once 'footergrok.php'; ?>
