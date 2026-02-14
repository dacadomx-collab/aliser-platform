<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'TALENTO']);

define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';

$vacantes = [];
$error_message = '';

try {
    $db = Database::getInstance()->getConnection();

    if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
        $id_eliminar = (int)$_GET['eliminar'];

        $stmt_img = $db->prepare('SELECT imagen_flyer FROM vacantes WHERE id = ?');
        $stmt_img->execute([$id_eliminar]);
        $img_data = $stmt_img->fetch(PDO::FETCH_ASSOC);

        if ($img_data && !empty($img_data['imagen_flyer'])) {
            $archivo = dirname(__DIR__) . '/assets/img/vacantes/' . basename($img_data['imagen_flyer']);
            if (file_exists($archivo)) {
                @unlink($archivo);
            }
        }

        $stmt_del = $db->prepare('DELETE FROM vacantes WHERE id = ?');
        $stmt_del->execute([$id_eliminar]);

        header('Location: vacantes.php?eliminado=success');
        exit;
    }

    $query = $db->query('SELECT id, titulo, sucursal, descripcion, imagen_flyer, estatus, fecha_fin, creado_en FROM vacantes ORDER BY creado_en DESC');
    $vacantes = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO en vacantes.php: ' . $e->getMessage());
    $error_message = 'No fue posible cargar las vacantes en este momento.';
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER Admin | Vacantes</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title">Gestion de Vacantes</h1>
                <p class="admin-subtitle">Publica y administra las vacantes laborales</p>
            </div>
            <div class="header-actions">
                <a href="nueva_vacante.php" class="login-btn btn-add">
                    <span class="btn-text">Nueva Vacante</span>
                    <span class="btn-glow"></span>
                </a>
                <a href="dashboard.php" class="login-btn">
                    <span class="btn-text">Volver al Panel</span>
                    <span class="btn-glow"></span>
                </a>
            </div>
        </header>

        <main class="vacantes-grid">
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php foreach ($vacantes as $v): ?>
                <article class="vacante-card">
                    <div class="vacante-image-container">
                        <?php
                        $nombre_archivo = basename($v['imagen_flyer'] ?? '');
                        if (!empty($nombre_archivo) && file_exists('../assets/img/vacantes/' . $nombre_archivo)) {
                            $ruta_final = '../assets/img/vacantes/' . $nombre_archivo;
                        } else {
                            $ruta_final = '../assets/img/vacantes/no-image.webp';
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($ruta_final); ?>" alt="Vacante" loading="lazy">
                    </div>

                    <div class="vacante-info">
                        <h2 class="vacante-titulo"><?php echo htmlspecialchars($v['titulo']); ?></h2>
                        <span class="vacante-sucursal"><?php echo htmlspecialchars($v['sucursal'] ?? 'Matriz'); ?></span>
                        <div class="vacante-detalles">
                            <?php echo htmlspecialchars(substr($v['descripcion'], 0, 140)); ?>...
                        </div>
                        <span class="status-badge badge-<?php echo htmlspecialchars($v['estatus']); ?>">
                            <?php echo strtoupper(htmlspecialchars($v['estatus'])); ?>
                        </span>
                        <?php
                        $hoy = date('Y-m-d');
                        $fecha_fin = isset($v['fecha_fin']) ? (string)$v['fecha_fin'] : '';
                        if ($fecha_fin !== '' && $fecha_fin < $hoy):
                        ?>
                            <span class="status-badge badge-vencida">VENCIDA</span>
                        <?php endif; ?>
                    </div>

                    <div class="vacante-footer">
                        <a href="nueva_vacante.php?id=<?php echo (int)$v['id']; ?>" class="btn-edit">Editar</a>
                        <a href="?eliminar=<?php echo (int)$v['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar vacante?')">Eliminar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>

