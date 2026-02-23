<?php
define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';

if (($_GET['key'] ?? '') !== 'aliser_gold') {
    die('Acceso no autorizado');
}

try {
    $passwordHash = password_hash('Aliser2026!', PASSWORD_BCRYPT);

    if ($passwordHash === false) {
        throw new RuntimeException('No se pudo generar el hash de la contrasena.');
    }

    $db = Database::getInstance();
    $sql = 'UPDATE usuarios_admin SET password = :password, rol = :rol WHERE id = :id OR usuario = :usuario';
    $db->query($sql, [
        'password' => $passwordHash,
        'rol' => 'MASTER',
        'id' => 1,
        'usuario' => 'admin',
    ]);

    echo '✅ Contraseña actualizada con éxito. BORRE ESTE ARCHIVO INMEDIATAMENTE.';
} catch (Throwable $e) {
    error_log('reset_password.php error: ' . $e->getMessage());
    die('Error al actualizar la contrasena.');
}
