<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

$autoPrint = isset($_GET['print']) && $_GET['print'] === '1';
require_once __DIR__ . '/includes/db.php';

function buildOrigin(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function projectBasePath(): string
{
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $base = dirname(dirname($script));
    if ($base === '\\' || $base === '.' || $base === '/') {
        return '';
    }
    return $base;
}

function projectUrl(string $path): string
{
    $base = rtrim(buildOrigin() . projectBasePath(), '/');
    return $base . '/' . ltrim($path, '/');
}

$panelUrl = projectUrl('admin/index.php');

$rolesInfo = [
    'MASTER' => [
        'icon' => 'fa-shield-halved',
        'title' => 'MASTER',
        'desc' => 'Control total del panel. Puedes asignar roles, auditar accesos y coordinar a todo el equipo.',
        'powers' => 'Capacidad de gobernar el sistema completo y mantener la operacion segura.',
        'files' => ['dashboard.php', 'usuarios.php', 'guia_bienvenida.php', 'vacantes.php', 'nueva_vacante.php', 'terrenos.php', 'promociones.php']
    ],
    'TALENTO' => [
        'icon' => 'fa-people-group',
        'title' => 'TALENTO',
        'desc' => 'Gestion de vacantes y bolsa de trabajo.',
        'powers' => 'Capacidad de dar voz a nuevas vacantes y filtrar el mejor talento sudcaliforniano.',
        'files' => ['dashboard.php', 'vacantes.php', 'nueva_vacante.php', 'save_vacante.php']
    ],
    'BIENES' => [
        'icon' => 'fa-map-location-dot',
        'title' => 'BIENES',
        'desc' => 'Gestion de terrenos y ofertas recibidas desde el portal.',
        'powers' => 'Capacidad de evaluar ubicaciones estrategicas para la expansion.',
        'files' => ['dashboard.php', 'terrenos.php', 'nuevo_terreno.php']
    ],
    'PROMO' => [
        'icon' => 'fa-tag',
        'title' => 'PROMO',
        'desc' => 'Gestion de promociones y cupones (publicacion y vigencia).',
        'powers' => 'Capacidad de lanzar campañas con urgencia y claridad para el cliente.',
        'files' => ['dashboard.php', 'promociones.php', 'nueva_promocion.php', 'save_promocion.php']
    ],
    'MARCA' => [
        'icon' => 'fa-bullhorn',
        'title' => 'MARCA',
        'desc' => 'Apoyo a promociones y comunicacion de marca.',
        'powers' => 'Capacidad de cuidar la identidad y amplificar mensajes clave.',
        'files' => ['dashboard.php', 'promociones.php', 'nueva_promocion.php', 'save_promocion.php']
    ],
    'SOPORTE' => [
        'icon' => 'fa-headset',
        'title' => 'SOPORTE',
        'desc' => 'Soporte operativo con acceso restringido.',
        'powers' => 'Capacidad de apoyar incidencias sin exponer modulos sensibles.',
        'files' => ['dashboard.php']
    ]
];

$users = [];
$selectedUserId = isset($_GET['user']) && is_numeric($_GET['user']) ? (int)$_GET['user'] : 0;
$selectedUser = null;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, nombre_completo, usuario, email, rol FROM usuarios_admin ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO en guia_bienvenida.php: ' . $e->getMessage());
}

foreach ($users as $u) {
    if ((int)$u['id'] === $selectedUserId) {
        $selectedUser = $u;
        break;
    }
}

function rolesFromCsv(string $csv): array
{
    $parts = preg_split('/\s*,\s*/', $csv, -1, PREG_SPLIT_NO_EMPTY);
    $out = [];
    foreach ($parts as $p) {
        $out[] = strtoupper(trim((string)$p));
    }
    return array_values(array_unique(array_filter($out)));
}

$selectedName = $selectedUser ? (string)$selectedUser['nombre_completo'] : '';
$selectedRoles = $selectedUser ? rolesFromCsv((string)$selectedUser['rol']) : [];

$textoWhatsAppBase = "Bienvenida ALISER\n"
    . "Acceso al panel: " . $panelUrl . "\n\n"
    . "Roles:\n";

$textoWhatsApp = $textoWhatsAppBase;
foreach ($rolesInfo as $key => $info) {
    $textoWhatsApp .= "- " . $key . ": " . $info['desc'] . "\n";
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>ALISER Admin | Guia de Bienvenida</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="css/admin-style.css">
  <style>
    .infografia-shell {
      background:
        radial-gradient(circle at 10% 10%, rgba(236, 212, 168, 0.24), transparent 45%),
        radial-gradient(circle at 90% 20%, rgba(67, 145, 132, 0.22), transparent 50%),
        radial-gradient(circle at 20% 90%, rgba(37, 103, 55, 0.16), transparent 55%);
      border-radius: 18px;
      border: 1px solid rgba(255, 255, 255, 0.4);
      box-shadow: 0 18px 46px rgba(0, 0, 0, 0.14);
      padding: 1.25rem;
    }

    .selector-glass {
      background: rgba(255, 255, 255, 0.72);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(37, 103, 55, 0.12);
      border-radius: 16px;
      padding: 1rem;
      box-shadow: 0 12px 28px rgba(24, 67, 37, 0.12);
      display: grid;
      gap: 0.75rem;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      align-items: end;
      margin-bottom: 1rem;
    }

    .selector-meta {
      font-size: 0.9rem;
      color: #2f4e43;
      line-height: 1.45;
      margin: 0;
    }

    .roles-pillrow {
      display: flex;
      flex-wrap: wrap;
      gap: 0.45rem;
      align-items: center;
      min-height: 34px;
    }

    .infografia-grid {
      display: grid;
      gap: 0.95rem;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      margin-top: 0.8rem;
    }

    .role-card {
      position: relative;
      background: rgba(255, 255, 255, 0.72);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.42);
      border-radius: 18px;
      padding: 1rem;
      box-shadow: 0 16px 34px rgba(0, 0, 0, 0.14);
      overflow: hidden;
      transform: translateY(16px);
      opacity: 0;
      animation: fadeInUp 0.55s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .role-card::before {
      content: "";
      position: absolute;
      inset: -2px;
      background: radial-gradient(circle at top left, rgba(255,255,255,0.8), transparent 40%);
      opacity: 0.35;
      pointer-events: none;
    }

    .role-card.is-selected {
      border-color: rgba(232, 191, 108, 0.85);
      box-shadow: 0 20px 46px rgba(232, 191, 108, 0.24), 0 0 0 2px rgba(232, 191, 108, 0.28) inset;
    }

    .role-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      margin-bottom: 0.55rem;
      position: relative;
      z-index: 1;
    }

    .role-icon {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(37, 103, 55, 0.12);
      background: rgba(255, 255, 255, 0.82);
      box-shadow: 0 10px 20px rgba(0,0,0,0.08);
      color: #204135;
      flex: 0 0 auto;
    }

    .role-titleline {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin: 0;
      flex: 1;
    }

    .role-desc {
      margin: 0.35rem 0 0;
      color: #2f4e43;
      line-height: 1.5;
      position: relative;
      z-index: 1;
    }

    .role-powers {
      margin: 0.65rem 0 0;
      padding: 0.65rem 0.75rem;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.72);
      border: 1px solid rgba(37, 103, 55, 0.12);
      color: #3a4f44;
      position: relative;
      z-index: 1;
    }

    .files-list {
      margin: 0.75rem 0 0;
      padding-left: 1.1rem;
      color: #3a4f44;
      line-height: 1.45;
      position: relative;
      z-index: 1;
    }

    .files-list li { margin: 0.15rem 0; }

    .guia-actions {
      display:flex;
      gap:0.75rem;
      flex-wrap:wrap;
      margin-top: 1rem;
    }

    .guia-textarea { width:100%; min-height: 180px; }

    @keyframes fadeInUp {
      to { transform: translateY(0); opacity: 1; }
    }

    @media print {
      .header-actions, .guia-actions, .btn-secondary, .login-btn { display:none !important; }
      body { background: #fff !important; }
    }
  </style>
</head>
<body class="admin-body-secondary">
  <div class="admin-wrapper">
    <header class="admin-header-main">
      <div class="header-content">
        <h1 class="admin-title">Guia de Bienvenida</h1>
        <p class="admin-subtitle">Resumen rapido de roles y permisos (copiable para WhatsApp)</p>
      </div>
      <div class="header-actions">
        <a href="usuarios.php" class="btn-secondary">Volver a Usuarios</a>
        <a href="guia_bienvenida.php?print=1" class="login-btn">
          <span class="btn-text">Imprimir / PDF</span>
          <span class="btn-glow"></span>
        </a>
      </div>
    </header>

    <main class="admin-content-card">
      <div class="infografia-shell">
        <section class="selector-glass" aria-label="Generador de bienvenida">
          <div class="form-group" style="margin:0;">
            <label for="userSelect" class="form-label">Selecciona un usuario</label>
            <select id="userSelect" class="form-input">
              <option value="0">Sin seleccionar</option>
              <?php foreach ($users as $u): ?>
                <option
                  value="<?php echo (int)$u['id']; ?>"
                  data-name="<?php echo htmlspecialchars((string)$u['nombre_completo']); ?>"
                  data-roles="<?php echo htmlspecialchars((string)$u['rol']); ?>"
                  <?php echo ((int)$u['id'] === $selectedUserId) ? 'selected' : ''; ?>
                >
                  <?php echo htmlspecialchars((string)$u['nombre_completo'] . ' (' . (string)$u['usuario'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="selector-meta">
              Al seleccionar un usuario, la infografia resalta sus roles y el texto para WhatsApp se personaliza automaticamente.
            </p>
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Roles detectados</label>
            <div class="roles-pillrow" id="rolesPills">
              <?php if (!empty($selectedRoles)): ?>
                <?php foreach ($selectedRoles as $r): ?>
                  <?php $css = 'role-' . strtolower($r); ?>
                  <span class="status-badge role-badge <?php echo htmlspecialchars($css); ?>"><?php echo htmlspecialchars($r); ?></span>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="text-muted">Selecciona un usuario para ver sus roles.</span>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <h2 class="modules-title">Infografia de Roles (interactiva)</h2>
        <section class="infografia-grid" id="rolesGrid">
          <?php $i = 0; foreach ($rolesInfo as $key => $info): $i++; ?>
            <?php $badgeCss = 'role-' . strtolower($key); ?>
            <article class="role-card <?php echo in_array($key, $selectedRoles, true) ? 'is-selected' : ''; ?>" data-role="<?php echo htmlspecialchars($key); ?>" style="animation-delay: <?php echo (string)($i * 0.06); ?>s;">
              <div class="role-head">
                <div class="role-icon" aria-hidden="true">
                  <i class="fa-solid <?php echo htmlspecialchars($info['icon']); ?>"></i>
                </div>
                <h3 class="role-titleline">
                  <span class="status-badge role-badge <?php echo htmlspecialchars($badgeCss); ?>"><?php echo htmlspecialchars($info['title']); ?></span>
                </h3>
              </div>
              <p class="role-desc"><?php echo htmlspecialchars($info['desc']); ?></p>
              <div class="role-powers">
                <strong>Tus Poderes:</strong> <?php echo htmlspecialchars($info['powers']); ?>
              </div>
              <ul class="files-list">
                <?php foreach ($info['files'] as $f): ?>
                  <li><code><?php echo htmlspecialchars($f); ?></code></li>
                <?php endforeach; ?>
              </ul>
            </article>
          <?php endforeach; ?>
        </section>

        <h2 class="modules-title" style="margin-top:1.2rem;">Copiar para WhatsApp</h2>
        <textarea id="texto-wa" class="form-textarea guia-textarea" readonly><?php echo htmlspecialchars($textoWhatsApp); ?></textarea>
        <div class="guia-actions">
          <button type="button" id="btn-copy" class="login-btn shimmer">
            <span class="btn-text">Copiar Texto</span>
            <span class="btn-glow"></span>
          </button>
          <button type="button" id="btn-print" class="login-btn">
            <span class="btn-text">Imprimir / PDF</span>
            <span class="btn-glow"></span>
          </button>
        </div>
      </div>
    </main>
  </div>

  <script>
    (function () {
      const btnCopy = document.getElementById('btn-copy');
      const btnPrint = document.getElementById('btn-print');
      const area = document.getElementById('texto-wa');
      const userSelect = document.getElementById('userSelect');
      const rolesGrid = document.getElementById('rolesGrid');
      const rolesPills = document.getElementById('rolesPills');

      const PANEL_URL = <?php echo json_encode($panelUrl, JSON_UNESCAPED_UNICODE); ?>;
      const ROLES_INFO = <?php echo json_encode($rolesInfo, JSON_UNESCAPED_UNICODE); ?>;

      function parseRolesCsv(csv) {
        return String(csv || '')
          .split(',')
          .map(s => s.trim().toUpperCase())
          .filter(Boolean)
          .filter((v, i, a) => a.indexOf(v) === i);
      }

      function renderPills(roles) {
        if (!rolesPills) return;
        rolesPills.innerHTML = '';
        if (!roles || roles.length === 0) {
          const span = document.createElement('span');
          span.className = 'text-muted';
          span.textContent = 'Selecciona un usuario para ver sus roles.';
          rolesPills.appendChild(span);
          return;
        }
        roles.forEach((r) => {
          const badge = document.createElement('span');
          badge.className = 'status-badge role-badge role-' + String(r).toLowerCase();
          badge.textContent = r;
          rolesPills.appendChild(badge);
        });
      }

      function highlightRoles(roles) {
        if (!rolesGrid) return;
        rolesGrid.querySelectorAll('.role-card').forEach((card) => {
          const role = card.getAttribute('data-role') || '';
          if (roles.includes(role)) card.classList.add('is-selected');
          else card.classList.remove('is-selected');
        });
      }

      function buildWelcomeText(nombre, roles) {
        const rolesLine = roles.length ? roles.join(', ') : 'SOPORTE';
        let out = 'Bienvenida ALISER\\n';
        if (nombre) out += 'Hola ' + nombre + ',\\n';
        out += 'Tus roles: ' + rolesLine + '\\n';
        out += 'Acceso al panel: ' + PANEL_URL + '\\n\\n';
        out += 'Resumen de roles:\\n';
        roles.forEach((r) => {
          const info = ROLES_INFO[r];
          if (!info) return;
          out += '- ' + r + ': ' + info.desc + '\\n';
        });
        out += '\\nSi necesitas acceso a otro modulo, solicita al MASTER el rol correspondiente.';
        return out;
      }

      function copyText() {
        if (!area) return;
        const text = area.value || '';
        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(text).catch(() => {});
          return;
        }
        area.select();
        area.setSelectionRange(0, 999999);
        try { document.execCommand('copy'); } catch (e) {}
      }

      if (btnCopy) btnCopy.addEventListener('click', copyText);
      if (btnPrint) btnPrint.addEventListener('click', () => window.print());
      if (userSelect && area) {
        userSelect.addEventListener('change', () => {
          const opt = userSelect.options[userSelect.selectedIndex];
          const nombre = opt ? (opt.getAttribute('data-name') || '') : '';
          const rolesCsv = opt ? (opt.getAttribute('data-roles') || '') : '';
          const roles = parseRolesCsv(rolesCsv);
          renderPills(roles);
          highlightRoles(roles);
          area.value = buildWelcomeText(nombre, roles);
        });
        // Initial sync from server selection
        const opt = userSelect.options[userSelect.selectedIndex];
        const nombre = opt ? (opt.getAttribute('data-name') || '') : '';
        const rolesCsv = opt ? (opt.getAttribute('data-roles') || '') : '';
        const roles = parseRolesCsv(rolesCsv);
        if (roles.length) {
          area.value = buildWelcomeText(nombre, roles);
        }
      }

      <?php if ($autoPrint): ?>
      window.addEventListener('load', () => window.print());
      <?php endif; ?>
    })();
  </script>
</body>
</html>
