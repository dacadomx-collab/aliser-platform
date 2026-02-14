<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'PROMO', 'MARCA']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/image_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: promociones.php');
    exit;
}

$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
$tipo_publico = isset($_POST['tipo_publico']) ? trim($_POST['tipo_publico']) : '';
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
$fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';
$estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : '';

$tipos_validos = ['menudeo', 'mayoreo'];
$estatus_validos = ['activa', 'pausada'];

if ($titulo === '' || $descripcion === '' || $fecha_inicio === '' || $fecha_fin === '') {
    header('Location: nueva_promocion.php?error=1' . ($id ? '&id=' . $id : ''));
    exit;
}

if (!in_array($tipo_publico, $tipos_validos, true)) {
    header('Location: nueva_promocion.php?error=tipo' . ($id ? '&id=' . $id : ''));
    exit;
}

if (!in_array($estatus, $estatus_validos, true)) {
    header('Location: nueva_promocion.php?error=estatus' . ($id ? '&id=' . $id : ''));
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
    header('Location: nueva_promocion.php?error=fecha' . ($id ? '&id=' . $id : ''));
    exit;
}

if ($fecha_fin < $fecha_inicio) {
    header('Location: nueva_promocion.php?error=rango' . ($id ? '&id=' . $id : ''));
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $imagen_flyer = null;

    if (isset($_FILES['imagen_flyer']) && $_FILES['imagen_flyer']['error'] === UPLOAD_ERR_OK) {
        ImageHelper::validateImage($_FILES['imagen_flyer']);
        $resultado = ImageHelper::processVacanteImage($_FILES['imagen_flyer'], 'assets/img/promociones/');
        if ($resultado && isset($resultado['path'])) {
            $imagen_flyer = $resultado['path'];
        }
    }

    if ($id !== null) {
        if ($imagen_flyer === null) {
            $stmtActual = $db->prepare('SELECT imagen_flyer FROM promociones WHERE id = :id');
            $stmtActual->execute([':id' => $id]);
            $actual = $stmtActual->fetch(PDO::FETCH_ASSOC);
            $imagen_flyer = $actual['imagen_flyer'] ?? null;
        }

        $sql = 'UPDATE promociones
                SET tipo_publico = :tipo_publico,
                    titulo = :titulo,
                    descripcion = :descripcion,
                    imagen_flyer = :imagen_flyer,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    estatus = :estatus
                WHERE id = :id';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':tipo_publico' => $tipo_publico,
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':imagen_flyer' => $imagen_flyer,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin,
            ':estatus' => $estatus,
            ':id' => $id
        ]);
    } else {
        $sql = 'INSERT INTO promociones (tipo_publico, titulo, descripcion, imagen_flyer, fecha_inicio, fecha_fin, estatus)
                VALUES (:tipo_publico, :titulo, :descripcion, :imagen_flyer, :fecha_inicio, :fecha_fin, :estatus)';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':tipo_publico' => $tipo_publico,
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':imagen_flyer' => $imagen_flyer,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin,
            ':estatus' => $estatus
        ]);
    }

    header('Location: promociones.php?success=1');
    exit;
} catch (PDOException $e) {
    error_log('Error PDO en save_promocion.php: ' . $e->getMessage());
    header('Location: nueva_promocion.php?error=db' . ($id ? '&id=' . $id : ''));
    exit;
} catch (Exception $e) {
    error_log('Error general en save_promocion.php: ' . $e->getMessage());
    header('Location: nueva_promocion.php?error=general' . ($id ? '&id=' . $id : ''));
    exit;
}
