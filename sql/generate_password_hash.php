<?php
/**
 * ALISER - Generador de Hash de Contraseña
 * Script auxiliar para generar hashes de contraseñas
 * 
 * USO: Ejecutar desde línea de comandos o navegador
 * php generate_password_hash.php
 */

// Contraseña de prueba
$password = 'Admin123!';

// Generar hash usando bcrypt (recomendado)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "========================================\n";
echo "ALISER - Generador de Hash de Contraseña\n";
echo "========================================\n\n";
echo "Contraseña: " . $password . "\n";
echo "Hash generado: " . $hash . "\n\n";
echo "Este hash puede ser usado en el INSERT del script SQL.\n";
echo "========================================\n";

// Verificar que el hash funciona
if (password_verify($password, $hash)) {
    echo "✓ Hash verificado correctamente\n";
} else {
    echo "✗ Error: El hash no es válido\n";
}
