<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
define('ALISER_ADMIN', true);
require_once __DIR__ . '/includes/db.php';
$db = Database::getInstance()->getConnection();

$query = $db->query("SELECT * FROM terrenos ORDER BY creado_en DESC");
$terrenos = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ALISER Admin | Terrenos</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body style="background-color: #f4f7f6; min-height: 100vh; font-family: var(--font-family-primary); padding: 20px;">
    <div class="admin-wrapper" style="width: 95%; max-width: 1300px; margin: 0 auto;">
        
        <header class="admin-header-main" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--aliser-green-primary);">
            <div class="header-content">
                <h1 class="admin-title" style="color: var(--aliser-green-primary); margin: 0; font-size: 1.8rem; font-weight: 700;">Módulo de Terrenos</h1>
                <p class="admin-subtitle" style="color: #666; margin: 0; font-size: 0.9rem;">Gestión de ofertas recibidas desde el portal</p>
            </div>
            <a href="dashboard.php" class="login-btn" style="width: auto; padding: 10px 25px; text-decoration: none;">Volver al PANEL</a>
        </header>

        <main class="login-card" style="width: 100%; max-width: none; padding: 0; overflow: hidden; border-radius: 15px; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--aliser-green-primary);">
                            <th style="padding: 18px; text-align: left; color: white;">Vista</th>
                            <th style="padding: 18px; text-align: left; color: white;">Detalles / Precio</th>
                            <th style="padding: 18px; text-align: left; color: white;">Situación Legal</th> <th style="padding: 18px; text-align: left; color: white;">Contacto</th>
                            <th style="padding: 18px; text-align: left; color: white;">Estatus</th>
                            <th style="padding: 18px; text-align: left; color: white;">Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terrenos as $t): ?>
                        <tr style="border-bottom: 1px solid #eee; transition: 0.3s;" onmouseover="this.style.backgroundColor='#f9fdfa'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 15px; text-align: center;">
                                <?php if ($t['imagen_terreno']): ?>
                                    <img src="../assets/img/terrenos/<?= $t['imagen_terreno'] ?>" 
                                         onclick="openZoom(this.src)"
                                         style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px; cursor: pointer; border: 2px solid var(--aliser-sand-secondary);">
                                <?php else: ?>
                                    <div style="width: 70px; height: 70px; background: #f9f9f9; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999; border: 1px dashed #ccc;">Sin foto</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <strong style="color: var(--aliser-green-primary);"><?= strtoupper($t['tipo_oferta']) ?></strong><br>
                                <strong><?= number_format($t['metros_cuadrados'], 0) ?> m²</strong><br>
                                <span style="color: #28a745; font-weight: bold;">$<?= number_format($t['expectativa_economica'], 2) ?></span>
                            </td>
                            <td style="padding: 15px; max-width: 250px;">
                                <div style="font-size: 0.85rem; color: #555; line-height: 1.4; max-height: 60px; overflow-y: auto;">
                                    <?= !empty($t['situacion_legal']) ? htmlspecialchars($t['situacion_legal']) : '<i style="color:#bbb">No especificado</i>' ?>
                                </div>
                            </td>
                            <td style="padding: 15px;">
                                <strong style="color: #333;"><?= htmlspecialchars($t['nombre_completo']) ?></strong><br>
                                <span style="color: var(--aliser-teal-dark); font-size: 0.85rem;"><?= $t['telefono'] ?></span>
                            </td>
                            <td style="padding: 15px;">
                                <span class="status-badge badge-<?= $t['estatus'] ?>" style="padding: 6px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: bold;">
                                    <?= strtoupper($t['estatus']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="<?= $t['ubicacion_maps'] ?>" target="_blank" style="text-decoration: none; font-size: 1.5rem;" title="Ver en Google Maps">📍</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="zoomContainer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:10000; justify-content:center; align-items:center; backdrop-filter: blur(8px);" onclick="closeZoom()">
        <span style="position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer;">&times;</span>
        <img id="imgZoomed" src="" style="max-width: 90%; max-height: 90%; border-radius: 10px;">
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