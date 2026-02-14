<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'TALENTO']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/image_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vacantes.php');
    exit;
}

$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$sucursal = isset($_POST['sucursal']) ? trim($_POST['sucursal']) : 'Matriz';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : null;
$fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;
$estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : 'activa';
$imagen_flyer = null;
$vacante_id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
$sucursales_permitidas = ['Matriz', 'La Paz', 'Los Cabos', 'Constitución'];

if ($titulo === '' || $descripcion === '') {
    header('Location: nueva_vacante.php?error=1');
    exit;
}

if (!in_array($sucursal, $sucursales_permitidas, true)) {
    header('Location: nueva_vacante.php?error=sucursal');
    exit;
}

if (!in_array($estatus, ['activa', 'pausada'], true)) {
    header('Location: nueva_vacante.php?error=estatus');
    exit;
}

if (!empty($fecha_inicio) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
    header('Location: nueva_vacante.php?error=fecha_inicio');
    exit;
}

if (!empty($fecha_fin) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
    header('Location: nueva_vacante.php?error=fecha_fin');
    exit;
}

if (!empty($fecha_inicio) && !empty($fecha_fin) && $fecha_fin < $fecha_inicio) {
    header('Location: nueva_vacante.php?error=fechas');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    if (isset($_FILES['imagen_flyer']) && $_FILES['imagen_flyer']['error'] === UPLOAD_ERR_OK) {
        ImageHelper::validateImage($_FILES['imagen_flyer']);
        $resultado = ImageHelper::processVacanteImage($_FILES['imagen_flyer']);
        if ($resultado && isset($resultado['path'])) {
            $imagen_flyer = $resultado['path'];
        }
    }

    if ($vacante_id !== null) {
        if ($imagen_flyer === null) {
            $stmtImg = $db->prepare('SELECT imagen_flyer FROM vacantes WHERE id = :id');
            $stmtImg->execute([':id' => $vacante_id]);
            $actual = $stmtImg->fetch(PDO::FETCH_ASSOC);
            $imagen_flyer = $actual['imagen_flyer'] ?? null;
        }

        $sql = 'UPDATE vacantes
                SET titulo = :titulo,
                    sucursal = :sucursal,
                    descripcion = :descripcion,
                    imagen_flyer = :imagen_flyer,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    estatus = :estatus
                WHERE id = :id';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':sucursal' => $sucursal,
            ':descripcion' => $descripcion,
            ':imagen_flyer' => $imagen_flyer,
            ':fecha_inicio' => $fecha_inicio !== '' ? $fecha_inicio : null,
            ':fecha_fin' => $fecha_fin !== '' ? $fecha_fin : null,
            ':estatus' => $estatus,
            ':id' => $vacante_id
        ]);
    } else {
        $sql = 'INSERT INTO vacantes (titulo, sucursal, descripcion, imagen_flyer, fecha_inicio, fecha_fin, estatus)
                VALUES (:titulo, :sucursal, :descripcion, :imagen_flyer, :fecha_inicio, :fecha_fin, :estatus)';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':sucursal' => $sucursal,
            ':descripcion' => $descripcion,
            ':imagen_flyer' => $imagen_flyer,
            ':fecha_inicio' => $fecha_inicio !== '' ? $fecha_inicio : null,
            ':fecha_fin' => $fecha_fin !== '' ? $fecha_fin : null,
            ':estatus' => $estatus
        ]);
    }

    header('Location: vacantes.php?success=1');
    exit;
} catch (PDOException $e) {
    error_log('Error PDO en save_vacante.php: ' . $e->getMessage());
    header('Location: nueva_vacante.php?error=db');
    exit;
} catch (Exception $e) {
    error_log('Error general en save_vacante.php: ' . $e->getMessage());
    header('Location: nueva_vacante.php?error=general');
    exit;
}
