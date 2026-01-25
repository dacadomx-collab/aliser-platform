<?php
/**
 * ALISER - Script de Prueba de Contraseña
 * Genera y verifica hash de contraseña
 * 
 * USO: Ejecutar desde navegador o línea de comandos
 */

// Contraseña de prueba
$password = 'Admin123!';

echo "========================================\n";
echo "ALISER - Test de Hash de Contraseña\n";
echo "========================================\n\n";

// Generar nuevo hash
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "Contraseña: " . $password . "\n";
echo "Hash generado: " . $hash . "\n\n";

// Verificar que el hash funciona
if (password_verify($password, $hash)) {
    echo "✓ Hash verificado correctamente\n\n";
} else {
    echo "✗ Error: El hash no es válido\n\n";
}

// Probar con el hash del SQL
$sqlHash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy';
echo "========================================\n";
echo "Verificando hash del SQL:\n";
echo "Hash: " . $sqlHash . "\n";

if (password_verify($password, $sqlHash)) {
    echo "✓ El hash del SQL es VÁLIDO para 'Admin123!'\n";
} else {
    echo "✗ El hash del SQL NO es válido para 'Admin123!'\n";
    echo "Generando nuevo hash...\n\n";
    echo "NUEVO HASH PARA SQL:\n";
    echo $hash . "\n";
    echo "\nActualiza este hash en sql/database.sql\n";
}

echo "========================================\n";
