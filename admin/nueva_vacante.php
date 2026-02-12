<?php
/**
 * ALISER - Panel de Administración
 * Archivo: nueva_vacante.php
 * Sincronizado con DB_STRUCTURE.md
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

if (!defined('ALISER_ADMIN')) { define('ALISER_ADMIN', true); }

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/image_helper.php';

$db = Database::getInstance()->getConnection();
$error_message = '';
$success_message = '';
$editar = false;
$vacante = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editar = true;
    $stmt = $db->prepare("SELECT * FROM vacantes WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $vacante = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sincronización estricta con DB_STRUCTURE.md
    $titulo = trim($_POST['titulo']);
    $sucursal = trim($_POST['sucursal']);
    $descripcion = trim($_POST['descripcion']);
    $estatus = $_POST['estatus']; // 'activa' o 'pausada'

    if (empty($titulo) || empty($sucursal) || empty($descripcion)) {
        $error_message = 'Todos los campos obligatorios deben ser llenados.';
    } else {
        try {
            $imagen_flyer = $vacante['imagen_flyer'] ?? null;

            if (isset($_FILES['imagen_flyer']) && $_FILES['imagen_flyer']['error'] === UPLOAD_ERR_OK) {
                ImageHelper::validateImage($_FILES['imagen_flyer']);
                $resultado = ImageHelper::processVacanteImage($_FILES['imagen_flyer']);
                if ($resultado) {
                    if ($editar && $imagen_flyer) { ImageHelper::deleteImage($imagen_flyer); }
                    $imagen_flyer = $resultado['path'];
                }else {
                    throw new Exception("Error al procesar la imagen.");
                }   
            }

           if ($editar) {
                $sql = "UPDATE vacantes SET 
                        titulo = :titulo, 
                        descripcion = :descripcion, 
                        estatus = :estatus,
                        imagen_flyer = :imagen -- Asegúrate que este sea el nombre real en tu DB
                        WHERE id = :id";
                $params = [
                    ':titulo' => $titulo,
                    ':descripcion' => $descripcion,
                    ':estatus' => $estatus,
                    ':imagen' => $imagen_final,
                    ':id' => (int)$_GET['id']
                ];
            } else {
                // Si 'sucursal' te da error, es porque NO debe ir en el INSERT
                $sql = "INSERT INTO vacantes (titulo, descripcion, imagen_flyer, estatus) 
                        VALUES (:titulo, :descripcion, :imagen, :estatus)";
                $params = [
                    ':titulo' => $titulo,
                    ':descripcion' => $descripcion,
                    ':imagen' => $imagen_final,
                    ':estatus' => $estatus
                ];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            header("Location: vacantes.php?success=1");
            exit;

        } catch (PDOException $e) {
            // Esto te dirá exactamente qué columna es la que NO existe
            $error_message = "Error de sistema: " . $e->getMessage();
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
    <link rel="stylesheet" href="/aliser-web/admin/css/admin-style.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--aliser-green-primary);">
            <div class="header-content">
                <h1 class="admin-title"><?= $editar ? 'Editar Vacante' : 'Nueva Vacante' ?></h1>
                <p class="admin-subtitle">Panel de gestión de talento humano ALISER</p>
            </div>
            <div class="header-actions">
                <a href="vacantes.php" class="login-btn" style="background: var(--color-gray);">Volver a Lista</a>
            </div>
        </header>

        <main class="admin-container" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-weight:bold; color:var(--aliser-green-primary); margin-bottom:8px;">Título de la Vacante</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($vacante['titulo'] ?? '') ?>" placeholder="Ej: Cajero/a Multifuncional" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-weight:bold; color:var(--aliser-green-primary); margin-bottom:8px;">Sucursal</label>
                    <input type="text" name="sucursal" value="<?= htmlspecialchars($vacante['sucursal'] ?? '') ?>" placeholder="Ej: Sucursal La Paz Centro" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-weight:bold; color:var(--aliser-green-primary); margin-bottom:8px;">Descripción de la Vacante</label>
                    <textarea name="descripcion" rows="6" placeholder="Detalla los requisitos y beneficios..." required><?= htmlspecialchars($vacante['descripcion'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label style="display:block; font-weight:bold; color:var(--aliser-green-primary); margin-bottom:8px;">Imagen Flyer (WebP)</label>
                        <input type="file" name="imagen_flyer" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label style="display:block; font-weight:bold; color:var(--aliser-green-primary); margin-bottom:8px;">Estatus</label>
                        <select name="estatus">
                            <option value="activa" <?= ($vacante['estatus'] ?? '') == 'activa' ? 'selected' : '' ?>>Activa</option>
                            <option value="pausada" <?= ($vacante['estatus'] ?? '') == 'pausada' ? 'selected' : '' ?>>Pausada</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 2rem;">
                    <button type="submit" class="login-btn shimmer" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                        <?= $editar ? '💾 Actualizar Cambios' : '🚀 Publicar Vacante' ?>
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>