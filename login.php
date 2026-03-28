<?php
// Modelo: Grok (xAI) - Chunk 2: login.php
// Pantalla independiente (sin headergrok ni footergrok)
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

session_start();

// Configuración hardcoded
$wallpaper = 'img/fondo-login.jpg';          // Cambiar si existe o usar degradado
$logo_type = 'fa-ticket-alt';                // o 'img/logo.png' si prefieres imagen
$mensaje_dia = 'Bienvenido al Sistema de Soporte Técnico - Recuerda registrar tus minutos con precisión.';

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Usuario y contraseña son obligatorios.';
    } else {
        require_once 'config.php';  // $link disponible

        mysqli_query($link, "SET sql_mode = ''");

        $stmt = mysqli_prepare($link, "SELECT users_id, users_name, users_password, users_admin, empresas_id, users_status 
                                       FROM cat_usuarios 
                                       WHERE users_username = ? AND users_status = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['users_password'])) {
                // Login OK
                $_SESSION['user_id']    = $row['users_id'];
                $_SESSION['user_name']  = $row['users_name'];
                $_SESSION['user_role']  = $row['users_admin'];
                $_SESSION['empresa_id'] = $row['empresas_id'];

                // Actualizar último login
                $now_mx = date('Y-m-d H:i:s'); // o usar CONVERT_TZ en BD
                mysqli_query($link, "UPDATE cat_usuarios SET users_lastlogin = users_newlogin, users_newlogin = '$now_mx' 
                                     WHERE users_id = " . (int)$row['users_id']);

                header("Location: index.php");
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado o inactivo.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tickets - Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" crossorigin="anonymous">
    <style>
        body {
            background: <?php echo file_exists($wallpaper) ? "url('$wallpaper') center/cover no-repeat fixed" : "linear-gradient(135deg, #667eea 0%, #764ba2 100%)"; ?>;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .login-card {
            background: rgba(255,255,255,0.95);
            color: #333;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            padding: 2.5rem;
            max-width: 420px;
            width: 100%;
        }
        .fa-icon { font-size: 4rem; color: #007bff; margin-bottom: 1rem; }
        .alert { margin-top: 1rem; }
    </style>
</head>
<body>

<div class="login-card text-center">
    <?php if ($logo_type === 'fa-ticket-alt' || strpos($logo_type, 'fa-') === 0): ?>
        <i class="fas <?php echo $logo_type; ?> fa-icon"></i>
    <?php else: ?>
        <img src="<?php echo $logo_type; ?>" alt="Logo" style="max-height:80px; margin-bottom:1rem;">
    <?php endif; ?>

    <h3 class="mb-4">Sistema de Tickets</h3>
    <p class="text-muted mb-4"><?php echo htmlspecialchars($mensaje_dia); ?></p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="form-group">
            <input type="text" class="form-control form-control-lg" name="username" placeholder="Usuario" required autofocus>
        </div>
        <div class="form-group">
            <input type="password" class="form-control form-control-lg" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
            <i class="fas fa-sign-in-alt mr-2"></i> Ingresar
        </button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
