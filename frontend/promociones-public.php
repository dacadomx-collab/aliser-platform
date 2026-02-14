<?php
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/../admin/includes/db.php';

$promociones = [];
$error_message = '';
try {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT id, tipo_publico, titulo, descripcion, imagen_flyer, fecha_inicio, fecha_fin, estatus, creado_en
            FROM promociones
            WHERE estatus = 'activa'
              AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
            ORDER BY fecha_fin ASC, creado_en DESC";
    $stmt = $db->query($sql);
    $promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO en promociones-public.php: ' . $e->getMessage());
    $error_message = 'No fue posible cargar las promociones en este momento.';
}

function truncatePromo(string $text, int $max = 150): string
{
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '...' : $text;
    }
    return strlen($text) > $max ? substr($text, 0, $max) . '...' : $text;
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Promociones activas ALISER para hogar y mayoreo en Baja California Sur.">
  <meta name="robots" content="index, follow">
  <title>Promociones ALISER | Menudeo y Mayoreo</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="src/css/main.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .promo-public-main {
      padding: 2rem 1rem 3rem;
      min-height: 72vh;
      background: radial-gradient(circle at top right, rgba(236, 212, 168, 0.18), transparent 52%),
                  radial-gradient(circle at bottom left, rgba(67, 145, 132, 0.22), transparent 50%);
    }
    .promo-public-wrap { max-width: 1200px; margin: 0 auto; }
    .promo-header { text-align: center; margin-bottom: 1.2rem; }
    .promo-title { color: #256737; margin: 0; font-size: clamp(1.8rem, 2.6vw, 2.45rem); }
    .promo-subtitle { color: #34534a; margin-top: 0.4rem; }
    .promo-identidad {
      margin: 0.9rem auto 0;
      max-width: 920px;
      color: #2f4e43;
      font-style: italic;
      font-weight: 600;
      line-height: 1.5;
    }
    .promo-filters {
      margin: 1.4rem 0 1.8rem;
      padding: 1rem;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.62);
      border: 1px solid rgba(255, 255, 255, 0.62);
      box-shadow: 0 8px 20px rgba(24, 67, 37, 0.12);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
      justify-content: center;
    }
    .promo-filter-btn {
      border: 1px solid rgba(37, 103, 55, 0.25);
      background: rgba(255, 255, 255, 0.8);
      color: #204135;
      border-radius: 10px;
      min-height: 42px;
      padding: 0.6rem 1rem;
      font-size: 0.9rem;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .promo-filter-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 16px rgba(24, 67, 37, 0.14);
    }
    .promo-filter-btn.is-active {
      color: #fff;
      border-color: rgba(37, 103, 55, 0.25);
      background: linear-gradient(135deg, #256737, #439184);
    }
    .promo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(285px, 1fr)); gap: 1rem; }
    .promo-card {
      background: rgba(255, 255, 255, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.55);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 12px 30px rgba(24, 67, 37, 0.15);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      display: flex;
      flex-direction: column;
    }
    .promo-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-bottom: 1px solid rgba(37, 103, 55, 0.12);
      cursor: zoom-in;
    }
    .promo-content { padding: 1rem; display: flex; flex-direction: column; gap: 0.55rem; height: 100%; }
    .promo-card-title {
      color: #256737;
      margin: 0;
      font-size: 1.1rem;
      background: linear-gradient(90deg, #256737, #439184, #256737);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: promoShimmer 2.6s linear infinite;
    }
    .promo-badge {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: 0.22rem 0.68rem;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.2px;
    }
    .promo-badge-menudeo { background: rgba(232, 191, 108, 0.22); border: 1px solid rgba(201, 142, 27, 0.35); color: #694503; }
    .promo-badge-mayoreo { background: rgba(67, 145, 132, 0.16); border: 1px solid rgba(67, 145, 132, 0.32); color: #1f5b53; }
    .promo-desc { margin: 0; color: #3a4f44; font-size: 0.93rem; line-height: 1.45; }
    .promo-fechas { margin: 0; color: #2d6252; font-size: 0.84rem; font-weight: 600; }
    .promo-countdown {
      margin: 0;
      color: #8a2c2c;
      background: rgba(255, 232, 232, 0.9);
      border: 1px solid rgba(138, 44, 44, 0.25);
      border-radius: 8px;
      padding: 0.38rem 0.5rem;
      font-size: 0.8rem;
      font-weight: 700;
      display: none;
    }
    .promo-actions { margin-top: auto; padding-top: 0.2rem; }
    .promo-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      width: 100%;
      min-height: 42px;
      border-radius: 10px;
      font-weight: 800;
      color: #fff;
      background: linear-gradient(135deg, #256737, #439184);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .promo-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 18px rgba(37, 103, 55, 0.28); }
    .promo-state {
      background: rgba(255, 255, 255, 0.85);
      border: 1px solid rgba(37, 103, 55, 0.12);
      border-radius: 12px;
      padding: 1rem;
      text-align: center;
      color: #4a4a4a;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: 0 10px 22px rgba(24, 67, 37, 0.14);
    }
    .promo-state-error { color: #8a2c2c; border-color: rgba(138, 44, 44, 0.25); }
    .promo-lightbox {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.9);
      z-index: 9999;
      padding: 1rem;
    }
    .promo-lightbox.is-open { display: flex; }
    .promo-lightbox-content {
      width: min(1100px, 100%);
      max-height: 95vh;
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
    }
    .promo-lightbox-image {
      width: 100%;
      max-height: 82vh;
      object-fit: contain;
      border-radius: 12px;
      background: rgba(0, 0, 0, 0.35);
    }
    .promo-lightbox-actions {
      display: flex;
      justify-content: center;
    }
    .promo-lightbox-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 42px;
      text-decoration: none;
      border-radius: 10px;
      padding: 0.6rem 1rem;
      font-weight: 800;
      color: #fff;
      background: linear-gradient(135deg, #256737, #439184);
    }
    .promo-lightbox-close {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 40px;
      height: 40px;
      border: 0;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.22);
      color: #fff;
      cursor: pointer;
      font-size: 1.5rem;
      line-height: 1;
    }
    @keyframes promoShimmer {
      0% { background-position: 0 0; }
      100% { background-position: 200% 0; }
    }
    @media (max-width: 780px) {
      .promo-filters { display: grid; grid-template-columns: 1fr; }
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
        <li class="nav-item nav-item-home">
          <a href="index.html#hero" class="nav-link nav-link-home"><i class="fas fa-home" aria-hidden="true"></i><span>Inicio</span></a>
        </li>
        <li class="nav-item"><a href="promociones-public.php" class="nav-link">Promociones</a></li>
        <li class="nav-item"><a href="index.html#terrenos-section" class="nav-link">Terrenos</a></li>
        <li class="nav-item nav-item-right"><a href="vacantes-public.php" class="nav-link">Trabaja con Nosotros</a></li>
      </ul>
    </div>
  </nav>

  <main class="promo-public-main">
    <div class="promo-public-wrap">
      <div class="promo-header">
        <h1 class="promo-title">Promociones y Cupones ALISER</h1>
        <p class="promo-subtitle">Beneficios activos para tu hogar y para tu negocio.</p>
        <p class="promo-identidad">En ALISER, premiamos tu preferencia. Somos la empresa 100% sudcaliforniana que apoya tu economia. ¡Sumate a nuestra comunidad y crece con nosotros!</p>
      </div>

      <section class="promo-filters" aria-label="Filtros de promociones">
        <button type="button" class="promo-filter-btn is-active" data-tipo="menudeo">Para mi Hogar</button>
        <button type="button" class="promo-filter-btn" data-tipo="mayoreo">Negocios</button>
      </section>

      <?php if (!empty($error_message)): ?>
        <div class="promo-state promo-state-error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (empty($promociones)): ?>
        <div class="promo-state">No hay promociones activas.</div>
      <?php else: ?>
        <section id="promo-grid" class="promo-grid">
          <?php foreach ($promociones as $promo): ?>
            <?php
              $flyerName = trim((string)($promo['imagen_flyer'] ?? ''));
              $diskFlyerPath = dirname(__DIR__) . '/assets/img/promociones/' . $flyerName;
              $flyerSrc = ($flyerName !== '' && file_exists($diskFlyerPath))
                ? '../assets/img/promociones/' . rawurlencode($flyerName)
                : 'assets/img/logo/logo.png';

              $tipoPublico = (string)$promo['tipo_publico'];
              $tipoLabel = $tipoPublico === 'mayoreo' ? 'Mayoreo' : 'Menudeo';
              $tipoClass = $tipoPublico === 'mayoreo' ? 'promo-badge-mayoreo' : 'promo-badge-menudeo';
              $fechaFinRaw = (string)$promo['fecha_fin'];
              $fechaFinLabel = date('d/m/Y', strtotime($fechaFinRaw));
              $tituloSafe = htmlspecialchars((string)$promo['titulo']);
            ?>
            <article
              class="promo-card"
              data-tipo="<?php echo htmlspecialchars($tipoPublico); ?>"
              data-fecha-fin="<?php echo htmlspecialchars($fechaFinRaw); ?>"
            >
              <img
                src="<?php echo htmlspecialchars($flyerSrc); ?>"
                alt="<?php echo $tituloSafe; ?>"
                class="promo-image js-open-lightbox"
                loading="lazy"
                data-full="<?php echo htmlspecialchars($flyerSrc); ?>"
                data-title="<?php echo $tituloSafe; ?>"
                onerror="this.onerror=null;this.src='assets/img/logo/logo.png';"
              >
              <div class="promo-content">
                <span class="promo-badge <?php echo $tipoClass; ?>"><?php echo $tipoLabel; ?></span>
                <h2 class="promo-card-title"><?php echo $tituloSafe; ?></h2>
                <p class="promo-desc"><?php echo htmlspecialchars(truncatePromo((string)$promo['descripcion'], 150)); ?></p>
                <p class="promo-fechas">Disponible hasta el: <?php echo htmlspecialchars($fechaFinLabel); ?></p>
                <p class="promo-countdown js-countdown">¡Termina en: 00:00:00!</p>
                <div class="promo-actions">
                  <?php if ($tipoPublico === 'menudeo'): ?>
                    <a class="promo-btn" href="<?php echo htmlspecialchars($flyerSrc); ?>" target="_blank" rel="noopener noreferrer">Descargar Cupon</a>
                  <?php else: ?>
                    <?php
                      $waMsg = 'Hola, me interesa la promocion: ' . (string)$promo['titulo'];
                      $waUrl = 'https://wa.me/521234567890?text=' . rawurlencode($waMsg);
                    ?>
                    <a class="promo-btn" href="<?php echo htmlspecialchars($waUrl); ?>" target="_blank" rel="noopener noreferrer">Contactar Asesor</a>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </section>
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
          <li><a href="promociones-public.php" class="footer-link">Promociones</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-container">
        <p class="footer-copyright">&copy; 2026 ALISER. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>

  <div class="promo-lightbox" id="promoLightbox" aria-hidden="true">
    <button type="button" class="promo-lightbox-close" id="promoLightboxClose" aria-label="Cerrar visor">x</button>
    <div class="promo-lightbox-content">
      <img src="" alt="" id="promoLightboxImage" class="promo-lightbox-image">
      <div class="promo-lightbox-actions">
        <a href="#" id="promoLightboxDownload" class="promo-lightbox-btn" download target="_blank" rel="noopener noreferrer">Descargar Flyer</a>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const filterButtons = Array.from(document.querySelectorAll('.promo-filter-btn'));
      const cards = Array.from(document.querySelectorAll('.promo-card'));
      if (filterButtons.length === 0 || cards.length === 0) return;

      const DAY_MS = 24 * 60 * 60 * 1000;
      let activeType = 'menudeo';

      function endOfDayTimestamp(isoDate) {
        if (!isoDate) return NaN;
        const date = new Date(isoDate + 'T23:59:59');
        return date.getTime();
      }

      function twoDigits(value) {
        return value < 10 ? '0' + value : String(value);
      }

      function formatRemaining(ms) {
        const totalSeconds = Math.max(0, Math.floor(ms / 1000));
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return twoDigits(hours) + ':' + twoDigits(minutes) + ':' + twoDigits(seconds);
      }

      function applyTypeFilter() {
        cards.forEach((card) => {
          const tipo = card.dataset.tipo || '';
          card.style.display = tipo === activeType ? '' : 'none';
        });
      }

      function refreshCountdown() {
        const now = Date.now();
        cards.forEach((card) => {
          const countdown = card.querySelector('.js-countdown');
          if (!countdown) return;
          const endTs = endOfDayTimestamp(card.dataset.fechaFin || '');
          if (Number.isNaN(endTs)) {
            countdown.style.display = 'none';
            return;
          }
          const diff = endTs - now;
          if (diff > 0 && diff < DAY_MS) {
            countdown.style.display = 'block';
            countdown.textContent = '¡Termina en: ' + formatRemaining(diff) + '!';
          } else {
            countdown.style.display = 'none';
          }
        });
      }

      filterButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          activeType = btn.dataset.tipo || 'menudeo';
          filterButtons.forEach((item) => item.classList.remove('is-active'));
          btn.classList.add('is-active');
          applyTypeFilter();
        });
      });

      const lightbox = document.getElementById('promoLightbox');
      const lightboxImage = document.getElementById('promoLightboxImage');
      const lightboxDownload = document.getElementById('promoLightboxDownload');
      const lightboxClose = document.getElementById('promoLightboxClose');
      const openers = document.querySelectorAll('.js-open-lightbox');

      function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
      }

      openers.forEach((opener) => {
        opener.addEventListener('click', () => {
          const src = opener.getAttribute('data-full') || opener.getAttribute('src');
          const title = opener.getAttribute('data-title') || 'Flyer promocion';
          if (!src || !lightbox || !lightboxImage || !lightboxDownload) return;
          lightboxImage.src = src;
          lightboxImage.alt = title;
          lightboxDownload.href = src;
          lightboxDownload.setAttribute('download', title.replace(/\s+/g, '_') + '.webp');
          lightbox.classList.add('is-open');
          lightbox.setAttribute('aria-hidden', 'false');
        });
      });

      if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
      }

      if (lightbox) {
        lightbox.addEventListener('click', (e) => {
          if (e.target === lightbox) closeLightbox();
        });
      }

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
      });

      applyTypeFilter();
      refreshCountdown();
      setInterval(refreshCountdown, 1000);
    })();
  </script>
</body>
</html>
