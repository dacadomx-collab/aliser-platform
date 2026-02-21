<?php
// ALISER 2026 - Script de Diagnóstico de Base de Datos
require_once 'includes/db.php'; // Tu clase Singleton

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Diagnóstico de Conexión: ALISER</h2>";

try {
    $db = Database::getInstance();
    echo "<p style='color:green;'>✅ Conexión Exitosa al servidor de GreenGeeks.</p>";

    // 1. Probar Tabla: usuarios_admin (Estructura Espejo)
    echo "<h3>1. Verificando Tabla: usuarios_admin</h3>";
    $stmtUser = $db->query("SELECT id, nombre_completo, usuario, activo FROM usuarios_admin LIMIT 1");
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✔ Datos encontrados: Usuario <b>" . $user['usuario'] . "</b> (ID: " . $user['id'] . ").<br>";
    } else {
        echo "ℹ Tabla vacía o sin registros de administradores.<br>";
    }
    //test para subir a github   
    // 2. Probar Tabla: vacantes (Siguiendo DB_STRUCTURE.md)
    echo "<h3>2. Verificando Tabla: vacantes</h3>";
    $stmtVac = $db->query("SELECT id, titulo, sucursal, estatus FROM vacantes LIMIT 1");
    $vacante = $stmtVac->fetch(PDO::FETCH_ASSOC);

    if ($vacante) {
        echo "✔ Datos encontrados: Vacante <b>" . $vacante['titulo'] . "</b> en " . $vacante['sucursal'] . ".<br>";
    } else {
        echo "ℹ No hay vacantes registradas actualmente.<br>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error de Diagnóstico: " . $e->getMessage() . "</p>";
    echo "<p>Revisa que el archivo <b>admin/includes/db_config.php</b> tenga el nombre de base de datos <u>tecnidepot_aliser</u>.</p>";
}
?>