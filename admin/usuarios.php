<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/includes/auth.php';
requireRole(['MASTER']);

if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/includes/db.php';

$db = Database::getInstance()->getConnection();
$error_message = '';
$success_message = '';
$warning_message = '';
$roles_validos = ['MASTER', 'TALENTO', 'BIENES', 'PROMO', 'MARCA', 'SOPORTE'];
$modo_edicion = false;
$usuario_edicion = null;
$adminIdSesion = (int)($_SESSION['admin_id'] ?? 0);
$debug_pdo_message = false; // Debug desactivado (usar mensajes amigables).

if (isset($_SESSION['flash_success'])) {
    $success_message = (string)$_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error_message = (string)$_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
if (isset($_SESSION['flash_warning'])) {
    $warning_message = (string)$_SESSION['flash_warning'];
    unset($_SESSION['flash_warning']);
}

function buildOrigin(): string
{
    $forwardedProto = (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
    $forwardedHost = (string)($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '');
    $httpsOn = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = ($forwardedProto !== '') ? $forwardedProto : ($httpsOn ? 'https' : 'http');

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($forwardedHost !== '') {
        $host = explode(',', $forwardedHost)[0];
    }
    if ($host === '') {
        $host = 'localhost';
    }

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

function sendWelcomeEmail(string $toEmail, string $nombre, string $rol): bool
{
    if ($toEmail === '') {
        return false;
    }

    $logoUrl = projectUrl('frontend/assets/img/logo/logo.png');
    $panelUrl = projectUrl('admin/index.php');

    $subject = 'Bienvenido a ALISER | Acceso al Panel';
    $body = '<!doctype html><html><head><meta charset="utf-8"></head><body style="margin:0;padding:0;background:#f4f7f5;font-family:Arial,sans-serif;">'
          . '<div style="max-width:620px;margin:0 auto;padding:18px;">'
          . '<div style="background:rgba(255,255,255,0.96);border:1px solid rgba(37,103,55,0.14);border-radius:14px;box-shadow:0 10px 26px rgba(24,67,37,0.12);padding:18px;">'
          . '<div style="text-align:center;margin-bottom:12px;">'
          . '<img src="' . htmlspecialchars($logoUrl) . '" alt="ALISER" style="width:120px;height:auto;display:inline-block;">'
          . '<h1 style="margin:10px 0 0;color:#256737;font-size:20px;">Bienvenido(a) al Panel ALISER</h1>'
          . '</div>'
          . '<p style="margin:0 0 10px;color:#2f4e43;font-size:14px;line-height:1.5;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>'
          . '<p style="margin:0 0 12px;color:#2f4e43;font-size:14px;line-height:1.5;">Tu usuario ha sido creado y se te asigno el rol: <strong>' . htmlspecialchars($rol) . '</strong>.</p>'
          . '<div style="text-align:center;margin:18px 0;">'
          . '<a href="' . htmlspecialchars($panelUrl) . '" style="display:inline-block;background:linear-gradient(135deg,#256737,#439184);color:#fff;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;">Entrar al Panel</a>'
          . '</div>'
          . '<p style="margin:0;color:#6b6b6b;font-size:12px;line-height:1.5;">Si no solicitaste este acceso, ignora este correo.</p>'
          . '</div>'
          . '<p style="text-align:center;color:#7b7b7b;font-size:11px;margin:10px 0 0;">© ' . date('Y') . ' ALISER</p>'
          . '</div>'
          . '</body></html>';

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'From: ALISER <no-reply@aliser.mx>';

    try {
        return mail($toEmail, $subject, $body, implode("\r\n", $headers));
    } catch (Throwable $e) {
        error_log('sendWelcomeEmail fallo: ' . $e->getMessage());
        return false;
    }
}

function isDuplicateKey(PDOException $e): bool
{
    // SQLSTATE 23000 / MySQL error 1062 = Duplicate entry
    if ((string)$e->getCode() === '23000') {
        return true;
    }
    if (isset($e->errorInfo[0]) && (string)$e->errorInfo[0] === '23000') {
        return true;
    }
    if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim((string)($_POST['accion'] ?? ''));

    try {
        if ($accion === 'crear') {
            $nombre_completo = trim((string)($_POST['nombre_completo'] ?? ''));
            $usuario = trim((string)($_POST['usuario'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
            $roles = $_POST['roles'] ?? [];
            $password = (string)($_POST['password'] ?? '');

            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $roles = array_values(array_unique(array_filter(array_map(function ($r) {
                return strtoupper(trim((string)$r));
            }, $roles))));

            if ($nombre_completo === '' || $usuario === '' || $email === '' || empty($roles) || $password === '') {
                throw new RuntimeException('Completa todos los campos para crear el usuario.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('El correo electronico no tiene un formato valido.');
            }

            foreach ($roles as $r) {
                if (!in_array($r, $roles_validos, true)) {
                    throw new RuntimeException('El rol seleccionado no es valido.');
                }
            }
            $rolCsv = implode(',', $roles);

            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $activo = 1;

            $sql = "INSERT INTO usuarios_admin (nombre_completo, usuario, password, email, whatsapp, rol, activo)
                    VALUES (:nombre_completo, :usuario, :password, :email, :whatsapp, :rol, :activo)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nombre_completo' => $nombre_completo,
                ':usuario' => $usuario,
                ':password' => $password_hash,
                ':email' => $email,
                ':whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                ':rol' => $rolCsv,
                ':activo' => $activo
            ]);

            $mailOk = false;
            try {
                $mailOk = sendWelcomeEmail($email, $nombre_completo, $rolCsv);
            } catch (Throwable $e) {
                $mailOk = false;
                error_log('mail() exception: ' . $e->getMessage());
            }

            $_SESSION['flash_success'] = 'Usuario creado correctamente.';
            if (!$mailOk) {
                $_SESSION['flash_warning'] = 'Usuario creado. Nota: El servidor de correo local no está configurado, el email de bienvenida no se envió externamente.';
            }
            header('Location: usuarios.php');
            exit;
        }

        if ($accion === 'actualizar') {
            $id = (int)($_POST['id'] ?? 0);
            $nombre_completo = trim((string)($_POST['nombre_completo'] ?? ''));
            $usuario = trim((string)($_POST['usuario'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
            $roles = $_POST['roles'] ?? [];
            $password = (string)($_POST['password'] ?? '');

            if ($id <= 0 || $nombre_completo === '' || $usuario === '' || $email === '') {
                throw new RuntimeException('Datos incompletos para actualizar el usuario.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('El correo electronico no tiene un formato valido.');
            }

            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $roles = array_values(array_unique(array_filter(array_map(function ($r) {
                return strtoupper(trim((string)$r));
            }, $roles))));
            if (empty($roles)) {
                throw new RuntimeException('Selecciona al menos un rol.');
            }
            foreach ($roles as $r) {
                if (!in_array($r, $roles_validos, true)) {
                    throw new RuntimeException('El rol seleccionado no es valido.');
                }
            }
            $rolCsv = implode(',', $roles);

            if ($password !== '') {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios_admin
                        SET nombre_completo = :nombre_completo, usuario = :usuario, email = :email, whatsapp = :whatsapp, rol = :rol, password = :password
                        WHERE id = :id";
                $params = [
                    ':nombre_completo' => $nombre_completo,
                    ':usuario' => $usuario,
                    ':email' => $email,
                    ':whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                    ':rol' => $rolCsv,
                    ':password' => $password_hash,
                    ':id' => $id
                ];
            } else {
                $sql = "UPDATE usuarios_admin
                        SET nombre_completo = :nombre_completo, usuario = :usuario, email = :email, whatsapp = :whatsapp, rol = :rol
                        WHERE id = :id";
                $params = [
                    ':nombre_completo' => $nombre_completo,
                    ':usuario' => $usuario,
                    ':email' => $email,
                    ':whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                    ':rol' => $rolCsv,
                    ':id' => $id
                ];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $_SESSION['flash_success'] = 'Usuario actualizado correctamente.';
            header('Location: usuarios.php');
            exit;
        }

        if ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('No se recibio un id valido para eliminar.');
            }

            if ($adminIdSesion > 0 && $adminIdSesion === $id) {
                throw new RuntimeException('No puedes eliminar tu propio usuario.');
            }

            $stmt = $db->prepare('DELETE FROM usuarios_admin WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $_SESSION['flash_success'] = 'Usuario eliminado correctamente.';
            header('Location: usuarios.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Error PDO en usuarios.php: ' . $e->getMessage());
        if (isDuplicateKey($e)) {
            $error_message = 'Este correo electronico ya esta registrado con otro usuario.';
        } else {
            $error_message = $debug_pdo_message
                ? ('Error en la base de datos: ' . $e->getMessage())
                : 'No fue posible completar la accion solicitada.';
        }
    } catch (RuntimeException $e) {
        $error_message = $e->getMessage();
    }
}

$usuarios = [];
try {
    if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
        $id_editar = (int)$_GET['editar'];
        $stmtEdit = $db->prepare('SELECT id, nombre_completo, usuario, email, whatsapp, rol FROM usuarios_admin WHERE id = :id');
        $stmtEdit->execute([':id' => $id_editar]);
        $usuario_edicion = $stmtEdit->fetch(PDO::FETCH_ASSOC);
        $modo_edicion = $usuario_edicion !== false;
    }

    $stmt = $db->query('SELECT id, nombre_completo, usuario, email, whatsapp, rol, ultimo_acceso FROM usuarios_admin ORDER BY id ASC');
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error PDO al cargar usuarios.php: ' . $e->getMessage());
    if ($error_message === '') {
        $error_message = 'No fue posible cargar el listado. Ejecuta primero la migracion de usuarios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALISER Admin | Usuarios</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body class="admin-body-secondary">
    <div class="admin-wrapper">
        <header class="admin-header-main">
            <div class="header-content">
                <h1 class="admin-title">Usuarios del Sistema</h1>
                <p class="admin-subtitle">Gestion de accesos por rol</p>
            </div>
            <div class="header-actions">
                <a href="guia_bienvenida.php" class="btn-secondary">Guia de Bienvenida</a>
                <a href="dashboard.php" class="login-btn">
                    <span class="btn-text">Volver al Panel</span>
                    <span class="btn-glow"></span>
                </a>
            </div>
        </header>

        <main class="admin-content-card">
            <?php if ($error_message !== ''): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message !== ''): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($warning_message !== ''): ?>
                <div class="warning-message"><?php echo htmlspecialchars($warning_message); ?></div>
            <?php endif; ?>

            <h2 class="modules-title"><?php echo $modo_edicion ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h2>
            <form method="POST" action="usuarios.php" class="admin-form" autocomplete="off">
                <input type="hidden" name="accion" value="<?php echo $modo_edicion ? 'actualizar' : 'crear'; ?>">
                <?php if ($modo_edicion && $usuario_edicion): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$usuario_edicion['id']; ?>">
                <?php endif; ?>
                <?php
                $roles_seleccionados = [];
                if ($modo_edicion && $usuario_edicion && isset($usuario_edicion['rol'])) {
                    $roles_seleccionados = preg_split('/\s*,\s*/', (string)$usuario_edicion['rol'], -1, PREG_SPLIT_NO_EMPTY);
                    $roles_seleccionados = array_values(array_unique(array_map('strtoupper', $roles_seleccionados)));
                }
                ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="nombre_completo">Nombre Completo</label>
                        <input class="form-input" type="text" name="nombre_completo" id="nombre_completo" required value="<?php echo htmlspecialchars((string)($usuario_edicion['nombre_completo'] ?? '')); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="usuario">Usuario</label>
                        <input class="form-input" type="text" name="usuario" id="usuario" required value="<?php echo htmlspecialchars((string)($usuario_edicion['usuario'] ?? '')); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-input" type="email" name="email" id="email" required value="<?php echo htmlspecialchars((string)($usuario_edicion['email'] ?? '')); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="whatsapp">WhatsApp</label>
                        <input class="form-input" type="text" name="whatsapp" id="whatsapp" value="<?php echo htmlspecialchars((string)($usuario_edicion['whatsapp'] ?? '')); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Roles (multiple)</label>
                        <div class="roles-multi">
                            <?php foreach ($roles_validos as $rol_item): ?>
                                <?php $checked = in_array($rol_item, $roles_seleccionados, true); ?>
                                <label class="role-check">
                                    <input type="checkbox" name="roles[]" value="<?php echo htmlspecialchars($rol_item); ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($rol_item); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password"><?php echo $modo_edicion ? 'Nueva Contrasena (Opcional)' : 'Contrasena'; ?></label>
                        <input class="form-input" type="password" name="password" id="password" <?php echo $modo_edicion ? '' : 'required'; ?>>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="login-btn shimmer">
                        <span class="btn-text"><?php echo $modo_edicion ? 'Actualizar Usuario' : 'Crear Usuario'; ?></span>
                        <span class="btn-glow"></span>
                    </button>
                    <?php if ($modo_edicion): ?>
                        <a href="usuarios.php" class="btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>

            <h2 class="modules-title" style="margin-top:1.4rem;">Listado de Usuarios</h2>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th>Último Acceso</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr class="table-row-hover">
                                <td><?php echo (int)$u['id']; ?></td>
                                <td><?php echo htmlspecialchars((string)$u['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars((string)$u['usuario']); ?></td>
                                <td><?php echo htmlspecialchars((string)$u['email']); ?></td>
                                <td>
                                    <?php
                                    $waRaw = (string)$u['whatsapp'];
                                    $waDigits = preg_replace('/\D+/', '', $waRaw);
                                    ?>
                                    <?php if ($waDigits !== ''): ?>
                                        <a href="https://wa.me/<?php echo htmlspecialchars($waDigits); ?>" target="_blank" rel="noopener noreferrer" class="btn-edit">
                                            <?php echo htmlspecialchars($waRaw); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($waRaw); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $ua = (string)($u['ultimo_acceso'] ?? '');
                                    echo $ua === '' ? 'Nunca' : htmlspecialchars(date('d/m/Y H:i', strtotime($ua)));
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $rolParts = preg_split('/\s*,\s*/', (string)$u['rol'], -1, PREG_SPLIT_NO_EMPTY);
                                    $rolParts = array_values(array_unique(array_map(function ($r) {
                                        return strtoupper(trim((string)$r));
                                    }, $rolParts)));
                                    ?>
                                    <?php foreach ($rolParts as $rolValue): ?>
                                        <?php $rolCss = 'role-' . strtolower($rolValue); ?>
                                        <span class="status-badge role-badge <?php echo htmlspecialchars($rolCss); ?>"><?php echo htmlspecialchars($rolValue); ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <a class="btn-edit" href="usuarios.php?editar=<?php echo (int)$u['id']; ?>">Editar</a>
                                    <?php if ((int)$u['id'] !== $adminIdSesion): ?>
                                        <form method="POST" action="usuarios.php" style="display:inline-block;" onsubmit="return confirm('¿Eliminar usuario?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                            <button type="submit" class="btn-delete" style="background:none;border:0;cursor:pointer;">Eliminar</button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn-delete" style="background:none;border:0;cursor:not-allowed;opacity:0.5;" disabled>Eliminar</button>
                                    <?php endif; ?>
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
<script>
  (function () {
    const form = document.querySelector('form.admin-form');
    const email = document.getElementById('email');
    if (!form || !email) return;

    form.addEventListener('submit', function (e) {
      const value = String(email.value || '').trim();
      if (!value) return;
      const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
      if (!ok) {
        e.preventDefault();
        alert('El correo electronico no tiene un formato valido.');
        email.focus();
      }
    });
  })();
</script>
