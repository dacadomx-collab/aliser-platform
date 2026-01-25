<?php
/**
 * ALISER - Panel de Administración
 * Dashboard Principal
 * 
 * @package ALISER
 * @version 1.0.0
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Procesar logout
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    // Destruir todas las variables de sesión
    $_SESSION = array();
    
    // Si se desea destruir la sesión completamente, también borrar la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finalmente, destruir la sesión
    session_destroy();
    
    // Redirigir al login
    header('Location: index.php?logged_out=1');
    exit;
}

// Obtener datos del usuario de la sesión
$admin_nombre = isset($_SESSION['admin_nombre']) ? $_SESSION['admin_nombre'] : 'Usuario';
$admin_rol = isset($_SESSION['admin_rol']) ? $_SESSION['admin_rol'] : 'admin';
$admin_usuario = isset($_SESSION['admin_usuario']) ? $_SESSION['admin_usuario'] : '';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    
    <title>Dashboard - ALISER Panel de Administración</title>
    
    <!-- Estilos del Panel de Administración -->
    <link rel="stylesheet" href="css/admin-style.css">
    
    <style>
        /* Estilos específicos del Dashboard */
        .dashboard-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 3rem 2.5rem;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .dashboard-logo {
            width: 100px;
            height: auto;
            margin: 0 auto 1.5rem;
            display: block;
            filter: drop-shadow(0 4px 8px rgba(37, 103, 55, 0.2));
        }
        
        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--aliser-green-primary);
            margin-bottom: 0.5rem;
        }
        
        .dashboard-subtitle {
            font-size: 1rem;
            color: var(--color-gray);
            margin-bottom: 1.5rem;
        }
        
        .welcome-message {
            background: rgba(67, 145, 132, 0.1);
            border: 1px solid rgba(67, 145, 132, 0.3);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .welcome-text {
            font-size: 1.125rem;
            color: var(--aliser-green-primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .user-info {
            font-size: 0.95rem;
            color: var(--color-gray);
        }
        
        .user-info strong {
            color: var(--aliser-teal-tertiary);
        }
        
        .logout-btn {
            position: relative;
            padding: 0.875rem 2rem;
            background: transparent;
            border: 2px solid var(--aliser-teal-tertiary);
            color: var(--aliser-teal-tertiary);
            font-family: var(--font-family-primary);
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all var(--transition-fast);
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--aliser-teal-tertiary);
            z-index: -1;
            transition: left 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .logout-btn:hover::before,
        .logout-btn:focus::before {
            left: 0;
        }
        
        .logout-btn:hover,
        .logout-btn:focus {
            color: var(--aliser-green-primary);
            outline: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(67, 145, 132, 0.3);
        }
        
        .logout-btn .btn-text {
            position: relative;
            z-index: 1;
        }
        
        .logout-btn .btn-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(67, 145, 132, 0.5) 0%, transparent 70%);
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition-fast);
            z-index: 0;
        }
        
        .logout-btn:hover .btn-glow,
        .logout-btn:focus .btn-glow {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <!-- Header -->
            <div class="dashboard-header">
                <img 
                    src="../frontend/assets/img/logo/logo.png" 
                    alt="ALISER - Panel de Administración" 
                    class="dashboard-logo"
                >
                <h1 class="dashboard-title">Panel de Administración</h1>
                <p class="dashboard-subtitle">Sistema de gestión ALISER</p>
            </div>

            <!-- Mensaje de Bienvenida -->
            <div class="welcome-message">
                <p class="welcome-text">¡Bienvenido al Panel de ALISER!</p>
                <div class="user-info">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($admin_nombre); ?></p>
                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($admin_usuario); ?></p>
                    <p><strong>Rol:</strong> <?php echo htmlspecialchars(ucfirst($admin_rol)); ?></p>
                </div>
            </div>

            <!-- Módulos del Sistema -->
            <div class="modules-section" style="margin: 2rem 0;">
                <h2 style="font-size: 1.25rem; color: var(--aliser-green-primary); margin-bottom: 1.5rem;">Módulos</h2>
                <div class="modules-grid" style="display: grid; gap: 1rem;">
                    <a href="vacantes.php" class="module-btn" style="
                        position: relative;
                        padding: 1rem 2rem;
                        background: transparent;
                        border: 2px solid var(--aliser-teal-tertiary);
                        color: var(--aliser-teal-tertiary);
                        font-family: var(--font-family-primary);
                        font-size: 1rem;
                        font-weight: 600;
                        border-radius: 8px;
                        cursor: pointer;
                        transition: all var(--transition-fast);
                        overflow: hidden;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        text-decoration: none;
                        display: block;
                    ">
                        <span style="position: relative; z-index: 1;">📋 Gestión de Vacantes</span>
                        <span class="btn-glow"></span>
                    </a>
                </div>
            </div>

            <!-- Botón de Cerrar Sesión -->
            <a href="?logout=1" class="logout-btn" style="margin-top: 1rem;">
                <span class="btn-text">Cerrar Sesión</span>
                <span class="btn-glow"></span>
            </a>
        </div>
    </div>
</body>
</html>
