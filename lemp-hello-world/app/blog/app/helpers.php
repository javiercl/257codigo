<?php
declare(strict_types=1);

/**
 * Helpers compartidos del blog
 * - Sesión propia del blog
 * - Utilidades JSON
 * - CSRF
 * - Normalización/validación
 * - Rate limit en memoria de sesión
 */

require_once __DIR__ . '/db.php'; // Debe definir db(): PDO y NO imprimir nada

/* ---- Sesión separada para el blog ---- */
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Usa un nombre de sesión distinto para no chocar con otras prácticas
    session_name('BLOGSESSID');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // descomenta si sirves por HTTPS
    ]);
    session_start();
}

/* ------------- Utilidades JSON ------------- */

function json_ok(array $data = [], int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $msg, int $status = 400, array $errors = []): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    $payload = ['ok' => false, 'error' => $msg];
    if (!empty($errors)) $payload['errors'] = $errors;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ------------- Escapado / helpers de texto ------------- */

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $title): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim((string)$slug, '-');
    return $slug !== '' ? $slug : 'post';
}

/** Normaliza usernames: minúsculas, espacios→'_', sólo [a-z0-9_], 3–20 chars */
function normalize_username(string $input, string $email = ''): string {
    $u = strtolower(trim($input));
    if ($u === '' && $email !== '') {
        $u = strtolower(explode('@', $email, 2)[0] ?? '');
    }
    $u = preg_replace('/[^a-z0-9_]+/', '_', $u);
    $u = trim((string)$u, '_');
    if ($u === '' && $email !== '') {
        $u = 'user_' . bin2hex(random_bytes(2));
    }
    if (strlen($u) < 3) $u = str_pad($u, 3, '_');
    if (strlen($u) > 20) $u = substr($u, 0, 20);
    return $u;
}

/** Valida username ya normalizado */
function valid_username(string $u): bool {
    return (bool)preg_match('/^[a-z0-9_]{3,20}$/', $u);
}

function valid_password(string $p): bool {
    return strlen($p) >= 8
        && preg_match('/[A-Z]/', $p)
        && preg_match('/[a-z]/', $p)
        && preg_match('/\d/', $p);
}

/* ------------------- CSRF ------------------- */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): void {
    if (!$token || !isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        json_error('CSRF inválido', 403);
    }
}

/* ------------- Rate limit simple (en sesión) ------------- */

function rl_get(string $key, int $windowSeconds = 600): array {
    $now = time();
    $rl  = $_SESSION['rl'][$key] ?? ['count' => 0, 'from' => $now];
    if ($now - ($rl['from'] ?? $now) > $windowSeconds) {
        $rl = ['count' => 0, 'from' => $now];
    }
    return $rl;
}

function rl_save(string $key, array $data): void {
    $_SESSION['rl'][$key] = $data;
}

function rl_fail(string $key, int $max = 5): void {
    $rl = rl_get($key);
    $rl['count']++;
    rl_save($key, $rl);
    if ($rl['count'] >= $max) {
        json_error('Demasiados intentos, intenta más tarde.', 429);
    }
}

function rl_reset(string $key): void {
    rl_save($key, ['count' => 0, 'from' => time()]);
}

/* ------------- Auth helpers básicos ------------- */

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) json_error('No autenticado', 401);
}
