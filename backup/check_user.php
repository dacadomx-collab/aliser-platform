<?php
define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = Database::getInstance();
    $row = $db->fetchOne("SELECT * FROM usuarios_admin WHERE usuario = 'admin' LIMIT 1");

    header('Content-Type: text/plain; charset=utf-8');

    echo "=== CHECK USER ALISER ===\n";
    echo 'session_status(): ' . session_status() . "\n";

    if (!$row) {
        echo "Usuario 'admin' no encontrado.\n";
        exit;
    }

    $passwordOk = password_verify('Aliser2026!', (string)$row['password']);

    echo 'Usuario: ' . (string)$row['usuario'] . "\n";
    echo 'Rol: ' . (string)($row['rol'] ?? '') . "\n";
    echo 'password_verify(Aliser2026!): ' . ($passwordOk ? 'TRUE' : 'FALSE') . "\n";
} catch (Throwable $e) {
    error_log('check_user.php error: ' . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error al consultar usuario admin.';
}
