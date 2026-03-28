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
</html>
