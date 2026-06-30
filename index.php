<?php
session_start();
// Modelo: Kimi 2 web.6
// Licencia GPL
// Módulo: Index / Punto de entrada
// require_once 'headergrok.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="4;url=dashboard.php">
    <title>Redirigiendo...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .mensaje {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .mensaje h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .mensaje p {
            color: #666;
            font-size: 16px;
        }
        .spinner {
            margin: 20px auto;
            width: 40px;
            height: 40px;
            border: 4px solid #e0e0e0;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="mensaje">
        <div class="spinner"></div>
        <h1>Esta es una versión temporal del index</h1>
        <p>Serás redirigido al dashboard en 4 segundos...</p>
    </div>
</body>
</html>
