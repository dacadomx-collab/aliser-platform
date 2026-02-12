<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../admin/includes/db.php';

$response = ['vacantes' => [], 'terrenos' => [], 'debug' => []];

try {
    $db = getDB();

    // Prueba 1: Vacantes (Manual dice: estatus)
    try {
        $stmt1 = $db->query("SELECT * FROM vacantes WHERE estatus = 'activa' ORDER BY id DESC");
        $response['vacantes'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['debug']['error_vacantes'] = "Error en tabla vacantes: " . $e->getMessage();
    }

    // Prueba 2: Terrenos (Manual dice: estatus)
    try {
        $stmt2 = $db->query("SELECT * FROM terrenos ORDER BY creado_en DESC LIMIT 5");
        $response['terrenos'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['debug']['error_terrenos'] = "Error en tabla terrenos: " . $e->getMessage();
    }

} catch (Exception $e) {
    $response['error_general'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);