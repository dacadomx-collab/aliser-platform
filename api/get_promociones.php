<?php
header('Content-Type: application/json; charset=utf-8');

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/../admin/includes/db.php';

$response = [
    'success' => false,
    'promociones' => [],
    'message' => ''
];

try {
    $db = Database::getInstance()->getConnection();
    $hoy = date('Y-m-d');

    $sql = "SELECT tipo_publico, titulo, descripcion, imagen_flyer, fecha_fin
            FROM promociones
            WHERE estatus = :estatus
              AND fecha_inicio <= :hoy
              AND fecha_fin >= :hoy
            ORDER BY fecha_fin ASC, creado_en DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':estatus' => 'activa',
        ':hoy' => $hoy
    ]);

    $response['success'] = true;
    $response['promociones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO en get_promociones.php: ' . $e->getMessage());
    $response['message'] = 'No fue posible cargar promociones.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
