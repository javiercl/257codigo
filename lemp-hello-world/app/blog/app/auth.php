<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * Autenticación para el blog
 * - Registro
 * - Login
 * - Logout
 * Todas las funciones responden con json_ok/json_error (nunca echo/HTML)
 */

function login_user(string $login, string $password): void {
    $pdo = db();

    $raw       = trim($login);
    $login_lc  = strtolower($raw);
    $login_norm= normalize_username($raw, $login_lc);

    $sql = "SELECT id, username, email, password_hash, role
            FROM users
            WHERE email = :e OR username = :u1 OR username = :u2
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':e'  => $login_lc,
        ':u1' => $login_lc,
        ':u2' => $login_norm,
    ]);

    $u = $stmt->fetch();
    if (!$u || !password_verify($password, $u['password_hash'])) {
        rl_fail('login', 5);                 // rate-limit sólo en fallos
        json_error('Credenciales inválidas', 401);
    }

    rl_reset('login');                       // éxito → resetea conteo

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'       => (int)$u['id'],
        'username' => $u['username'],
        'email'    => $u['email'],
        'role'     => $u['role'] ?? 'author',
    ];
    json_ok(['user' => $_SESSION['user']]);
}

function register_user(string $username, string $email, string $password, string $confirm): void {
    $email    = strtolower(trim($email));
    $normUser = normalize_username($username, $email);

    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors['email']    = 'Email inválido';
    if (!valid_username($normUser))                      $errors['username'] = 'Sólo [a-z0-9_], 3–20';
    if (!valid_password($password))                      $errors['password'] = 'Mín. 8, 1 mayús, 1 minús y 1 dígito';
    if ($password !== $confirm)                          $errors['confirm']  = 'No coincide';

    if ($errors) json_error('Datos inválidos', 422, $errors);

    $pdo = db();
    $exists = $pdo->prepare("SELECT 1 FROM users WHERE username = :u OR email = :e LIMIT 1");
    $exists->execute([':u' => $normUser, ':e' => $email]);
    if ($exists->fetch()) {
        json_error('Usuario o email ya registrado', 422, ['username'=>'ocupado', 'email'=>'ocupado']);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins  = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, created_at)
                           VALUES (:u, :e, :h, 'admin', NOW())");
    $ins->execute([':u'=>$normUser, ':e'=>$email, ':h'=>$hash]);
    $id = (int)$pdo->lastInsertId();

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'=>$id, 'username'=>$normUser, 'email'=>$email, 'role'=>'admin'
    ];
    json_ok(['user' => $_SESSION['user']]);
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool)($p['secure'] ?? false), (bool)($p['httponly'] ?? true));
    }
    session_destroy();
    json_ok();
}
