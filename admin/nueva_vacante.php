<?php
/**
 * ALISER - Panel de Administración
 * Crear/Editar Vacante
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

// Variables
$vacante = null;
$editar = false;
$error_message = '';
$success_message = '';

// Si hay ID, estamos editando
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editar = true;
    $vacante_id = (int)$_GET['id'];
    try {
        $db = getDB();
        $vacante = $db->fetchOne("SELECT * FROM vacantes WHERE id = :id", ['id' => $vacante_id]);
        if (!$vacante) {
            $error_message = 'Vacante no encontrada.';
        }
    } catch (Exception $e) {
        $error_message = 'Error al cargar la vacante.';
        error_log('Error cargando vacante: ' . $e->getMessage());
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
    $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($titulo) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_fin)) {
        $error_message = 'Por favor, completa todos los campos obligatorios.';
    } elseif (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        $error_message = 'La fecha de fin debe ser posterior a la fecha de inicio.';
    } else {
        try {
            $db = getDB();
            $imagen_flyer = null;
            
            // Procesar subida de imagen
            if (isset($_FILES['imagen_flyer']) && $_FILES['imagen_flyer']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['imagen_flyer'];
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                // Validar tipo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mime_type, $allowed_types)) {
                    throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG o WEBP.');
                }
                
                // Validar tamaño
                if ($file['size'] > $max_size) {
                    throw new Exception('El archivo es demasiado grande. Máximo 5MB.');
                }
                
                // Generar nombre único
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $nombre_archivo = 'vacante_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = '../assets/img/vacantes/' . $nombre_archivo;
                
                // Crear directorio si no existe
                $directorio = dirname($ruta_destino);
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }
                
                // Mover archivo
                if (move_uploaded_file($file['tmp_name'], $ruta_destino)) {
                    $imagen_flyer = 'assets/img/vacantes/' . $nombre_archivo;
                    
                    // Si estamos editando y hay una imagen anterior, eliminarla
                    if ($editar && $vacante && $vacante['imagen_flyer'] && file_exists('../' . $vacante['imagen_flyer'])) {
                        @unlink('../' . $vacante['imagen_flyer']);
                    }
                } else {
                    throw new Exception('Error al subir la imagen.');
                }
            } elseif ($editar && $vacante) {
                // Si estamos editando y no se subió nueva imagen, mantener la anterior
                $imagen_flyer = $vacante['imagen_flyer'];
            }
            
            // Insertar o actualizar
            if ($editar && $vacante) {
                // Actualizar
                $sql = "UPDATE vacantes 
                        SET titulo = :titulo, 
                            descripcion = :descripcion, 
                            fecha_inicio = :fecha_inicio, 
                            fecha_fin = :fecha_fin, 
                            activo = :activo,
                            actualizado_en = NOW()";
                
                $params = [
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'activo' => $activo,
                    'id' => $vacante['id']
                ];
                
                if ($imagen_flyer !== null) {
                    $sql .= ", imagen_flyer = :imagen_flyer";
                    $params['imagen_flyer'] = $imagen_flyer;
                }
                
                $sql .= " WHERE id = :id";
                
                $db->query($sql, $params);
                $success_message = 'Vacante actualizada correctamente.';
            } else {
                // Insertar nuevo
                if ($imagen_flyer === null) {
                    $error_message = 'Debes subir una imagen del flyer.';
                } else {
                    $sql = "INSERT INTO vacantes (titulo, descripcion, imagen_flyer, fecha_inicio, fecha_fin, activo) 
                            VALUES (:titulo, :descripcion, :imagen_flyer, :fecha_inicio, :fecha_fin, :activo)";
                    
                    $db->query($sql, [
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'imagen_flyer' => $imagen_flyer,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin' => $fecha_fin,
                        'activo' => $activo
                    ]);
                    
                    $success_message = 'Vacante creada correctamente.';
                }
            }
            
            // Redirigir después de éxito
            if (!empty($success_message)) {
                header('Location: vacantes.php?success=1');
                exit;
            }
            
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            error_log('Error guardando vacante: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    
    <title><?php echo $editar ? 'Editar' : 'Nueva'; ?> Vacante - ALISER</title>
    
    <!-- Estilos del Panel de Administración -->
    <link rel="stylesheet" href="css/admin-style.css">
    
    <style>
        .admin-container {
            min-height: 100vh;
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .admin-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--aliser-green-primary);
            margin: 0 0 0.5rem 0;
        }
        
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--aliser-green-dark);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-label .required {
            color: #dc3545;
        }
        
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 2px solid rgba(37, 103, 55, 0.15);
            border-radius: 8px;
            font-family: var(--font-family-primary);
            font-size: 1rem;
            color: var(--color-gray-dark);
            background: var(--color-white);
            transition: all var(--transition-base);
            outline: none;
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-input:focus,
        .form-textarea:focus {
            border-color: var(--aliser-teal-tertiary);
            box-shadow: 0 0 0 4px rgba(67, 145, 132, 0.1);
        }
        
        .form-file-wrapper {
            position: relative;
        }
        
        .form-file-input {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 2px dashed rgba(37, 103, 55, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all var(--transition-base);
        }
        
        .form-file-input:hover {
            border-color: var(--aliser-teal-tertiary);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-file-preview {
            margin-top: 1rem;
        }
        
        .form-file-preview img {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
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
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: rgba(102, 102, 102, 0.2);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1 class="admin-title"><?php echo $editar ? '✏️ Editar Vacante' : '➕ Nueva Vacante'; ?></h1>
            <p style="color: var(--color-gray); margin: 0;"><?php echo $editar ? 'Modifica los datos de la vacante' : 'Completa el formulario para crear una nueva vacante'; ?></p>
        </div>

        <!-- Mensajes -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <strong>⚠️</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <strong>✓</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <!-- Título -->
                <div class="form-group">
                    <label for="titulo" class="form-label">
                        Título <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        class="form-input"
                        placeholder="Ej: Gerente de Tienda"
                        value="<?php echo $vacante ? htmlspecialchars($vacante['titulo']) : ''; ?>"
                        required
                    >
                </div>

                <!-- Descripción -->
                <div class="form-group">
                    <label for="descripcion" class="form-label">
                        Descripción <span class="required">*</span>
                    </label>
                    <textarea
                        id="descripcion"
                        name="descripcion"
                        class="form-textarea"
                        placeholder="Describe los requisitos, responsabilidades y beneficios de la vacante..."
                        required
                    ><?php echo $vacante ? htmlspecialchars($vacante['descripcion']) : ''; ?></textarea>
                </div>

                <!-- Imagen Flyer -->
                <div class="form-group">
                    <label for="imagen_flyer" class="form-label">
                        Imagen del Flyer <?php echo $editar ? '' : '<span class="required">*</span>'; ?>
                    </label>
                    <div class="form-file-wrapper">
                        <input
                            type="file"
                            id="imagen_flyer"
                            name="imagen_flyer"
                            class="form-file-input"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            <?php echo $editar ? '' : 'required'; ?>
                            onchange="previewImage(this)"
                        >
                        <small style="display: block; margin-top: 0.5rem; color: var(--color-gray);">
                            Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5MB
                        </small>
                    </div>
                    <div class="form-file-preview" id="imagePreview">
                        <?php if ($editar && $vacante && $vacante['imagen_flyer']): ?>
                            <img src="../<?php echo htmlspecialchars($vacante['imagen_flyer']); ?>" 
                                 alt="Imagen actual" 
                                 style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 1rem;">
                            <p style="font-size: 0.875rem; color: var(--color-gray); margin-top: 0.5rem;">
                                Imagen actual. Sube una nueva para reemplazarla.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fechas -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="fecha_inicio" class="form-label">
                            Fecha de Inicio <span class="required">*</span>
                        </label>
                        <input
                            type="date"
                            id="fecha_inicio"
                            name="fecha_inicio"
                            class="form-input"
                            value="<?php echo $vacante ? $vacante['fecha_inicio'] : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin" class="form-label">
                            Fecha de Fin <span class="required">*</span>
                        </label>
                        <input
                            type="date"
                            id="fecha_fin"
                            name="fecha_fin"
                            class="form-input"
                            value="<?php echo $vacante ? $vacante['fecha_fin'] : ''; ?>"
                            required
                        >
                    </div>
                </div>

                <!-- Activo -->
                <div class="form-group">
                    <div class="form-checkbox-wrapper">
                        <input
                            type="checkbox"
                            id="activo"
                            name="activo"
                            class="form-checkbox"
                            <?php echo ($vacante && $vacante['activo']) || !$editar ? 'checked' : ''; ?>
                        >
                        <label for="activo" class="form-label" style="margin: 0; text-transform: none;">
                            Vacante activa (visible en el sitio)
                        </label>
                    </div>
                </div>

                <!-- Botones -->
                <div class="form-actions">
                    <a href="vacantes.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="login-btn">
                        <span class="btn-text"><?php echo $editar ? 'Actualizar Vacante' : 'Crear Vacante'; ?></span>
                        <span class="btn-glow"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Vista previa" style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 1rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</body>
</html>
