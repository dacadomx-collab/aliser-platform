<?php
if (session_status() === PHP_SESSION_NONE) {
    $forwardedProto = (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
    $httpsOn = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $secure = ($forwardedProto === 'https') || $httpsOn;

    // Cookie params must be set before session_start.
    // LocalTunnel terminates TLS, so we also honor X-Forwarded-Proto=https.
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    session_start();
}

function normalizeRole(string $role): string
{
    $map = [
        'admin' => 'MASTER',
        'editor' => 'SOPORTE',
        'master' => 'MASTER',
        'talento' => 'TALENTO',
        'bienes' => 'BIENES',
        'promo' => 'PROMO',
        'marca' => 'MARCA',
        'soporte' => 'SOPORTE'
    ];

    $key = strtolower(trim($role));
    return $map[$key] ?? strtoupper($role);
}

function normalizeRoles($rolesRaw): string
{
    if (is_array($rolesRaw)) {
        $parts = $rolesRaw;
    } else {
        $raw = (string)$rolesRaw;
        $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
    }

    $out = [];
    foreach ($parts as $part) {
        $role = normalizeRole((string)$part);
        if ($role === '') continue;
        $out[$role] = true;
    }

    if (empty($out)) {
        return 'SOPORTE';
    }

    return implode(',', array_keys($out));
}

function requireAdminLogin(): void
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: index.php');
        exit;
    }
}

function getCurrentAdminRolesCsv(): string
{
    $sessionRole = $_SESSION['admin_rol'] ?? 'SOPORTE';
    $normalized = normalizeRoles($sessionRole);
    $_SESSION['admin_rol'] = $normalized;
    return $normalized;
}

function getCurrentAdminRoles(): array
{
    $csv = getCurrentAdminRolesCsv();
    $parts = preg_split('/\s*,\s*/', $csv, -1, PREG_SPLIT_NO_EMPTY);
    $out = [];
    foreach ($parts as $p) {
        $out[] = normalizeRole((string)$p);
    }
    return array_values(array_unique(array_filter($out)));
}

function userHasAnyRole(array $allowedRoles): bool
{
    $current = getCurrentAdminRoles();
    foreach ($current as $r) {
        if (in_array($r, $allowedRoles, true)) {
            return true;
        }
    }
    return false;
}

function requireRole(array $allowedRoles): void
{
    requireAdminLogin();
    if (!userHasAnyRole($allowedRoles)) {
        header('Location: dashboard.php?denied=1');
        exit;
    }
}
