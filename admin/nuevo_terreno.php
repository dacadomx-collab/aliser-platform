<?php
/**
 * ALISER - Panel de Administración
 * Crear/Editar Terreno
 * 
 * @package ALISER
 * @version 1.0.0
 */

require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER', 'BIENES']);

// Definir constante antes de incluir db.php
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

// Incluir archivo de conexión a base de datos
require_once __DIR__ . '/includes/db.php';
// Incluir helper de procesamiento de imágenes
require_once __DIR__ . '/includes/image_helper.php';

// Obtener datos del usuario de la sesión
$admin_nombre = isset($_SESSION['admin_nombre']) ? $_SESSION['admin_nombre'] : 'Usuario';
$admin_rol = isset($_SESSION['admin_rol']) ? $_SESSION['admin_rol'] : 'admin';

// Variables
$terreno = null;
$editar = false;
$error_message = '';
$success_message = '';

// Si hay ID, estamos editando
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editar = true;
    $terreno_id = (int)$_GET['id'];
    try {
        $db = getDB();
        $terreno = $db->fetchOne("SELECT * FROM terrenos WHERE id = :id", ['id' => $terreno_id]);
        if (!$terreno) {
            $error_message = 'Terreno no encontrado.';
        }
    } catch (Exception $e) {
        $error_message = 'Error al cargar el terreno.';
        error_log('Error cargando terreno: ' . $e->getMessage());
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
    $superficie = isset($_POST['superficie']) ? trim($_POST['superficie']) : '';
    $precio_sugerido = isset($_POST['precio_sugerido']) ? trim($_POST['precio_sugerido']) : null;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : 'disponible';
    
    // Validaciones
    if (empty($ubicacion) || empty($superficie) || empty($descripcion)) {
        $error_message = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!is_numeric($superficie) || floatval($superficie) <= 0) {
        $error_message = 'La superficie debe ser un número mayor a cero.';
    } elseif ($precio_sugerido !== null && $precio_sugerido !== '' && (!is_numeric($precio_sugerido) || floatval($precio_sugerido) < 0)) {
        $error_message = 'El precio sugerido debe ser un número válido.';
    } elseif (!in_array($estatus, ['disponible', 'en_evaluacion', 'adquirido', 'rechazado'])) {
        $error_message = 'El estatus seleccionado no es válido.';
    } else {
        try {
            $db = getDB();
            $imagen_terreno = null;
            
            // Procesar subida de imagen usando ImageHelper (conversión a WebP)
            if (isset($_FILES['imagen_terreno']) && $_FILES['imagen_terreno']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Validar imagen
                    ImageHelper::validateImage($_FILES['imagen_terreno']);
                    
                    // Procesar y convertir a WebP - guardar en assets/img/terrenos/
                    $image_result = ImageHelper::processAndConvertToWebP($_FILES['imagen_terreno'], 'assets/img/terrenos/');
                    
                    if ($image_result && isset($image_result['path'])) {
                        $imagen_terreno = $image_result['path'];
                        
                        // Si estamos editando y hay una imagen anterior, eliminarla
                        if ($editar && $terreno && $terreno['imagen_terreno']) {
                            ImageHelper::deleteImage($terreno['imagen_terreno']);
                        }
                    } else {
                        throw new Exception('Error al procesar la imagen.');
                    }
                } catch (Exception $img_exception) {
                    throw new Exception($img_exception->getMessage());
                }
            } elseif ($editar && $terreno) {
                // Si estamos editando y no se subió nueva imagen, mantener la anterior
                $imagen_terreno = $terreno['imagen_terreno'];
            }
            
            // Convertir valores numéricos
            $superficie = floatval($superficie);
            $precio_sugerido = ($precio_sugerido !== null && $precio_sugerido !== '') ? floatval($precio_sugerido) : null;
            
            // Insertar o actualizar
            if ($editar && $terreno) {
                // Actualizar
                $sql = "UPDATE terrenos 
                        SET ubicacion = :ubicacion, 
                            superficie = :superficie, 
                            precio_sugerido = :precio_sugerido, 
                            descripcion = :descripcion, 
                            estatus = :estatus,
                            actualizado_en = NOW()";
                
                $params = [
                    'ubicacion' => $ubicacion,
                    'superficie' => $superficie,
                    'precio_sugerido' => $precio_sugerido,
                    'descripcion' => $descripcion,
                    'estatus' => $estatus,
                    'id' => $terreno['id']
                ];
                
                if ($imagen_terreno !== null) {
                    $sql .= ", imagen_terreno = :imagen_terreno";
                    $params['imagen_terreno'] = $imagen_terreno;
                }
                
                $sql .= " WHERE id = :id";
                
                $db->query($sql, $params);
                $success_message = 'Terreno actualizado correctamente.';
            } else {
                // Insertar nuevo
                if ($imagen_terreno === null) {
                    $error_message = 'Debes subir una imagen del terreno.';
                } else {
                    $sql = "INSERT INTO terrenos (ubicacion, superficie, precio_sugerido, imagen_terreno, descripcion, estatus) 
                            VALUES (:ubicacion, :superficie, :precio_sugerido, :imagen_terreno, :descripcion, :estatus)";
                    
                    $db->query($sql, [
                        'ubicacion' => $ubicacion,
                        'superficie' => $superficie,
                        'precio_sugerido' => $precio_sugerido,
                        'imagen_terreno' => $imagen_terreno,
                        'descripcion' => $descripcion,
                        'estatus' => $estatus
                    ]);
                    
                    $success_message = 'Terreno creado correctamente.';
                }
            }
            
            // Redirigir después de éxito
            if (!empty($success_message)) {
                header('Location: terrenos.php?success=1');
                exit;
            }
            
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            error_log('Error guardando terreno: ' . $e->getMessage());
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
    
    <title><?php echo $editar ? 'Editar' : 'Nuevo'; ?> Terreno - ALISER</title>
    
    <!-- Estilos del Panel de Administración -->
    <link rel="stylesheet" href="css/admin-style.css">
    <!-- Estilos Modulares del Módulo de Vacantes (reutilizables) -->
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <!-- Header -->
        <div class="admin-header-main">
            <h1 class="admin-title"><?php echo $editar ? '✏️ Editar Terreno' : '➕ Nuevo Terreno'; ?></h1>
            <p class="admin-subtitle"><?php echo $editar ? 'Modifica los datos del terreno' : 'Completa el formulario para registrar un nuevo terreno'; ?></p>
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
        <div class="admin-content-card">
            <form method="POST" enctype="multipart/form-data">
                <!-- Ubicación -->
                <div class="form-group">
                    <label for="ubicacion" class="form-label">
                        Ubicación <span class="required">*</span>
                    </label>
                    <div class="ubicacion-wrapper">
                        <input
                            type="text"
                            id="ubicacion"
                            name="ubicacion"
                            class="form-input"
                            placeholder="Ej: Calle Principal #123, Colonia Centro, La Paz, BCS"
                            value="<?php echo $terreno ? htmlspecialchars($terreno['ubicacion']) : ''; ?>"
                            required
                        >
                        <div id="ubicacionFeedback" class="ubicacion-feedback is-hidden">
                            <span class="ubicacion-check">✓</span>
                            <span class="ubicacion-message">Ubicación detectada</span>
                        </div>
                    </div>
                </div>

                <!-- Superficie y Precio -->
                <div class="form-dates-grid">
                    <div class="form-group">
                        <label for="superficie" class="form-label">
                            Superficie (m²) <span class="required">*</span>
                        </label>
                        <input
                            type="number"
                            id="superficie"
                            name="superficie"
                            class="form-input"
                            placeholder="Ej: 500.00"
                            step="0.01"
                            min="0.01"
                            value="<?php echo $terreno ? htmlspecialchars($terreno['superficie']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="precio_sugerido" class="form-label">
                            Precio Sugerido (MXN)
                        </label>
                        <input
                            type="number"
                            id="precio_sugerido"
                            name="precio_sugerido"
                            class="form-input"
                            placeholder="Ej: 1500000.00"
                            step="0.01"
                            min="0"
                            value="<?php echo $terreno && $terreno['precio_sugerido'] ? htmlspecialchars($terreno['precio_sugerido']) : ''; ?>"
                        >
                    </div>
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
                        placeholder="Describe las características del terreno, servicios disponibles, accesos, etc..."
                        required
                    ><?php echo $terreno ? htmlspecialchars($terreno['descripcion']) : ''; ?></textarea>
                </div>

                <!-- Imagen Terreno -->
                <div class="form-group">
                    <label for="imagen_terreno" class="form-label">
                        Imagen del Terreno <?php echo $editar ? '' : '<span class="required">*</span>'; ?>
                    </label>
                    <div class="form-file-wrapper">
                        <input
                            type="file"
                            id="imagen_terreno"
                            name="imagen_terreno"
                            class="form-file-input"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            <?php echo $editar ? '' : 'required'; ?>
                            onchange="previewImage(this)"
                        >
                        <small class="form-help-text">
                            Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5MB. Se convertirá automáticamente a WebP.
                        </small>
                    </div>
                    <div class="form-file-preview" id="imagePreview">
                        <?php if ($editar && $terreno && $terreno['imagen_terreno']): ?>
                            <img src="../<?php echo htmlspecialchars($terreno['imagen_terreno']); ?>" 
                                 alt="Imagen actual" 
                                 loading="lazy"
                                 class="loaded">
                            <p class="form-help-text">
                                Imagen actual. Sube una nueva para reemplazarla.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estatus -->
                <div class="form-group">
                    <label for="estatus" class="form-label">
                        Estatus <span class="required">*</span>
                    </label>
                    <select
                        id="estatus"
                        name="estatus"
                        class="form-input"
                        required
                    >
                        <option value="disponible" <?php echo ($terreno && $terreno['estatus'] === 'disponible') ? 'selected' : ''; ?>>
                            Disponible
                        </option>
                        <option value="en_evaluacion" <?php echo ($terreno && $terreno['estatus'] === 'en_evaluacion') ? 'selected' : ''; ?>>
                            En Evaluación
                        </option>
                        <option value="adquirido" <?php echo ($terreno && $terreno['estatus'] === 'adquirido') ? 'selected' : ''; ?>>
                            Adquirido
                        </option>
                        <option value="rechazado" <?php echo ($terreno && $terreno['estatus'] === 'rechazado') ? 'selected' : ''; ?>>
                            Rechazado
                        </option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="form-actions">
                    <a href="terrenos.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="login-btn">
                        <span class="btn-text"><?php echo $editar ? 'Actualizar Terreno' : 'Crear Terreno'; ?></span>
                        <span class="btn-glow"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /**
         * Detector de Google Maps - Extracción de ubicación
         * Detecta URLs de Google Maps y extrae coordenadas o nombre del lugar
         */
        (function() {
            const ubicacionInput = document.getElementById('ubicacion');
            const feedbackElement = document.getElementById('ubicacionFeedback');
            
            if (!ubicacionInput || !feedbackElement) return;
            
            /**
             * Patrones de URLs de Google Maps
             */
            const googleMapsPatterns = {
                // https://maps.google.com/?q=lat,lng
                queryCoords: /maps\.google\.com\/\?q=(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                // https://www.google.com/maps/place/.../@lat,lng
                placeCoords: /maps\/place\/[^@]+@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                // https://www.google.com/maps/@lat,lng
                directCoords: /maps\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                // https://maps.app.goo.gl/... (short link)
                shortLink: /maps\.app\.goo\.gl\/[^\s]+/,
                // https://www.google.com/maps/place/Nombre+del+Lugar
                placeName: /maps\/place\/([^/@]+)/,
                // https://maps.google.com/?q=Nombre+del+Lugar
                queryName: /maps\.google\.com\/\?q=([^&]+)/
            };
            
            /**
             * Decodifica URL encoding
             */
            function decodeUrl(text) {
                try {
                    return decodeURIComponent(text.replace(/\+/g, ' '));
                } catch (e) {
                    return text.replace(/\+/g, ' ');
                }
            }
            
            /**
             * Extrae información de una URL de Google Maps
             */
            function extractLocationFromUrl(url) {
                let match;
                
                // Intentar extraer coordenadas
                match = url.match(googleMapsPatterns.queryCoords);
                if (match) {
                    return {
                        type: 'coords',
                        lat: parseFloat(match[1]),
                        lng: parseFloat(match[2]),
                        text: `${match[1]}, ${match[2]}`
                    };
                }
                
                match = url.match(googleMapsPatterns.placeCoords);
                if (match) {
                    return {
                        type: 'coords',
                        lat: parseFloat(match[1]),
                        lng: parseFloat(match[2]),
                        text: `${match[1]}, ${match[2]}`
                    };
                }
                
                match = url.match(googleMapsPatterns.directCoords);
                if (match) {
                    return {
                        type: 'coords',
                        lat: parseFloat(match[1]),
                        lng: parseFloat(match[2]),
                        text: `${match[1]}, ${match[2]}`
                    };
                }
                
                // Intentar extraer nombre del lugar
                match = url.match(googleMapsPatterns.placeName);
                if (match) {
                    return {
                        type: 'name',
                        text: decodeUrl(match[1])
                    };
                }
                
                match = url.match(googleMapsPatterns.queryName);
                if (match) {
                    return {
                        type: 'name',
                        text: decodeUrl(match[1])
                    };
                }
                
                // Para short links, intentar extraer del texto completo
                if (googleMapsPatterns.shortLink.test(url)) {
                    return {
                        type: 'link',
                        text: 'Enlace de Google Maps detectado'
                    };
                }
                
                return null;
            }
            
            /**
             * Muestra feedback de ubicación detectada
             */
            function showFeedback(message) {
                const messageElement = feedbackElement.querySelector('.ubicacion-message');
                if (messageElement) {
                    messageElement.textContent = message || 'Ubicación detectada';
                }
                
                feedbackElement.style.display = 'flex';
                feedbackElement.classList.remove('fade-out');
                
                // Ocultar después de 3 segundos
                setTimeout(() => {
                    feedbackElement.classList.add('fade-out');
                    setTimeout(() => {
                        feedbackElement.style.display = 'none';
                    }, 300);
                }, 3000);
            }
            
            /**
             * Procesa el valor del input para detectar URLs de Google Maps
             */
            function processInput() {
                const value = ubicacionInput.value.trim();
                
                // Verificar si contiene una URL de Google Maps
                if (value.includes('maps.google.com') || 
                    value.includes('google.com/maps') || 
                    value.includes('maps.app.goo.gl') ||
                    value.includes('goo.gl/maps')) {
                    
                    const location = extractLocationFromUrl(value);
                    
                    if (location) {
                        // Limpiar el campo y mostrar la información extraída
                        if (location.type === 'coords') {
                            ubicacionInput.value = location.text;
                            showFeedback(`Coordenadas detectadas: ${location.text}`);
                        } else if (location.type === 'name') {
                            ubicacionInput.value = location.text;
                            showFeedback(`Lugar detectado: ${location.text}`);
                        } else {
                            showFeedback('Enlace de Google Maps detectado');
                        }
                    }
                }
            }
            
            /**
             * Maneja el evento de pegar
             */
            ubicacionInput.addEventListener('paste', function(e) {
                // Pequeño delay para que el valor se actualice
                setTimeout(() => {
                    processInput();
                }, 10);
            });
            
            /**
             * Maneja cambios en el input (para detectar cuando se pega)
             */
            ubicacionInput.addEventListener('input', function() {
                // Solo procesar si parece una URL
                const value = ubicacionInput.value.trim();
                if (value.startsWith('http') || value.includes('maps')) {
                    processInput();
                }
            });
            
            /**
             * También detectar cuando se suelta el mouse (para drag & drop)
             */
            ubicacionInput.addEventListener('drop', function() {
                setTimeout(() => {
                    processInput();
                }, 10);
            });
        })();
        
        /**
         * Preview de imagen con lazy loading
         */
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Vista previa';
                    img.loading = 'lazy';
                    img.className = 'loaded';
                    
                    preview.innerHTML = '';
                    preview.appendChild(img);
                    
                    // Agregar texto informativo
                    const text = document.createElement('p');
                    text.className = 'form-help-text';
                    text.textContent = 'Vista previa de la imagen que se subirá.';
                    preview.appendChild(text);
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
        
        /**
         * Lazy loading para imágenes existentes
         */
        document.addEventListener('DOMContentLoaded', function() {
            const lazyImages = document.querySelectorAll('img[loading="lazy"]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                lazyImages.forEach(function(img) {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback para navegadores sin IntersectionObserver
                lazyImages.forEach(function(img) {
                    img.classList.add('loaded');
                });
            }
        });
    </script>
</body>
</html>
