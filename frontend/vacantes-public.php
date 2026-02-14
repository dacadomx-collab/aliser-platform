<?php
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/../admin/includes/db.php';

$vacantes = [];
$error_message = '';

try {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT id, titulo, sucursal, descripcion, imagen_flyer, fecha_fin, estatus, activo, creado_en
            FROM vacantes
            WHERE estatus = :estatus AND activo = 1
            ORDER BY (fecha_fin IS NULL) ASC, fecha_fin ASC, creado_en DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([':estatus' => 'activa']);
    $vacantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO en vacantes-public.php: ' . $e->getMessage());
    $error_message = 'No fue posible cargar las vacantes en este momento.';
}

function buildBaseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function absoluteUrl(string $path): string
{
    return rtrim(buildBaseUrl(), '/') . '/' . ltrim($path, '/');
}

function truncateText(string $text, int $max = 150): string
{
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '...' : $text;
    }
    return strlen($text) > $max ? substr($text, 0, $max) . '...' : $text;
}

$defaultOgTitle = 'Vacantes ALISER | Trabaja con Nosotros';
$defaultOgDesc = 'Vacantes activas ALISER en Baja California Sur. Postulate y crece con nosotros.';
$defaultOgImage = absoluteUrl('assets/img/vacantes/no-image.webp');
$defaultOgUrl = absoluteUrl('frontend/vacantes-public.php');

$ogTitle = $defaultOgTitle;
$ogDesc = $defaultOgDesc;
$ogImage = $defaultOgImage;
$ogUrl = $defaultOgUrl;

$requestedVacanteId = isset($_GET['vacante']) && is_numeric($_GET['vacante']) ? (int)$_GET['vacante'] : null;
$ogVacante = null;

if (!empty($vacantes)) {
    if ($requestedVacanteId !== null) {
        foreach ($vacantes as $item) {
            if ((int)$item['id'] === $requestedVacanteId) {
                $ogVacante = $item;
                break;
            }
        }
    }

    if ($ogVacante === null) {
        $ogVacante = $vacantes[0];
    }
}

if ($ogVacante !== null) {
    $ogTitle = (string)$ogVacante['titulo'] . ' | Vacantes ALISER';
    $ogDesc = truncateText((string)$ogVacante['descripcion'], 180);
    $ogUrl = absoluteUrl('frontend/vacantes-public.php?vacante=' . (int)$ogVacante['id']);

    $ogImgName = basename((string)($ogVacante['imagen_flyer'] ?? ''));
    if ($ogImgName !== '' && file_exists(dirname(__DIR__) . '/assets/img/vacantes/' . $ogImgName)) {
        $ogImage = absoluteUrl('assets/img/vacantes/' . $ogImgName);
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Vacantes activas ALISER. Postúlate a nuestras oportunidades laborales en Baja California Sur.">
  <meta name="robots" content="index, follow">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($ogDesc); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($ogUrl); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
  <meta property="og:locale" content="es_MX">
  <title>Vacantes ALISER | Trabaja con Nosotros</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="src/css/main.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .vacantes-public-main {
      padding: 2rem 1rem 3rem;
      background: radial-gradient(circle at top right, rgba(236, 212, 168, 0.18), transparent 50%),
                  radial-gradient(circle at bottom left, rgba(67, 145, 132, 0.22), transparent 48%);
      min-height: 70vh;
    }

    .vacantes-public-wrap {
      max-width: 1200px;
      margin: 0 auto;
    }

    .vacantes-public-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .vacantes-public-title {
      color: #256737;
      font-size: clamp(1.8rem, 2.6vw, 2.4rem);
      margin-bottom: 0.4rem;
    }

    .vacantes-public-subtitle {
      color: #34534a;
      font-size: 1rem;
    }

    .vacantes-public-manifiesto {
      margin: 0.9rem auto 0;
      max-width: 940px;
      font-size: 1.02rem;
      line-height: 1.55;
      color: #2f4e43;
      font-style: italic;
      font-weight: 500;
    }

    .vacantes-filters {
      margin: 1.4rem 0 1.8rem;
      padding: 1rem;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.62);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.62);
      box-shadow: 0 8px 20px rgba(24, 67, 37, 0.12);
      display: grid;
      gap: 0.75rem;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }

    .filter-label {
      font-size: 0.82rem;
      font-weight: 700;
      color: #256737;
      letter-spacing: 0.3px;
      text-transform: uppercase;
    }

    .filter-input {
      border: 1px solid rgba(37, 103, 55, 0.25);
      background: rgba(255, 255, 255, 0.88);
      color: #204135;
      border-radius: 10px;
      height: 42px;
      padding: 0 0.75rem;
      font-size: 0.93rem;
      outline: none;
    }

    .filter-input:focus {
      border-color: #439184;
      box-shadow: 0 0 0 3px rgba(67, 145, 132, 0.18);
    }

    .vacantes-public-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
      gap: 1rem;
    }

    .vacante-public-card {
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: 0 12px 30px rgba(24, 67, 37, 0.15);
      border-radius: 16px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      min-height: 100%;
    }

    .vacante-public-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-bottom: 1px solid rgba(37, 103, 55, 0.12);
      cursor: zoom-in;
    }

    .vacante-public-content {
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.6rem;
      flex: 1;
    }

    .vacante-public-title-text {
      color: #256737;
      font-size: 1.15rem;
      line-height: 1.25;
      margin: 0;
    }

    .vacante-public-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }

    .vacante-badge {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: 0.25rem 0.7rem;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.3px;
      border: 1px solid transparent;
    }

    .vacante-badge-sucursal {
      background: rgba(67, 145, 132, 0.16);
      border-color: rgba(67, 145, 132, 0.3);
      color: #1f5b53;
    }

    .vacante-badge-urgente {
      background: linear-gradient(90deg, #e8bf6c, #ffe7b8, #e8bf6c);
      background-size: 200% 100%;
      color: #694503;
      border-color: rgba(201, 142, 27, 0.45);
      animation: badgeShimmer 2.2s linear infinite;
    }

    .vacante-public-desc {
      color: #3a4f44;
      font-size: 0.93rem;
      line-height: 1.45;
      margin: 0;
    }

    .vacante-public-fecha {
      font-size: 0.84rem;
      color: #2d6252;
      font-weight: 600;
    }

    .vacante-public-postular {
      margin-top: auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
      text-decoration: none;
      background: linear-gradient(135deg, #256737, #439184);
      color: #fff;
      border-radius: 10px;
      padding: 0.7rem 0.9rem;
      font-weight: 700;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .vacante-public-postular:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 18px rgba(37, 103, 55, 0.28);
    }

    .vacante-whatsapp-cta {
      margin-top: auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      text-decoration: none;
      background: linear-gradient(135deg, #1f9f55, #25d366);
      color: #fff;
      border-radius: 10px;
      padding: 0.7rem 0.9rem;
      font-weight: 800;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      box-shadow: 0 8px 18px rgba(37, 211, 102, 0.26);
    }

    .vacante-whatsapp-cta:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 22px rgba(37, 211, 102, 0.34);
    }

    .share-block {
      margin-top: 0.55rem;
      background: rgba(255, 255, 255, 0.72);
      border: 1px solid rgba(37, 103, 55, 0.16);
      border-radius: 10px;
      padding: 0.55rem;
    }

    .share-title {
      margin: 0 0 0.45rem;
      font-size: 0.78rem;
      color: #2a5849;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.25px;
    }

    .share-actions {
      display: flex;
      gap: 0.35rem;
      flex-wrap: wrap;
    }

    .share-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      text-decoration: none;
      border: 1px solid transparent;
      border-radius: 8px;
      padding: 0.36rem 0.58rem;
      font-size: 0.78rem;
      font-weight: 700;
      cursor: pointer;
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    .share-facebook {
      background: rgba(24, 119, 242, 0.2);
      color: #0f4fb4;
      border-color: rgba(24, 119, 242, 0.35);
      box-shadow: 0 0 12px rgba(24, 119, 242, 0.2);
    }

    .share-whatsapp {
      background: rgba(37, 211, 102, 0.2);
      color: #138f46;
      border-color: rgba(37, 211, 102, 0.36);
      box-shadow: 0 0 12px rgba(37, 211, 102, 0.2);
    }

    .share-copy {
      background: rgba(255, 255, 255, 0.82);
      color: #24463b;
      border-color: rgba(36, 70, 59, 0.25);
    }

    .share-copy.copied {
      background: rgba(67, 145, 132, 0.25);
      border-color: rgba(67, 145, 132, 0.45);
    }

    .vacantes-public-empty,
    .vacantes-public-error {
      background: rgba(255, 255, 255, 0.85);
      border-radius: 12px;
      padding: 1rem;
      text-align: center;
      color: #4a4a4a;
      border: 1px solid rgba(37, 103, 55, 0.12);
    }

    .vacantes-public-error {
      color: #8a2c2c;
      border-color: rgba(138, 44, 44, 0.25);
    }

    .vacantes-public-empty {
      grid-column: 1 / -1;
    }

    .flyer-lightbox {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.9);
      z-index: 9999;
      padding: 1rem;
    }

    .flyer-lightbox.is-open {
      display: flex;
    }

    .flyer-lightbox-content {
      position: relative;
      width: min(1100px, 100%);
      max-height: 95vh;
      display: flex;
      flex-direction: column;
      gap: 0.6rem;
    }

    .flyer-lightbox-image {
      width: 100%;
      max-height: 86vh;
      object-fit: contain;
      border-radius: 12px;
      background: #111;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .flyer-lightbox-actions {
      display: flex;
      justify-content: center;
      gap: 0.6rem;
      flex-wrap: wrap;
    }

    .flyer-action-btn {
      background: rgba(255, 255, 255, 0.95);
      color: #174335;
      border: 1px solid rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      padding: 0.55rem 0.9rem;
      text-decoration: none;
      font-weight: 700;
      cursor: pointer;
    }

    .flyer-share-panel {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 10px;
      padding: 0.65rem;
      border: 1px solid rgba(255, 255, 255, 0.88);
    }

    .flyer-close-btn {
      position: absolute;
      top: -6px;
      right: -6px;
      width: 36px;
      height: 36px;
      border-radius: 999px;
      border: 0;
      background: rgba(255, 255, 255, 0.95);
      color: #1c4337;
      font-size: 1.2rem;
      font-weight: 700;
      cursor: pointer;
    }

    @keyframes badgeShimmer {
      0% { background-position: 100% 0; }
      100% { background-position: -100% 0; }
    }
  </style>
</head>
<body>
  <header class="header-top">
    <div class="header-top-container">
      <div class="header-brand">
        <a href="index.html#hero" class="header-logo" aria-label="ALISER - Inicio">
          <img src="assets/img/logo/logo.png" alt="ALISER - Tienda de Autoservicio" class="logo-img logo-img-top">
        </a>
        <p class="header-slogan">LA UNICA CADENA DE SUDCALIFORNIA</p>
      </div>
    </div>
  </header>

  <nav class="nav-bar" role="navigation" aria-label="Navegacion principal">
    <div class="nav-bar-container">
      <ul class="nav-menu">
        <li class="nav-item nav-item-home"><a href="index.html#hero" class="nav-link nav-link-home"><i class="fas fa-home" aria-hidden="true"></i><span>Inicio</span></a></li>
        <li class="nav-item"><a href="index.html#promociones" class="nav-link">Promociones</a></li>
        <li class="nav-item"><a href="index.html#terrenos-section" class="nav-link">Terrenos</a></li>
        <li class="nav-item nav-item-right"><a href="vacantes-public.php" class="nav-link">Trabaja con Nosotros</a></li>
      </ul>
    </div>
  </nav>

  <main class="vacantes-public-main">
    <div class="vacantes-public-wrap">
      <div class="vacantes-public-header">
        <h1 class="vacantes-public-title">Vacantes Activas ALISER</h1>
        <p class="vacantes-public-subtitle">Ordenadas por urgencia para que encuentres primero las oportunidades más próximas a vencer.</p>
        <p class="vacantes-public-manifiesto">
          En ALISER, no solo llenamos anaqueles, construimos el futuro de nuestra media península. Somos la única cadena 100% sudcaliforniana y buscamos gente con garra, que ame nuestra tierra y quiera crecer junto a una empresa que apoya a su comunidad. ¡Súmate al equipo que mueve a la Baja!
        </p>
      </div>

      <section class="vacantes-filters" aria-label="Filtros de vacantes">
        <div class="filter-group">
          <label for="filtro-sucursal" class="filter-label">Por Tienda/Sucursal</label>
          <select id="filtro-sucursal" class="filter-input">
            <option value="">Todas</option>
            <option value="Matriz">Matriz</option>
            <option value="La Paz">La Paz</option>
            <option value="Los Cabos">Los Cabos</option>
            <option value="Constitución">Constitución</option>
          </select>
        </div>
        <div class="filter-group">
          <label for="filtro-puesto" class="filter-label">Por Puesto</label>
          <input type="text" id="filtro-puesto" class="filter-input" placeholder="Ej: cajero, chofer, supervisor">
        </div>
        <div class="filter-group">
          <label for="filtro-orden" class="filter-label">Por Fecha</label>
          <select id="filtro-orden" class="filter-input">
            <option value="proximas">Próximas a vencer</option>
            <option value="recientes">Más recientes</option>
          </select>
        </div>
      </section>

      <?php if ($error_message !== ''): ?>
        <div class="vacantes-public-error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (empty($vacantes)): ?>
        <div class="vacantes-public-empty">Por el momento no hay vacantes activas.</div>
      <?php else: ?>
        <div class="vacantes-public-grid" id="vacantes-grid">
          <?php
          $today = new DateTime('today');
          foreach ($vacantes as $vacante):
              $imagenNombre = basename((string)($vacante['imagen_flyer'] ?? ''));
              $rutaDisco = dirname(__DIR__) . '/assets/img/vacantes/' . $imagenNombre;
              if ($imagenNombre !== '' && file_exists($rutaDisco)) {
                  $rutaImagen = '../assets/img/vacantes/' . $imagenNombre;
              } else {
                  $rutaImagen = '../assets/img/vacantes/no-image.webp';
              }

              $esUltimosDias = false;
              $fechaFinTexto = (string)($vacante['fecha_fin'] ?? '');
              $vacanteShareUrl = absoluteUrl('frontend/vacantes-public.php?vacante=' . (int)$vacante['id']);
              $vacanteShareMsg = 'Mira esta vacante de ALISER: ' . (string)$vacante['titulo'] . ' en sucursal ' . (string)($vacante['sucursal'] ?? 'Matriz') . '. ' . $vacanteShareUrl;
              $waRecruitMsg = '¡Hola! Me interesa la vacante de ' . (string)$vacante['titulo'] . ' en la sucursal ' . (string)($vacante['sucursal'] ?? 'Matriz') . ' que vi en su sitio web. ¿Me podrían dar más información?';
              if ($fechaFinTexto !== '') {
                  $fechaFinObj = DateTime::createFromFormat('Y-m-d', $fechaFinTexto);
                  if ($fechaFinObj instanceof DateTime) {
                      $dias = (int)$today->diff($fechaFinObj)->format('%r%a');
                      $esUltimosDias = ($dias >= 0 && $dias <= 3);
                  }
              }
          ?>
            <article class="vacante-public-card"
              data-id="<?php echo (int)$vacante['id']; ?>"
              data-titulo="<?php echo htmlspecialchars(strtolower((string)$vacante['titulo'])); ?>"
              data-sucursal="<?php echo htmlspecialchars((string)($vacante['sucursal'] ?? 'Matriz')); ?>"
              data-fecha-fin="<?php echo htmlspecialchars((string)($vacante['fecha_fin'] ?? '')); ?>"
              data-creado-en="<?php echo htmlspecialchars((string)$vacante['creado_en']); ?>">
              <img
                src="<?php echo htmlspecialchars($rutaImagen); ?>"
                alt="<?php echo htmlspecialchars($vacante['titulo']); ?>"
                class="vacante-public-image js-flyer-open"
                loading="lazy"
                data-full="<?php echo htmlspecialchars($rutaImagen); ?>"
                data-title="<?php echo htmlspecialchars($vacante['titulo']); ?>"
              >
              <div class="vacante-public-content">
                <h2 class="vacante-public-title-text"><?php echo htmlspecialchars($vacante['titulo']); ?></h2>
                <div class="vacante-public-badges">
                  <span class="vacante-badge vacante-badge-sucursal"><?php echo htmlspecialchars($vacante['sucursal'] ?? 'Matriz'); ?></span>
                  <?php if ($esUltimosDias): ?>
                    <span class="vacante-badge vacante-badge-urgente">ULTIMOS DIAS</span>
                  <?php endif; ?>
                </div>
                <p class="vacante-public-desc"><?php echo htmlspecialchars(truncateText((string)$vacante['descripcion'], 150)); ?></p>
                <p class="vacante-public-fecha">
                  <?php if (!empty($vacante['fecha_fin'])): ?>
                    Disponible hasta el: <?php echo htmlspecialchars(date('d/m/Y', strtotime((string)$vacante['fecha_fin']))); ?>
                  <?php else: ?>
                    Vacante Permanente
                  <?php endif; ?>
                </p>
                <a class="vacante-whatsapp-cta" href="https://wa.me/521234567890?text=<?php echo rawurlencode($waRecruitMsg); ?>" target="_blank" rel="noopener noreferrer">
                  <i class="fab fa-whatsapp" aria-hidden="true"></i>
                  <span>Postularme por WhatsApp</span>
                </a>
                <div class="share-block">
                  <p class="share-title">Compartir esta vacante</p>
                  <div class="share-actions">
                    <a class="share-btn share-facebook" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode($vacanteShareUrl); ?>">
                      <i class="fab fa-facebook-f" aria-hidden="true"></i>
                      <span>Facebook</span>
                    </a>
                    <a class="share-btn share-whatsapp" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text=<?php echo rawurlencode($vacanteShareMsg); ?>">
                      <i class="fab fa-whatsapp" aria-hidden="true"></i>
                      <span>WhatsApp</span>
                    </a>
                    <button type="button" class="share-btn share-copy js-copy-link" data-share-url="<?php echo htmlspecialchars($vacanteShareUrl); ?>">
                      <i class="fas fa-link" aria-hidden="true"></i>
                      <span>Copiar enlace</span>
                    </button>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer class="main-footer" role="contentinfo">
    <div class="footer-container">
      <div class="footer-column footer-brand">
        <div class="footer-logo">
          <img src="assets/img/logo/logo.png" alt="ALISER" class="footer-logo-img">
        </div>
        <h3 class="footer-brand-title">Nuestra Mision</h3>
        <p class="footer-brand-text">Proveer productos de calidad superior con el mejor servicio al cliente en Baja California Sur.</p>
      </div>

      <div class="footer-column footer-links">
        <h3 class="footer-column-title">Explorar</h3>
        <ul class="footer-link-list">
          <li><a href="index.html#hero" class="footer-link">Inicio</a></li>
          <li><a href="vacantes-public.php" class="footer-link">Bolsa de Trabajo</a></li>
          <li><a href="index.html#terrenos-section" class="footer-link">Adquisicion de Terrenos</a></li>
          <li><a href="index.html#promociones" class="footer-link">Promociones</a></li>
        </ul>
      </div>

      <div class="footer-column footer-contact glass-effect">
        <h3 class="footer-column-title">Contacto</h3>
        <div class="footer-contact-item">
          <span class="contact-icon">Tel</span>
          <div>
            <p class="contact-label">Telefono</p>
            <a href="tel:+521234567890" class="contact-link">+52 (612) 123-4567</a>
          </div>
        </div>
        <div class="footer-contact-item">
          <span class="contact-icon">Mail</span>
          <div>
            <p class="contact-label">Email</p>
            <a href="mailto:info@aliser.mx" class="contact-link">info@aliser.mx</a>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-container">
        <p class="footer-copyright">&copy; 2026 ALISER. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>

  <div class="flyer-lightbox" id="flyerLightbox" aria-hidden="true">
    <div class="flyer-lightbox-content">
      <button class="flyer-close-btn" id="flyerCloseBtn" aria-label="Cerrar visor">×</button>
      <img src="" alt="" id="flyerLightboxImage" class="flyer-lightbox-image">
      <div class="flyer-lightbox-actions">
        <a id="flyerDownloadBtn" class="flyer-action-btn" href="#" download target="_blank" rel="noopener noreferrer">Descargar Flyer</a>
      </div>
      <div class="flyer-share-panel">
        <p class="share-title">Compartir esta vacante</p>
        <div class="share-actions">
          <a id="flyerShareFacebook" class="share-btn share-facebook" href="#" target="_blank" rel="noopener noreferrer">
            <i class="fab fa-facebook-f" aria-hidden="true"></i>
            <span>Facebook</span>
          </a>
          <a id="flyerShareWhatsApp" class="share-btn share-whatsapp" href="#" target="_blank" rel="noopener noreferrer">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
            <span>WhatsApp</span>
          </a>
          <button type="button" id="flyerShareCopy" class="share-btn share-copy js-copy-link" data-share-url="">
            <i class="fas fa-link" aria-hidden="true"></i>
            <span>Copiar enlace</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const filtroSucursal = document.getElementById('filtro-sucursal');
      const filtroPuesto = document.getElementById('filtro-puesto');
      const filtroOrden = document.getElementById('filtro-orden');
      const grid = document.getElementById('vacantes-grid');
      const allCards = Array.from(grid.querySelectorAll('.vacante-public-card'));

      const emptyState = document.createElement('div');
      emptyState.className = 'vacantes-public-empty';
      emptyState.textContent = 'No hay vacantes que coincidan con los filtros aplicados.';

      function applyFilters() {
        const sucursal = filtroSucursal.value.trim();
        const puesto = filtroPuesto.value.trim().toLowerCase();
        const orden = filtroOrden.value;

        let visibles = allCards.filter((card) => {
          const cardSucursal = (card.dataset.sucursal || '').trim();
          const cardTitulo = (card.dataset.titulo || '').toLowerCase();
          const okSucursal = !sucursal || cardSucursal === sucursal;
          const okPuesto = !puesto || cardTitulo.includes(puesto);
          return okSucursal && okPuesto;
        });

        visibles.sort((a, b) => {
          if (orden === 'recientes') {
            const aCreado = new Date(a.dataset.creadoEn || 0).getTime();
            const bCreado = new Date(b.dataset.creadoEn || 0).getTime();
            return bCreado - aCreado;
          }

          const aFinRaw = a.dataset.fechaFin || '';
          const bFinRaw = b.dataset.fechaFin || '';
          const aNull = aFinRaw === '';
          const bNull = bFinRaw === '';

          if (aNull !== bNull) {
            return aNull ? 1 : -1;
          }

          if (!aNull && !bNull) {
            const aFin = new Date(aFinRaw).getTime();
            const bFin = new Date(bFinRaw).getTime();
            if (aFin !== bFin) {
              return aFin - bFin;
            }
          }

          const aCreado = new Date(a.dataset.creadoEn || 0).getTime();
          const bCreado = new Date(b.dataset.creadoEn || 0).getTime();
          return bCreado - aCreado;
        });

        allCards.forEach((card) => {
          card.style.display = 'none';
        });

        visibles.forEach((card) => {
          card.style.display = '';
          grid.appendChild(card);
        });

        if (visibles.length === 0) {
          grid.appendChild(emptyState);
        } else if (emptyState.parentNode === grid) {
          grid.removeChild(emptyState);
        }
      }

      filtroSucursal.addEventListener('change', applyFilters);
      filtroPuesto.addEventListener('input', applyFilters);
      filtroOrden.addEventListener('change', applyFilters);
      applyFilters();

      const lightbox = document.getElementById('flyerLightbox');
      const lightboxImage = document.getElementById('flyerLightboxImage');
      const downloadBtn = document.getElementById('flyerDownloadBtn');
      const closeBtn = document.getElementById('flyerCloseBtn');
      const openers = document.querySelectorAll('.js-flyer-open');
      const flyerShareFacebook = document.getElementById('flyerShareFacebook');
      const flyerShareWhatsApp = document.getElementById('flyerShareWhatsApp');
      const flyerShareCopy = document.getElementById('flyerShareCopy');

      function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
      }

      function copyToClipboard(text, btn) {
        if (!text) return;
        const onDone = () => {
          if (!btn) return;
          btn.classList.add('copied');
          const span = btn.querySelector('span');
          const old = span ? span.textContent : '';
          if (span) span.textContent = 'Copiado';
          setTimeout(() => {
            btn.classList.remove('copied');
            if (span) span.textContent = old || 'Copiar enlace';
          }, 1200);
        };

        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(text).then(onDone).catch(() => {});
          return;
        }

        const area = document.createElement('textarea');
        area.value = text;
        document.body.appendChild(area);
        area.select();
        try {
          document.execCommand('copy');
          onDone();
        } catch (e) {}
        document.body.removeChild(area);
      }

      document.querySelectorAll('.js-copy-link').forEach((btn) => {
        btn.addEventListener('click', () => {
          copyToClipboard(btn.dataset.shareUrl || '', btn);
        });
      });

      openers.forEach((opener) => {
        opener.addEventListener('click', () => {
          const src = opener.getAttribute('data-full') || opener.getAttribute('src');
          const title = opener.getAttribute('data-title') || 'Flyer Vacante';
          const card = opener.closest('.vacante-public-card');
          const id = card ? card.getAttribute('data-id') : '';
          const sucursal = card ? (card.dataset.sucursal || 'Matriz') : 'Matriz';
          const shareUrl = id ? (window.location.origin + window.location.pathname + '?vacante=' + id) : window.location.href;
          const waMsg = 'Mira esta vacante de ALISER: ' + title + ' en sucursal ' + sucursal + '. ' + shareUrl;

          lightboxImage.src = src;
          lightboxImage.alt = title;
          downloadBtn.href = src;
          downloadBtn.setAttribute('download', title.replace(/\s+/g, '_') + '.webp');
          flyerShareFacebook.href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl);
          flyerShareWhatsApp.href = 'https://wa.me/?text=' + encodeURIComponent(waMsg);
          flyerShareCopy.dataset.shareUrl = shareUrl;
          lightbox.classList.add('is-open');
          lightbox.setAttribute('aria-hidden', 'false');
        });
      });

      closeBtn.addEventListener('click', closeLightbox);
      lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
      });
    })();
  </script>
</body>
</html>
