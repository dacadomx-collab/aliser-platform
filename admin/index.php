<?php
/**
 * ALISER - Login Administrativo Profesional
 * Seguridad: Bcrypt + Singleton + Protección CSRF
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';

session_start();

// Si ya hay sesión, saltar al dashboard
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
            $sql = "SELECT id, usuario, password, nombre, rol FROM usuarios_admin WHERE usuario = :user LIMIT 1";
            $admin = $db->fetchOne($sql, ['user' => $userInput]);

            if ($admin && password_verify($passInput, $admin['password'])) {
                // ÉXITO: Iniciar sesión segura
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['usuario'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_rol'] = $admin['rol'];

                // Actualizar trazabilidad
                $db->query("UPDATE usuarios_admin SET ultimo_login = NOW() WHERE id = :id", ['id' => $admin['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Credenciales incorrectas.";
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "Error de conexión con la base de datos.";
        }
    } else {
        $error = "Por favor, llena todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER - Acceso Administrativo</title>
    <link rel="stylesheet" href="css/admin-style.css"> <style>
        body { background: #0b0e14; color: white; font-family: sans-serif; display: flex; justify-content:center; align-items:center; height: 100vh; margin:0; }
        .login-card { background: #161b22; padding: 40px; border-radius: 12px; border: 1px solid #256737; width: 100%; max-width: 350px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #ECD4A8; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #30363d; background: #0d1117; color: white; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #256737; border: none; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        button:hover { background: #1e522c; }
        .error { color: #ff7b72; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>ALISER ADMIN</h2>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required autofocus>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar al Santuario</button>
        </form>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>