<?php
define('ALISER_ADMIN', true);
require_once 'includes/db.php';

$usuario = 'admin'; 
$nueva_pass = 'Aliser2026!'; // ASEGÚRATE DE USAR ESTA
$hash = password_hash($nueva_pass, PASSWORD_BCRYPT);

try {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("UPDATE usuarios_admin SET password = ? WHERE usuario = ?");
    $stmt->execute([$hash, $usuario]);

    if ($stmt->rowCount() > 0) {
        echo "✅ Password de 'admin' actualizada con Seguridad Oro.";
    } else {
        echo "⚠️ No se encontró el usuario o la password ya era la misma.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}