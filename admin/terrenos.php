<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'BIENES']);

define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';
$db = Database::getInstance()->getConnection();

$query = $db->query('SELECT * FROM terrenos ORDER BY creado_en DESC');
$terrenos = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER Admin | Terrenos</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title">Modulo de Terrenos</h1>
                <p class="admin-subtitle">Gestion de ofertas recibidas desde el portal</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="login-btn">
                    <span class="btn-text">Volver al Panel</span>
                    <span class="btn-glow"></span>
                </a>
            </div>
        </header>

        <main class="admin-content-card">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Vista</th>
                            <th>Detalles / Precio</th>
                            <th>Situacion Legal</th>
                            <th>Contacto</th>
                            <th>Estatus</th>
                            <th>Ubicacion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terrenos as $t): ?>
                            <tr class="table-row-hover">
                                <td>
                                    <?php
                                    $imagen = (string)($t['imagen_terreno'] ?? '');
                                    $nombre = basename($imagen);
                                    $ruta1 = '../assets/img/terrenos/' . $nombre;
                                    $ruta2 = '../' . ltrim($imagen, '/');
                                    ?>
                                    <?php if (!empty($imagen) && (file_exists($ruta1) || file_exists($ruta2))): ?>
                                        <?php $src = file_exists($ruta1) ? $ruta1 : $ruta2; ?>
                                        <img
                                            src="<?php echo htmlspecialchars($src); ?>"
                                            class="thumb-img"
                                            alt="Terreno"
                                            loading="lazy"
                                            onclick="openZoom(this.src)"
                                        >
                                    <?php else: ?>
                                        <div class="thumb-placeholder">Sin foto</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="table-value-primary"><?php echo strtoupper(htmlspecialchars($t['tipo_oferta'])); ?></strong><br>
                                    <strong><?php echo number_format((float)$t['metros_cuadrados'], 0); ?> m2</strong><br>
                                    <span class="table-value-success">$<?php echo number_format((float)$t['expectativa_economica'], 2); ?></span>
                                </td>
                                <td>
                                    <div class="truncated-legal">
                                        <?php
                                        echo !empty($t['situacion_legal'])
                                            ? htmlspecialchars($t['situacion_legal'])
                                            : '<span class="text-muted">No especificado</span>';
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($t['nombre_completo']); ?></strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($t['telefono']); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge badge-<?php echo htmlspecialchars($t['estatus']); ?>">
                                        <?php echo strtoupper(htmlspecialchars($t['estatus'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($t['ubicacion_maps']); ?>" target="_blank" rel="noopener noreferrer" class="map-link" title="Ver en Google Maps">📍</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="zoomContainer" class="zoom-overlay" onclick="closeZoom()">
        <span class="zoom-close">&times;</span>
        <img id="imgZoomed" src="" class="zoom-image" alt="Zoom de imagen">
    </div>

    <script>
        function openZoom(src) {
            document.getElementById('imgZoomed').src = src;
            document.getElementById('zoomContainer').style.display = 'flex';
        }

        function closeZoom() {
            document.getElementById('zoomContainer').style.display = 'none';
        }
    </script>
</body>
</html>

