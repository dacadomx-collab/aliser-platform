<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ALISER_ADMIN', true);

echo "Iniciando prueba de rutas...<br>";

$ruta_db = __DIR__ . '/includes/db.php';
echo "Buscando DB en: $ruta_db <br>";

if (file_exists($ruta_db)) {
    require_once $ruta_db;
    echo "✅ Conexión cargada.<br>";
    
    try {
        $db = Database::getInstance()->getConnection();
        echo "✅ Conexión a Base de Datos EXITOSA.<br>";
    } catch (Exception $e) {
        echo "❌ Error de DB: " . $e->getMessage();
    }
} else {
    echo "❌ Error: No se encuentra includes/db.php";
}
?>
<form method="POST">
   <input type="text" name="usuario" placeholder="Usuario">
   <input type="password" name="password" placeholder="Password">
   <button type="submit">Probar Login</button>
</form>