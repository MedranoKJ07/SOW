<?php

/** Start a hardened session exactly once for every entry point. */
function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Require an authenticated user with one of the supplied application roles. */
function require_role(array $roles, string $loginPath = 'login.php'): void
{
    secure_session_start();
    $role = (string) ($_SESSION['rol'] ?? '');
    if (empty($_SESSION['id_usuario']) || !in_array($role, $roles, true)) {
        $target = defined('BASE_URL') ? BASE_URL . ltrim($loginPath, '/') : $loginPath;
        header('Location: ' . $target);
        exit;
    }
}

function csrf_token(string $namespace = 'default'): string
{
    secure_session_start();
    $key = 'csrf_' . preg_replace('/[^a-z0-9_]/i', '_', $namespace);
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }
    return $_SESSION[$key];
}

function csrf_valid(string $token, string $namespace = 'default'): bool
{
    $expected = csrf_token($namespace);
    return $token !== '' && hash_equals($expected, $token);
}
