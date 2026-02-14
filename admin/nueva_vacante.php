<?php
/**
 * ALISER - Panel de Administracion
 * Archivo: nueva_vacante.php
 * Sincronizado con DB_STRUCTURE.md
 */

require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'TALENTO']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/image_helper.php';

$db = Database::getInstance()->getConnection();
$error_message = '';
$editar = false;
$vacante = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editar = true;
    try {
        $stmt = $db->prepare('SELECT * FROM vacantes WHERE id = ?');
        $stmt->execute([(int)$_GET['id']]);
        $vacante = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error PDO al cargar vacante en nueva_vacante.php: ' . $e->getMessage());
        $error_message = 'No fue posible cargar la vacante solicitada.';
        $editar = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $sucursal = isset($_POST['sucursal']) ? trim($_POST['sucursal']) : 'Matriz';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : null;
    $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;
    $estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : 'activa';
    $sucursales_permitidas = ['Matriz', 'La Paz', 'Los Cabos', 'Constitución'];

    if (empty($titulo) || empty($descripcion)) {
        $error_message = 'Todos los campos obligatorios deben ser llenados.';
    } elseif (!in_array($sucursal, $sucursales_permitidas, true)) {
        $error_message = 'Sucursal no valida.';
    } elseif (!in_array($estatus, ['activa', 'pausada'], true)) {
        $error_message = 'Estatus no valido.';
    } elseif (!empty($fecha_inicio) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
        $error_message = 'fecha_inicio no es valida.';
    } elseif (!empty($fecha_fin) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        $error_message = 'fecha_fin no es valida.';
    } elseif (!empty($fecha_inicio) && !empty($fecha_fin) && $fecha_fin < $fecha_inicio) {
        $error_message = 'fecha_fin no puede ser menor que fecha_inicio.';
    } else {
        try {
            $imagen_flyer = $vacante['imagen_flyer'] ?? null;

            if (isset($_FILES['imagen_flyer']) && $_FILES['imagen_flyer']['error'] === UPLOAD_ERR_OK) {
                ImageHelper::validateImage($_FILES['imagen_flyer']);
                $resultado = ImageHelper::processVacanteImage($_FILES['imagen_flyer']);
                if ($resultado && isset($resultado['path'])) {
                    if ($editar && !empty($imagen_flyer)) {
                        ImageHelper::deleteImage($imagen_flyer);
                    }
                    $imagen_flyer = $resultado['path'];
                } else {
                    throw new Exception('Error al procesar la imagen.');
                }
            }

            if ($editar) {
                $sql = 'UPDATE vacantes
                        SET titulo = :titulo,
                            sucursal = :sucursal,
                            descripcion = :descripcion,
                            fecha_inicio = :fecha_inicio,
                            fecha_fin = :fecha_fin,
                            estatus = :estatus,
                            imagen_flyer = :imagen_flyer
                        WHERE id = :id';

                $params = [
                    ':titulo' => $titulo,
                    ':sucursal' => $sucursal,
                    ':descripcion' => $descripcion,
                    ':fecha_inicio' => $fecha_inicio !== '' ? $fecha_inicio : null,
                    ':fecha_fin' => $fecha_fin !== '' ? $fecha_fin : null,
                    ':estatus' => $estatus,
                    ':imagen_flyer' => $imagen_flyer,
                    ':id' => (int)$_GET['id']
                ];
            } else {
                $sql = 'INSERT INTO vacantes (titulo, sucursal, descripcion, imagen_flyer, fecha_inicio, fecha_fin, estatus)
                        VALUES (:titulo, :sucursal, :descripcion, :imagen_flyer, :fecha_inicio, :fecha_fin, :estatus)';

                $params = [
                    ':titulo' => $titulo,
                    ':sucursal' => $sucursal,
                    ':descripcion' => $descripcion,
                    ':imagen_flyer' => $imagen_flyer,
                    ':fecha_inicio' => $fecha_inicio !== '' ? $fecha_inicio : null,
                    ':fecha_fin' => $fecha_fin !== '' ? $fecha_fin : null,
                    ':estatus' => $estatus
                ];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            header('Location: vacantes.php?success=1');
            exit;
        } catch (PDOException $e) {
            error_log('Error PDO al guardar vacante en nueva_vacante.php: ' . $e->getMessage());
            $error_message = 'No fue posible guardar la vacante en este momento.';
        } catch (Exception $e) {
            error_log('Error general al guardar vacante en nueva_vacante.php: ' . $e->getMessage());
            $error_message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER Admin | Nueva Vacante</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title"><?php echo $editar ? 'Editar Vacante' : 'Nueva Vacante'; ?></h1>
                <p class="admin-subtitle">Panel de gestion de talento humano ALISER</p>
            </div>
            <div class="header-actions">
                <a href="vacantes.php" class="btn-secondary">Volver a Lista</a>
            </div>
        </header>

        <main class="admin-content-card">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="form-group">
                    <label for="titulo" class="form-label">Titulo de la Vacante <span class="required">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-input" value="<?php echo htmlspecialchars($vacante['titulo'] ?? ''); ?>" placeholder="Ej: Cajero/a Multifuncional" required>
                </div>

                <div class="form-group">
                    <label for="descripcion" class="form-label">Descripcion de la Vacante <span class="required">*</span></label>
                    <textarea id="descripcion" name="descripcion" class="form-textarea" placeholder="Detalla los requisitos y beneficios..." required><?php echo htmlspecialchars($vacante['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="sucursal" class="form-label">Sucursal <span class="required">*</span></label>
                    <select id="sucursal" name="sucursal" class="form-input" required>
                        <?php
                        $sucursal_actual = $vacante['sucursal'] ?? 'Matriz';
                        $sucursales = ['Matriz', 'La Paz', 'Los Cabos', 'Constitución'];
                        foreach ($sucursales as $sucursal_opcion):
                        ?>
                            <option value="<?php echo htmlspecialchars($sucursal_opcion); ?>" <?php echo ($sucursal_actual === $sucursal_opcion) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sucursal_opcion); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-input" value="<?php echo htmlspecialchars($vacante['fecha_inicio'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-input" value="<?php echo htmlspecialchars($vacante['fecha_fin'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="imagen_flyer" class="form-label">Imagen Flyer (WebP)</label>
                        <input type="file" id="imagen_flyer" name="imagen_flyer" class="form-input" accept="image/*">
                        <?php if (!empty($vacante['imagen_flyer'])): ?>
                            <p class="current-file">Actual: <?php echo htmlspecialchars(basename($vacante['imagen_flyer'])); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="estatus" class="form-label">Estatus <span class="required">*</span></label>
                        <select id="estatus" name="estatus" class="form-input" required>
                            <option value="activa" <?php echo (($vacante['estatus'] ?? 'activa') === 'activa') ? 'selected' : ''; ?>>Activa</option>
                            <option value="pausada" <?php echo (($vacante['estatus'] ?? '') === 'pausada') ? 'selected' : ''; ?>>Pausada</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="login-btn shimmer">
                        <span class="btn-text"><?php echo $editar ? 'Actualizar Cambios' : 'Publicar Vacante'; ?></span>
                        <span class="btn-glow"></span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
