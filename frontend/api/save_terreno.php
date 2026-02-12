<?php
/**
 * ALISER - save_terreno.php (Versión Blindada v4)
 * Sincronizado con DB_STRUCTURE.md y main.js (form-oferta-terreno)
 */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start(); // Captura cualquier aviso accidental

$response = ['success' => false, 'message' => 'Error de proceso'];

try {
    define('ALISER_ADMIN', true);
    $base_path = dirname(__DIR__, 2);
    require_once $base_path . '/admin/includes/db.php';
    $db = Database::getInstance()->getConnection();

    // 1. Manejo de Imagen (Solo si se subió una)
    $nombre_imagen = null;
    if (isset($_FILES['imagen_terreno']) && $_FILES['imagen_terreno']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagen_terreno']['name'], PATHINFO_EXTENSION));
        $nombre_imagen = "terreno_" . time() . "_" . uniqid() . "." . $ext;
        $ruta_destino = "../../assets/img/terrenos/" . $nombre_imagen;
        
        // Solo intentamos mover si definimos la ruta
        if (!move_uploaded_file($_FILES['imagen_terreno']['tmp_name'], $ruta_destino)) {
            $nombre_imagen = null; 
        }
    }

    // 2. Preparar SQL (Columnas 2 a 12 según DB_STRUCTURE.md)
    $sql = "INSERT INTO terrenos (
                tipo_oferta, nombre_completo, email, telefono, 
                ubicacion_maps, metros_cuadrados, expectativa_economica, 
                situacion_legal, imagen_terreno, estatus
            ) VALUES (:tipo, :nom, :email, :tel, :maps, :m2, :pre, :legal, :img, 'nuevo')";

    $stmt = $db->prepare($sql);
    
    // Mapeo directo de los name="" que usa tu main.js/index.html
    $result = $stmt->execute([
        ':tipo'  => $_POST['tipo_oferta'] ?? 'venta',
        ':nom'   => $_POST['nombre'] ?? '',
        ':email' => $_POST['email'] ?? '',
        ':tel'   => $_POST['telefono'] ?? '',
        ':maps'  => $_POST['ubicacion_maps'] ?? '',
        ':m2'    => (float)($_POST['metros_cuadrados'] ?? 0),
        ':pre'   => (float)($_POST['expectativa_economica'] ?? 0),
        ':legal' => $_POST['situacion_legal'] ?? '',
        ':img'   => $nombre_imagen
    ]);

    if ($result) {
        $response = ['success' => true, 'message' => '¡Propuesta registrada correctamente!'];
    }

} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

ob_end_clean(); // Limpia cualquier basura de PHP (warnings)
echo json_encode($response);