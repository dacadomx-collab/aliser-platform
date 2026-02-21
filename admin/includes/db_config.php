<?php
// ALISER 2026 - Configuración de Conexión Segura
// Este archivo NO se sincroniza con GitHub para proteger credenciales.

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['HTTP_HOST'] == 'localhost') {
    // CONFIGURACIÓN LOCAL (XAMPP ACADEP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'aliser_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // CONFIGURACIÓN PRODUCCIÓN (GREENGEEKS)
    define('DB_HOST', 'localhost'); // Usualmente localhost en cPanel
    define('DB_NAME', 'u12345_aliser'); // Reemplaza con el nombre real en cPanel
    define('DB_USER', 'u12345_admin'); // Reemplaza con el usuario real en cPanel
    define('DB_PASS', 'w;h-h&)YTTm0q)9R'); 
}
// Colores Corporativos ALISER para uso en constantes si fuera necesario
define('COLOR_VERDE', '#256737');
define('COLOR_ARENA', '#ECD4A8');
?>