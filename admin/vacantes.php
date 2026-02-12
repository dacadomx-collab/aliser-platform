<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }
define('ALISER_ADMIN', true);

require_once __DIR__ . '/includes/db.php';
// PRIMERO: Obtenemos la conexión
$db = Database::getInstance()->getConnection();

// SEGUNDO: Lógica de eliminación (Ahora $db ya existe y no dará error)
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    // Buscar la imagen para borrarla del disco
    $stmt_img = $db->prepare("SELECT imagen_flyer FROM vacantes WHERE id = ?");
    $stmt_img->execute([$id_eliminar]);
    $img_data = $stmt_img->fetch();
    
    if ($img_data && !empty($img_data['imagen_flyer'])) {
        $archivo = dirname(__DIR__) . '/assets/img/vacantes/' . basename($img_data['imagen_flyer']);
        if (file_exists($archivo)) { @unlink($archivo); }
    }
    
    $stmt_del = $db->prepare("DELETE FROM vacantes WHERE id = ?");
    $stmt_del->execute([$id_eliminar]);
    header("Location: vacantes.php?eliminado=success");
    exit;
}

// TERCERO: Carga de datos para la tabla
$query = $db->query("SELECT * FROM vacantes ORDER BY creado_en DESC");
$vacantes = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ALISER Admin | Vacantes</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary" style="background-color: #f4f7f6; min-height: 100vh; margin: 0; padding: 20px;">
    <div class="admin-wrapper" style="width: 95%; max-width: 1300px; margin: 0 auto;">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title">Gestión de Vacantes</h1>
                <p class="admin-subtitle">Publica y administra las vacantes laborales</p>
            </div>
            <div class="header-actions">
                <a href="nueva_vacante.php" class="login-btn btn-add">+ Nueva Vacante</a>
                <a href="dashboard.php" class="login-btn">Volver al PANEL</a>
            </div>
        </header>
        <main class="vacantes-grid">
           <link rel="stylesheet" href="css/admin-style.css">

            <?php foreach ($vacantes as $v): ?>
                <article class="vacante-card">
                    <div class="vacante-image">
                        <?php 
                            // 1. Limpiamos cualquier rastro de ruta previa que venga de la DB
                            $nombre_archivo = basename($v['imagen_flyer'] ?? ''); 
                            
                            // 2. Definimos la ruta correcta desde la carpeta /admin/
                            if (!empty($nombre_archivo) && file_exists("../assets/img/vacantes/" . $nombre_archivo)) {
                                $ruta_final = "../assets/img/vacantes/" . $nombre_archivo;
                            } else {
                                // Ruta al placeholder (Asegúrate que este archivo exista)
                                $ruta_final = "../assets/img/design/no-image.webp";
                            }
                        ?>
                        <img src="<?= $ruta_final ?>" alt="Vacante" loading="lazy">
                    </div>
                    
                    <div class="vacante-info">
                        <span class="vacante-sucursal"><?= htmlspecialchars($v['sucursal'] ?? 'General') ?></span>
                        <h2 class="vacante-titulo"><?= htmlspecialchars($v['titulo']) ?></h2>
                        <div class="vacante-detalles">
                            <?= substr(htmlspecialchars($v['descripcion']), 0, 100) ?>...
                        </div>
                        <span class="status-badge badge-<?= $v['estatus'] ?>">
                            <?= strtoupper($v['estatus']) ?>
                        </span>
                    </div>

                    <div class="vacante-footer">
                        <a href="nueva_vacante.php?id=<?= $v['id'] ?>" class="btn-edit">Editar</a>
                        <a href="?eliminar=<?= $v['id'] ?>" class="btn-delete" onclick="return confirm('¿Eliminar vacante?')">Eliminar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>