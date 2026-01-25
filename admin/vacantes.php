<?php
/**
 * ALISER - Panel de Administración
 * Gestión de Vacantes
 * 
 * @package ALISER
 * @version 1.0.0
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Definir constante antes de incluir db.php
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

// Incluir archivo de conexión a base de datos
require_once __DIR__ . '/includes/db.php';

// Obtener datos del usuario de la sesión
$admin_nombre = isset($_SESSION['admin_nombre']) ? $_SESSION['admin_nombre'] : 'Usuario';
$admin_rol = isset($_SESSION['admin_rol']) ? $_SESSION['admin_rol'] : 'admin';

// Procesar eliminación de vacante
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $vacante_id = (int)$_GET['eliminar'];
    try {
        $db = getDB();
        // Obtener información de la imagen antes de eliminar
        $vacante = $db->fetchOne("SELECT imagen_flyer FROM vacantes WHERE id = :id", ['id' => $vacante_id]);
        
        if ($vacante) {
            // Eliminar la vacante
            $db->query("DELETE FROM vacantes WHERE id = :id", ['id' => $vacante_id]);
            
            // Eliminar la imagen si existe
            if ($vacante['imagen_flyer'] && file_exists('../' . $vacante['imagen_flyer'])) {
                @unlink('../' . $vacante['imagen_flyer']);
            }
            
            $success_message = 'Vacante eliminada correctamente.';
        }
    } catch (Exception $e) {
        $error_message = 'Error al eliminar la vacante.';
        error_log('Error eliminando vacante: ' . $e->getMessage());
    }
}

// Obtener todas las vacantes
try {
    $db = getDB();
    $vacantes = $db->fetchAll("SELECT * FROM vacantes ORDER BY creado_en DESC");
} catch (Exception $e) {
    $error_message = 'Error al cargar las vacantes.';
    error_log('Error cargando vacantes: ' . $e->getMessage());
    $vacantes = [];
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    
    <title>Gestión de Vacantes - ALISER</title>
    
    <!-- Estilos del Panel de Administración -->
    <link rel="stylesheet" href="css/admin-style.css">
    
    <style>
        .admin-container {
            min-height: 100vh;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .admin-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--aliser-green-primary);
            margin: 0;
        }
        
        .admin-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            position: relative;
            padding: 0.875rem 1.5rem;
            background: transparent;
            border: 2px solid var(--aliser-teal-tertiary);
            color: var(--aliser-teal-tertiary);
            font-family: var(--font-family-primary);
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all var(--transition-fast);
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--aliser-teal-tertiary);
            z-index: -1;
            transition: left 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .btn-primary:hover::before,
        .btn-primary:focus::before {
            left: 0;
        }
        
        .btn-primary:hover,
        .btn-primary:focus {
            color: var(--aliser-green-primary);
            outline: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(67, 145, 132, 0.3);
        }
        
        .btn-secondary {
            padding: 0.875rem 1.5rem;
            background: rgba(102, 102, 102, 0.1);
            border: 2px solid rgba(102, 102, 102, 0.3);
            color: var(--color-gray-dark);
            font-family: var(--font-family-primary);
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .btn-secondary:hover {
            background: rgba(102, 102, 102, 0.2);
        }
        
        .btn-danger {
            padding: 0.5rem 1rem;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
            font-size: 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.2);
        }
        
        .vacantes-grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .vacante-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform var(--transition-base), box-shadow var(--transition-base);
        }
        
        .vacante-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .vacante-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        
        .vacante-titulo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--aliser-green-primary);
            margin: 0;
            flex: 1;
        }
        
        .vacante-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-active {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .badge-inactive {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        
        .vacante-imagen {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .vacante-descripcion {
            color: var(--color-gray);
            margin: 1rem 0;
            line-height: 1.6;
        }
        
        .vacante-fechas {
            display: flex;
            gap: 2rem;
            margin: 1rem 0;
            font-size: 0.875rem;
            color: var(--color-gray);
        }
        
        .vacante-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .empty-state {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .empty-state-text {
            font-size: 1.125rem;
            color: var(--color-gray);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <div>
                <h1 class="admin-title">📋 Gestión de Vacantes</h1>
                <p style="color: var(--color-gray); margin: 0.5rem 0 0 0;">Administra las vacantes de trabajo de ALISER</p>
            </div>
            <div class="admin-actions">
                <a href="dashboard.php" class="btn-secondary">← Volver</a>
                <a href="nueva_vacante.php" class="btn-primary">
                    <span class="btn-text">+ Agregar Nueva</span>
                    <span class="btn-glow"></span>
                </a>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <strong>✓</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <strong>⚠️</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Lista de Vacantes -->
        <?php if (empty($vacantes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p class="empty-state-text">No hay vacantes registradas aún.</p>
                <a href="nueva_vacante.php" class="btn-primary">
                    <span class="btn-text">Crear Primera Vacante</span>
                    <span class="btn-glow"></span>
                </a>
            </div>
        <?php else: ?>
            <div class="vacantes-grid">
                <?php foreach ($vacantes as $vacante): ?>
                    <div class="vacante-card">
                        <div class="vacante-header">
                            <h2 class="vacante-titulo"><?php echo htmlspecialchars($vacante['titulo']); ?></h2>
                            <div class="vacante-badges">
                                <span class="badge <?php echo $vacante['activo'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $vacante['activo'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($vacante['imagen_flyer']): ?>
                            <img src="../<?php echo htmlspecialchars($vacante['imagen_flyer']); ?>" 
                                 alt="<?php echo htmlspecialchars($vacante['titulo']); ?>" 
                                 class="vacante-imagen">
                        <?php endif; ?>
                        
                        <div class="vacante-descripcion">
                            <?php echo nl2br(htmlspecialchars($vacante['descripcion'])); ?>
                        </div>
                        
                        <div class="vacante-fechas">
                            <div>
                                <strong>Inicio:</strong> <?php echo date('d/m/Y', strtotime($vacante['fecha_inicio'])); ?>
                            </div>
                            <div>
                                <strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($vacante['fecha_fin'])); ?>
                            </div>
                        </div>
                        
                        <div class="vacante-actions">
                            <a href="nueva_vacante.php?id=<?php echo $vacante['id']; ?>" class="btn-secondary">Editar</a>
                            <a href="?eliminar=<?php echo $vacante['id']; ?>" 
                               class="btn-danger"
                               onclick="return confirm('¿Estás seguro de eliminar esta vacante?');">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
