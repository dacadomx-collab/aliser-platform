<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'PROMO', 'MARCA']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/includes/db.php';

$editar = false;
$promocion = null;
$error_message = '';

try {
    $db = Database::getInstance()->getConnection();

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $editar = true;
        $stmt = $db->prepare('SELECT * FROM promociones WHERE id = :id');
        $stmt->execute([':id' => (int)$_GET['id']]);
        $promocion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$promocion) {
            $editar = false;
            $error_message = 'No se encontrÃ³ la promociÃ³n solicitada.';
        }
    }

    if (isset($_GET['error'])) {
        $error_message = 'Revisa los datos del formulario e intenta nuevamente.';
    }
} catch (PDOException $e) {
    error_log('Error PDO en nueva_promocion.php: ' . $e->getMessage());
    $error_message = 'No fue posible cargar el formulario.';
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER Admin | <?php echo $editar ? 'Editar' : 'Nueva'; ?> PromociÃ³n</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title"><?php echo $editar ? 'Editar PromociÃ³n' : 'Nueva PromociÃ³n'; ?></h1>
                <p class="admin-subtitle">Registro de campaÃ±as de Menudeo y Mayoreo</p>
            </div>
            <div class="header-actions">
                <a href="promociones.php" class="btn-secondary">Volver a Lista</a>
            </div>
        </header>

        <main class="admin-content-card">
            <?php if ($error_message !== ''): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="save_promocion.php" enctype="multipart/form-data" class="admin-form">
                <?php if ($editar && $promocion): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$promocion['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="tipo_publico" class="form-label">Tipo de PÃºblico <span class="required">*</span></label>
                        <select id="tipo_publico" name="tipo_publico" class="form-input" required>
                            <?php $tipo = $promocion['tipo_publico'] ?? 'menudeo'; ?>
                            <option value="menudeo" <?php echo $tipo === 'menudeo' ? 'selected' : ''; ?>>Menudeo</option>
                            <option value="mayoreo" <?php echo $tipo === 'mayoreo' ? 'selected' : ''; ?>>Mayoreo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estatus" class="form-label">Estatus <span class="required">*</span></label>
                        <select id="estatus" name="estatus" class="form-input" required>
                            <?php $estatus = $promocion['estatus'] ?? 'activa'; ?>
                            <option value="activa" <?php echo $estatus === 'activa' ? 'selected' : ''; ?>>Activa</option>
                            <option value="pausada" <?php echo $estatus === 'pausada' ? 'selected' : ''; ?>>Pausada</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="titulo" class="form-label">TÃ­tulo <span class="required">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-input" required value="<?php echo htmlspecialchars($promocion['titulo'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion" class="form-label">DescripciÃ³n <span class="required">*</span></label>
                    <textarea id="descripcion" name="descripcion" class="form-textarea" required><?php echo htmlspecialchars($promocion['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio <span class="required">*</span></label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-input" required value="<?php echo htmlspecialchars($promocion['fecha_inicio'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin" class="form-label">Fecha Fin <span class="required">*</span></label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-input" required value="<?php echo htmlspecialchars($promocion['fecha_fin'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagen_flyer" class="form-label">Imagen Flyer</label>
                    <input type="file" id="imagen_flyer" name="imagen_flyer" class="form-input" accept="image/*">
                    <?php if (!empty($promocion['imagen_flyer'])): ?>
                        <p class="current-file">Actual: <?php echo htmlspecialchars(basename($promocion['imagen_flyer'])); ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="login-btn shimmer">
                        <span class="btn-text"><?php echo $editar ? 'Actualizar PromociÃ³n' : 'Guardar PromociÃ³n'; ?></span>
                        <span class="btn-glow"></span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>

