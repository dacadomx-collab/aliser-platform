<?php
/**
 * ALISER - Panel de Administración
 * Módulo de Acceso (Login)
 * 
 * @package ALISER
 * @version 1.0.0
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está autenticado, redirigir al dashboard (futuro)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Definir constante antes de incluir db.php
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

// Incluir archivo de conexión a base de datos
require_once __DIR__ . '/includes/db.php';

// Procesar formulario de login
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar y validar datos de entrada
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : ''; // Trim también en password para evitar espacios accidentales
    
    // Validar que los campos no estén vacíos después del trim
    if (empty($username) || empty($password)) {
        $error_message = 'Por favor, completa todos los campos.';
    } else {
        try {
            // Obtener instancia de la base de datos
            $db = getDB();
            
            // Consulta preparada para buscar el usuario (case-sensitive exacto)
            $sql = "SELECT id, nombre_completo, usuario, password, email, rol, activo 
                    FROM usuarios_admin 
                    WHERE usuario = :usuario AND activo = 1 
                    LIMIT 1";
            
            // Ejecutar consulta con prepared statement
            $user = $db->fetchOne($sql, ['usuario' => $username]);
            
            // Debug temporal (remover en producción)
            if (!$user) {
                error_log("Usuario no encontrado: " . $username);
            } else {
                error_log("Usuario encontrado: " . $user['usuario']);
                error_log("Hash en BD: " . substr($user['password'], 0, 20) . "...");
            }
            
            // Verificar si el usuario existe
            if (!$user) {
                $error_message = 'Usuario o contraseña incorrectos.';
            } else {
                // Verificar la contraseña
                $passwordHash = $user['password'];
                
                // Verificar que el hash no esté vacío
                if (empty($passwordHash)) {
                    error_log("Error: Hash de contraseña vacío para usuario: " . $username);
                    $error_message = 'Error en la configuración del usuario. Contacta al administrador.';
                } else {
                    // Verificar contraseña con password_verify
                    if (password_verify($password, $passwordHash)) {
                        // Iniciar sesión
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_nombre'] = $user['nombre_completo'];
                        $_SESSION['admin_usuario'] = $user['usuario'];
                        $_SESSION['admin_email'] = $user['email'];
                        $_SESSION['admin_rol'] = $user['rol'];
                        
                        // Actualizar último acceso
                        $updateSql = "UPDATE usuarios_admin SET ultimo_acceso = NOW() WHERE id = :id";
                        $db->query($updateSql, ['id' => $user['id']]);
                        
                        // Redirigir al dashboard
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        // Contraseña incorrecta
                        error_log("Contraseña incorrecta para usuario: " . $username);
                        $error_message = 'Usuario o contraseña incorrectos.';
                    }
                }
            }
            
        } catch (PDOException $e) {
            // Error de base de datos
            error_log('Error de autenticación: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $error_message = 'Error al procesar la solicitud. Por favor, intenta nuevamente.';
        } catch (Exception $e) {
            // Error general
            error_log('Error inesperado: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $error_message = 'Ha ocurrido un error inesperado. Por favor, contacta al administrador.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    
    <title>ALISER - Panel de Administración</title>
    
    <!-- Estilos del Panel de Administración -->
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header con Logo -->
            <div class="login-header">
                <img 
                    src="../frontend/assets/img/logo/logo.png" 
                    alt="ALISER - Panel de Administración" 
                    class="login-logo"
                >
                <h1 class="login-title">Panel de Administración</h1>
                <p class="login-subtitle">Acceso exclusivo para personal autorizado</p>
            </div>

            <!-- Formulario de Login -->
            <form class="login-form" method="POST" action="" autocomplete="off">
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <strong>⚠️</strong> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="success-message">
                        <strong>✓</strong> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Campo Usuario -->
                <div class="form-group">
                    <label for="username" class="form-label">Usuario</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Ingresa tu usuario"
                        required
                        autocomplete="username"
                        aria-required="true"
                        aria-label="Campo de usuario"
                    >
                </div>

                <!-- Campo Contraseña -->
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Ingresa tu contraseña"
                        required
                        autocomplete="current-password"
                        aria-required="true"
                        aria-label="Campo de contraseña"
                    >
                </div>

                <!-- Botón de Login -->
                <button type="submit" class="login-btn">
                    <span class="btn-text">Acceder</span>
                    <span class="btn-glow"></span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p class="login-footer-text">
                    Sistema de administración <strong>ALISER</strong><br>
                    <small style="font-size: 0.75rem; color: var(--color-gray);">
                        © <?php echo date('Y'); ?> Todos los derechos reservados
                    </small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
