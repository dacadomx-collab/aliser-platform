<?php
// ALISER - DIAGNÓSTICO DE EMERGENCIA
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ALISER_ADMIN', true);

echo "Buscando db.php en: " . __DIR__ . '/admin/includes/db.php <br>';

if (file_exists(__DIR__ . '/admin/includes/db.php')) {
    require_once __DIR__ . '/admin/includes/db.php';
    echo "✅ Archivo de conexión encontrado.<br>";
} else {
    die("❌ ERROR: No se encuentra el archivo admin/includes/db.php en esta ruta.");
}

$usuario = 'admin'; 
$nueva_pass = 'Aliser2026!'; 
$hash = password_hash($nueva_pass, PASSWORD_BCRYPT);

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("UPDATE usuarios_admin SET password = ? WHERE usuario = ?");
    $stmt->execute([$hash, $usuario]);

    if ($stmt->rowCount() > 0) {
        echo "<h2>✅ ÉXITO: Password actualizada.</h2>";
    } else {
        echo "⚠️ AVISO: El usuario no existe o la contraseña ya era la misma.";
    }
} catch (Exception $e) {
    echo "❌ ERROR DE BD: " . $e->getMessage();
}