<?php
// ALISER - DEPURACIÓN DE EMERGENCIA
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prueba de vida
// echo "El script se está ejecutando...";
/**
 * ALISER - Login Administrativo (Seguridad Oro)
 * VERIFICACIÓN: Uso de Singleton y Bcrypt.
 */
define('ALISER_ADMIN', true);
require_once 'includes/db.php';
session_start();

// Si ya está logueado, saltar al dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = trim($_POST['usuario'] ?? '');
    $passInput = $_POST['password'] ?? '';

    if (!empty($userInput) && !empty($passInput)) {
        try {
            $db = Database::getInstance();
            // Consulta preparada: Única forma de prevenir SQL Injection
            $sql = "SELECT id, usuario, password, nombre, rol FROM usuarios_admin WHERE usuario = :user LIMIT 1";
            $admin = $db->fetchOne($sql, ['user' => $userInput]);

            if ($admin && password_verify($passInput, $admin['password'])) {
                // ÉXITO: Generar Sesión Segura
                session_regenerate_id(true); // Previene fijación de sesión
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['usuario'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_rol'] = $admin['rol'];

                // Trazabilidad: Actualizar último login
                $db->query("UPDATE usuarios_admin SET ultimo_login = NOW() WHERE id = :id", ['id' => $admin['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Credenciales incorrectas.";
                // Log de seguridad (Intento fallido)
                error_log("ALISER_AUTH: Intento fallido para usuario: " . $userInput . " desde " . $_SERVER['REMOTE_ADDR']);
            }
        } catch (Exception $e) {
            error_log("ALISER_AUTH_ERROR: " . $e->getMessage());
            $error = "Error de sistema. Intente más tarde.";
        }
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}
?>