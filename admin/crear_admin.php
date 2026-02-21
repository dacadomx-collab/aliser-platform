<?php
/**
 * ALISER - Generador de Credenciales Oro (Versión Corregida)
 */
define('ALISER_ADMIN', true);
require_once 'includes/db.php';

// Configura tus credenciales
$nuevo_usuario = 'admin'; 
$password_plana = 'Aliser2026!'; 
$nombre_real = 'Administrador Aliser';

$password_hash = password_hash($password_plana, PASSWORD_BCRYPT);

try {
    // Obtenemos la conexión PDO a través del Singleton
    $pdo = Database::getInstance()->getConnection();
    
    // 1. Verificar si ya existe usando PDO nativo
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios_admin WHERE usuario = ?");
    $stmtCheck->execute([$nuevo_usuario]);
    
    if ($stmtCheck->fetch()) {
        die("<h3 style='color:orange;'>Aviso: El usuario ya existe en la base de datos local.</h3>");
    }

    // 2. Insertar con Seguridad Oro
    $sql = "INSERT INTO usuarios_admin (usuario, password, nombre, rol, ultimo_login) 
            VALUES (:user, :pass, :nom, 'admin', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user' => $nuevo_usuario,
        'pass' => $password_hash,
        'nom'  => $nombre_real
    ]);

    echo "<h2 style='color:green;'>✅ Usuario creado con éxito en Local</h2>";
    echo "Pruébalo ahora en tu login.";

} catch (PDOException $e) {
    echo "Error de Base de Datos: " . $e->getMessage();
}