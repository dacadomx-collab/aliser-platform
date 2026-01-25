<?php
/**
 * ALISER - Generador de Hash Válido
 * Genera un hash válido para Admin123!
 */

$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "========================================\n";
echo "ALISER - Hash para Admin123!\n";
echo "========================================\n\n";
echo "Contraseña: " . $password . "\n";
echo "Hash: " . $hash . "\n\n";

// Verificar
if (password_verify($password, $hash)) {
    echo "✓ Hash verificado correctamente\n\n";
    echo "========================================\n";
    echo "Copia este hash y actualiza sql/database.sql:\n";
    echo "========================================\n";
    echo $hash . "\n";
} else {
    echo "✗ Error generando hash\n";
}
