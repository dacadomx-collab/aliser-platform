<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>--- PRUEBA DE TÚNEL ALISER ---</h3>";

// PRUEBA 1: Conexión Manual Directa
$h = '127.0.0.1';
$d = 'tecnidepot_aliser';
$u = 'tecnidepot_aliserDB';
$p = '0l@{F0w?cRS$w&nN';

echo "Probando conexión manual a $d...<br>";

try {
    $pdo = new PDO("mysql:host=$h;dbname=$d;charset=utf8mb4", $u, $p);
    echo "✅ CONEXIÓN MANUAL: EXITOSA.<br>";
} catch (PDOException $e) {
    echo "❌ ERROR MANUAL: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// PRUEBA 2: Carga de Clases
echo "Probando carga de db.php...<br>";
if (file_exists('includes/db.php')) {
    require_once 'includes/db.php';
    echo "✅ Archivo db.php encontrado.<br>";
    try {
        $db = Database::getInstance()->getConnection();
        echo "✅ SINGLETON: CONECTADO.<br>";
    } catch (Exception $e) {
        echo "❌ ERROR SINGLETON: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ ERROR: No se encontró includes/db.php en " . getcwd() . "/includes/db.php";
}