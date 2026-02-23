<?php
session_start();
ob_start();

define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';


if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$deniedMessage = 'Acceso denegado. Verifique sus credenciales.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = trim((string)($_POST['usuario'] ?? ''));
    $passInput = (string)($_POST['password'] ?? '');

    if ($userInput !== '' && $passInput !== '') {
        try {
            $db = Database::getInstance();
            $sql = 'SELECT id, usuario, password, nombre_completo, rol FROM usuarios_admin WHERE usuario = :user LIMIT 1';
            $admin = $db->fetchOne($sql, ['user' => $userInput]);

            if ($admin && password_verify($passInput, (string)$admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_usuario'] = (string)$admin['usuario'];
                $_SESSION['admin_nombre'] = (string)$admin['nombre_completo'];
                $_SESSION['admin_rol'] = (string)$admin['rol'];

                session_write_close();
                header('Location: dashboard.php');
                exit;
            }

            $error = $deniedMessage;
        } catch (Throwable $e) {
            die('ERROR TECNICO: ' . $e->getMessage());
        }
    } else {
        $error = $deniedMessage;
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER - Acceso Administrativo</title>
    <style>
        :root {
            --aliser-green: #256737;
            --aliser-sand: #ECD4A8;
            --bg: #FFFFFF;
            --text: #1f2a23;
            --soft-border: rgba(37, 103, 55, 0.2);
            --soft-shadow: 0 14px 35px rgba(37, 103, 55, 0.16);
            --error-soft: #cc4b48;
            --field-bg: #fdfdfd;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 390px;
            background: #fff;
            border: 1px solid var(--soft-border);
            border-top: 4px solid var(--aliser-green);
            border-radius: 14px;
            padding: 30px 28px 24px;
            box-shadow: var(--soft-shadow);
        }

        .brand-line {
            height: 3px;
            width: 58px;
            background: var(--aliser-sand);
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .login-title {
            margin: 0 0 6px;
            font-size: 1.55rem;
            color: var(--aliser-green);
            letter-spacing: 0.02em;
        }

        .title-accent {
            color: #b39a67;
        }

        .login-subtitle {
            margin: 0 0 18px;
            font-size: 0.92rem;
            color: #57665c;
        }

        .form-group {
            margin-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #3d4a41;
        }

        input {
            width: 100%;
            border: 1px solid #d8e1db;
            border-radius: 10px;
            padding: 11px 12px;
            background: var(--field-bg);
            color: #1f2a23;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus {
            border-color: var(--aliser-green);
            box-shadow: 0 0 0 3px rgba(37, 103, 55, 0.14);
        }

        button {
            width: 100%;
            margin-top: 8px;
            border: 1px solid var(--aliser-green);
            border-radius: 10px;
            padding: 11px 14px;
            background: var(--aliser-green);
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.08s ease;
        }

        button:hover {
            background: #1f5a30;
        }

        button:active {
            transform: translateY(1px);
        }

        .error {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 9px;
            border: 1px solid rgba(204, 75, 72, 0.25);
            background: #fff2f2;
            color: var(--error-soft);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-line"></div>
        <h1 class="login-title">ALISER <span class="title-accent">Admin</span></h1>
        <p class="login-subtitle">Acceso seguro al panel de control</p>

        <form method="POST" action="index.php" autocomplete="off">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contrasena" required>
            </div>

            <button type="submit">Entrar</button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>




