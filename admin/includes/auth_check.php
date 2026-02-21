<?php
session_start();
require_once 'config.php';

// Si el usuario ya está logueado, no hacemos nada
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    return; 
}

// Si intentan loguearse
if (isset($_POST['admin_pass'])) {
    $pass_introducida = $_POST['admin_pass'];
    $hash_almacenado = $_ENV['ADMIN_PASS_HASH'];

    if (password_verify($pass_introducida, $hash_almacenado)) {
        // ÉXITO: Creamos la sesión
        $_SESSION['admin_auth'] = true;
        header("Location: " . $_SERVER['PHP_SELF']); // Recarga la página actual ya logueado
        exit;
    } else {
        // FALLO: Registrar en log y mandar a troll
        $log_error = "[" . date("Y-m-d H:i:s") . "] Intento de login fallido desde: " . $_SERVER['REMOTE_ADDR'] . "\n";
        file_put_contents("seguridad.log", $log_error, FILE_APPEND);
        header('Location: troll.php');
        exit;
    }
}

// Si no está logueado y no ha enviado el formulario, mostramos el login
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Protocolo ORO</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body style="background:#0b0e14; display:flex; justify-content:center; align-items:center; height:100vh;">
    <form method="POST" style="background:#161b22; padding:30px; border:1px solid #7000FF; border-radius:10px; text-align:center;">
        <h2 style="color:#00D1FF;">SISTEMA ALISER</h2>
        <p style="color:#888;">Ingresa la Clave Maestra</p>
        <input type="password" name="admin_pass" required style="background:#000; color:#fff; border:1px solid #333; padding:10px; width:200px; margin-bottom:20px;">
        <br>
        <button type="submit" style="background:#7000FF; color:#white; border:none; padding:10px 20px; cursor:pointer; font-weight:bold;">ACCEDER</button>
    </form>
</body>
</html>
<?php exit; // Detiene la carga del resto de la página si no está logueado ?>