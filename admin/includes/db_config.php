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
    define('DB_HOST', 'localhost'); 
    define('DB_NAME', 'tecnidepot_aliser'); 
    define('DB_USER', 'tecnidepot_aliserDB'); 
    define('DB_PASS', '0l@{F0w?cRS$w&nN'); 
}
// Colores Corporativos ALISER para uso en constantes si fuera necesario
define('COLOR_VERDE', '#256737');
define('COLOR_ARENA', '#ECD4A8');
?>