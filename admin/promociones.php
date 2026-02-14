<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'PROMO', 'MARCA']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/includes/db.php';

$error_message = '';
$promociones = [];
$tipo_publico = isset($_GET['tipo_publico']) ? trim($_GET['tipo_publico']) : '';
$tipos_validos = ['menudeo', 'mayoreo'];

if (!in_array($tipo_publico, $tipos_validos, true)) {
    $tipo_publico = '';
}

try {
    $db = Database::getInstance()->getConnection();

    if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
        $id_eliminar = (int)$_GET['eliminar'];

        $stmtImg = $db->prepare('SELECT imagen_flyer FROM promociones WHERE id = :id');
        $stmtImg->execute([':id' => $id_eliminar]);
        $img = $stmtImg->fetch(PDO::FETCH_ASSOC);

        if ($img && !empty($img['imagen_flyer'])) {
            $archivo = dirname(__DIR__) . '/assets/img/promociones/' . basename($img['imagen_flyer']);
            if (file_exists($archivo)) {
                @unlink($archivo);
            }
        }

        $stmtDel = $db->prepare('DELETE FROM promociones WHERE id = :id');
        $stmtDel->execute([':id' => $id_eliminar]);
        header('Location: promociones.php?deleted=1');
        exit;
    }

    $sql = 'SELECT id, tipo_publico, titulo, descripcion, imagen_flyer, fecha_inicio, fecha_fin, estatus, creado_en
            FROM promociones';
    $params = [];

    if ($tipo_publico !== '') {
        $sql .= ' WHERE tipo_publico = :tipo_publico';
        $params[':tipo_publico'] = $tipo_publico;
    }

    $sql .= ' ORDER BY fecha_inicio DESC, creado_en DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO en promociones.php: ' . $e->getMessage());
    $error_message = 'No fue posible cargar las promociones.';
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER Admin | Promociones</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title">Promociones y Cupones</h1>
                <p class="admin-subtitle">Gestiona flyers para Menudeo y Mayoreo</p>
            </div>
            <div class="header-actions">
                <a href="nueva_promocion.php" class="login-btn btn-add">
                    <span class="btn-text">Nueva Promocion</span>
                    <span class="btn-glow"></span>
                </a>
                <a href="dashboard.php" class="login-btn">
                    <span class="btn-text">Volver al Panel</span>
                    <span class="btn-glow"></span>
                </a>
            </div>
        </header>

        <main class="admin-content-card">
            <div class="tabs-row">
                <a href="promociones.php?tipo_publico=menudeo" class="tab-btn <?php echo $tipo_publico === 'menudeo' ? 'active' : ''; ?>">Vista Menudeo</a>
                <a href="promociones.php?tipo_publico=mayoreo" class="tab-btn <?php echo $tipo_publico === 'mayoreo' ? 'active' : ''; ?>">Vista Mayoreo</a>
                <a href="promociones.php" class="tab-btn <?php echo $tipo_publico === '' ? 'active' : ''; ?>">Todas</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Flyer</th>
                            <th>PÃºblico</th>
                            <th>TÃ­tulo</th>
                            <th>DescripciÃ³n</th>
                            <th>Vigencia</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promociones as $promo): ?>
                            <tr class="table-row-hover">
                                <td>
                                    <?php
                                    $img_name = basename((string)($promo['imagen_flyer'] ?? ''));
                                    $ruta = '../assets/img/promociones/' . $img_name;
                                    $fallback = '../assets/img/vacantes/no-image.webp';
                                    $src = ($img_name !== '' && file_exists($ruta)) ? $ruta : $fallback;
                                    ?>
                                    <img src="<?php echo htmlspecialchars($src); ?>" alt="Flyer" class="promo-thumb" loading="lazy">
                                </td>
                                <td>
                                    <span class="status-badge badge-<?php echo htmlspecialchars($promo['tipo_publico']); ?>">
                                        <?php echo strtoupper(htmlspecialchars($promo['tipo_publico'])); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($promo['titulo']); ?></strong></td>
                                <td class="table-description"><?php echo htmlspecialchars(substr((string)$promo['descripcion'], 0, 170)); ?>...</td>
                                <td>
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime((string)$promo['fecha_inicio']))); ?>
                                    -
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime((string)$promo['fecha_fin']))); ?>
                                </td>
                                <td>
                                    <span class="status-badge badge-<?php echo htmlspecialchars($promo['estatus']); ?>">
                                        <?php echo strtoupper(htmlspecialchars($promo['estatus'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="nueva_promocion.php?id=<?php echo (int)$promo['id']; ?>" class="btn-edit">Editar</a>
                                    <a href="?eliminar=<?php echo (int)$promo['id']; ?>" class="btn-delete" onclick="return confirm('Â¿Eliminar promociÃ³n?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

