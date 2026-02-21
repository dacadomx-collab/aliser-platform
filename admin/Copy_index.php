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
require_once __DIR__ . '/includes/auth.php';

// Procesar formulario de login
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar y validar datos de entrada
    $usuario = isset($_POST['usuario']) ? trim((string) $_POST['usuario']) : '';
    $password_ingresada = isset($_POST['password']) ? (string) $_POST['password'] : '';
    
    // Validar que los campos no estén vacíos después del trim
    if ($usuario === '' || $password_ingresada === '') {
        $error_message = 'Por favor, completa todos los campos.';
    } else {
        try {
            // Obtener instancia de la base de datos
            $db = Database::getInstance()->getConnection();
            
            // Consulta preparada para buscar el usuario (case-sensitive exacto)
            $sql = "SELECT id, usuario, password, rol
                    FROM usuarios_admin
                    WHERE usuario = :usuario
                    LIMIT 1";
            
            // Ejecutar consulta con prepared statement
            $stmt = $db->prepare($sql);
            $stmt->execute([':usuario' => $usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si el usuario existe
            if (!$user) {
                $error_message = 'Credenciales incorrectas. Por favor, verifica tus datos.';
            } else {
                // Verificar la contraseña
                $hash_db = (string) ($user['password'] ?? '');
                
                // Verificar que el hash no esté vacío
                if ($hash_db === '') {
                    error_log("Error: Hash de contraseña vacío para usuario: " . $usuario);
                    $error_message = 'Error en la configuración del usuario. Contacta al administrador.';
                } else {
                    if (password_verify($password_ingresada, $hash_db)) {
                        // Iniciar sesión
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_nombre'] = $user['usuario'];
                        $_SESSION['admin_usuario'] = $user['usuario'];
                        $_SESSION['admin_rol'] = normalizeRoles((string)$user['rol']);
                        
                        // Actualizar ultimo acceso con compatibilidad temporal de estructura.
                        try {
                            $updateSql = "UPDATE usuarios_admin SET ultimo_login = NOW() WHERE id = :id";
                            $updateStmt = $db->prepare($updateSql);
                            $updateStmt->execute([':id' => $user['id']]);
                        } catch (PDOException $e) {
                            error_log('No fue posible actualizar ultimo_login: ' . $e->getMessage());
                            try {
                                $legacyUpdateSql = "UPDATE usuarios_admin SET ultimo_acceso = NOW() WHERE id = :id";
                                $legacyUpdateStmt = $db->prepare($legacyUpdateSql);
                                $legacyUpdateStmt->execute([':id' => $user['id']]);
                            } catch (PDOException $legacyE) {
                                error_log('No fue posible actualizar ultimo_acceso: ' . $legacyE->getMessage());
                            }
                        }
                        
                        // Redirigir al dashboard
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $error_message = 'Credenciales incorrectas. Por favor, verifica tus datos.';
                    }
                }
            }
            
        } catch (PDOException $e) {
            // Error de base de datos
            error_log('Error de autenticación: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $error_message = 'Credenciales incorrectas. Por favor, verifica tus datos.';
        } catch (Exception $e) {
            // Error general
            error_log('Error inesperado: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $error_message = 'Credenciales incorrectas. Por favor, verifica tus datos.';
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
            <form class="login-form" method="POST" action="index.php" autocomplete="off">
                <?php if (!empty($error_message)): ?>
                    <div class="error-message error-message-shake">
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
                    <label for="usuario" class="form-label">Usuario</label>
                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        class="form-input"
                        placeholder="Ingresa tu usuario"
                        required
                        autocomplete="username"
                        aria-required="true"
                        aria-label="Campo de usuario"
                        value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>"
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
                    <small class="login-footer-note">
                        © <?php echo date('Y'); ?> Todos los derechos reservados
                    </small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
