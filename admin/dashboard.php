<?php
/**
 * ALISER - Panel de Administracion
 * Dashboard Principal
 *
 * @package ALISER
 * @version 1.0.0
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    header('Location: index.php?logged_out=1');
    exit;
}

$admin_nombre = isset($_SESSION['admin_nombre']) ? $_SESSION['admin_nombre'] : 'Usuario';
$admin_roles = getCurrentAdminRoles();
$admin_usuario = isset($_SESSION['admin_usuario']) ? $_SESSION['admin_usuario'] : '';

$modulePermissions = [
    'vacantes.php' => ['MASTER', 'TALENTO'],
    'terrenos.php' => ['MASTER', 'BIENES'],
    'promociones.php' => ['MASTER', 'PROMO', 'MARCA'],
    'usuarios.php' => ['MASTER']
];

function canAccessModule(string $module, array $roles, array $permissions): bool
{
    if (!isset($permissions[$module])) return false;
    foreach ($roles as $r) {
        if (in_array($r, $permissions[$module], true)) return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard - ALISER Panel de Administracion</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <img
                    src="../frontend/assets/img/logo/logo.png"
                    alt="ALISER - Panel de Administracion"
                    class="dashboard-logo"
                >
                <h1 class="dashboard-title">Panel de Administracion</h1>
                <p class="dashboard-subtitle">Sistema de gestion ALISER</p>
            </div>

            <div class="welcome-message">
                <p class="welcome-text">Bienvenido al panel de ALISER</p>
                <div class="user-info">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($admin_nombre); ?></p>
                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($admin_usuario); ?></p>
                    <p><strong>Rol:</strong> <?php echo htmlspecialchars(implode(', ', $admin_roles)); ?></p>
                </div>
            </div>

            <div class="modules-section">
                <h2 class="modules-title">Modulos</h2>
                <div class="modules-grid">
                    <?php if (canAccessModule('vacantes.php', $admin_roles, $modulePermissions)): ?>
                        <div class="module-card">
                            <a href="vacantes.php" class="login-btn">
                                <span class="btn-text">Gestion de Vacantes</span>
                                <span class="btn-glow"></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (canAccessModule('terrenos.php', $admin_roles, $modulePermissions)): ?>
                        <div class="module-card">
                            <a href="terrenos.php" class="login-btn">
                                <span class="btn-text">Gestion de Terrenos</span>
                                <span class="btn-glow"></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (canAccessModule('promociones.php', $admin_roles, $modulePermissions)): ?>
                        <div class="module-card">
                            <a href="promociones.php" class="login-btn">
                                <span class="btn-text">&#127915; Promociones y Cupones</span>
                                <span class="btn-glow"></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (canAccessModule('usuarios.php', $admin_roles, $modulePermissions)): ?>
                        <div class="module-card">
                            <a href="usuarios.php" class="login-btn">
                                <span class="btn-text">Usuarios y Roles</span>
                                <span class="btn-glow"></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <a href="?logout=1" class="login-btn">
                <span class="btn-text">Cerrar Sesion</span>
                <span class="btn-glow"></span>
            </a>
        </div>
    </div>
</body>
</html>
