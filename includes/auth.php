<?php
require_once __DIR__ . '/db.php';

function admin_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_name('SLSADMIN');
    session_start();
}

function admin_user(): ?array {
    admin_session_start();
    if (empty($_SESSION['admin_id'])) return null;
    return [
        'id'    => $_SESSION['admin_id'],
        'name'  => $_SESSION['admin_name']  ?? 'Admin',
        'email' => $_SESSION['admin_email'] ?? '',
    ];
}

function require_admin(): void {
    if (!admin_user()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function admin_login(string $email, string $password): array {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (login_locked($ip)) {
        return [false, 'Too many failed attempts. Try again in 15 minutes.'];
    }

    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password_hash'])) {
        log_login_attempt($ip, $email, false);
        return [false, 'Invalid email or password.'];
    }

    log_login_attempt($ip, $email, true);
    admin_session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id']    = (int)$row['id'];
    $_SESSION['admin_name']  = $row['name'];
    $_SESSION['admin_email'] = $row['email'];
    return [true, ''];
}

function admin_logout(): void {
    admin_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function log_login_attempt(string $ip, string $email, bool $success): void {
    $stmt = db()->prepare('INSERT INTO login_attempts (ip, email, success) VALUES (?, ?, ?)');
    $stmt->execute([$ip, $email, $success ? 1 : 0]);
}

function login_locked(string $ip): bool {
    $stmt = db()->prepare(
        'SELECT COUNT(*) AS c FROM login_attempts
         WHERE ip = ? AND success = 0 AND attempted_at > (NOW() - INTERVAL 15 MINUTE)'
    );
    $stmt->execute([$ip]);
    $row = $stmt->fetch();
    return ((int)$row['c']) >= 5;
}
