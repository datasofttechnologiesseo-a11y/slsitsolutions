<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exists' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['exists' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Light rate limit: 20 checks per IP per minute
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
try {
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL 1 MINUTE)'
    );
    $stmt->execute([$ip]);
    if ((int)$stmt->fetchColumn() >= 20) {
        http_response_code(429);
        echo json_encode(['exists' => false, 'message' => 'Too many attempts. Please try again shortly.']);
        exit;
    }
} catch (\Throwable $e) {
    // continue
}

try {
    $stmt = db()->prepare('SELECT id, name FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['exists' => false, 'message' => 'Server error. Please try again.']);
    exit;
}

if (!$row) {
    echo json_encode(['exists' => false, 'message' => 'No account found with this email address.']);
    exit;
}

echo json_encode([
    'exists' => true,
    'name'   => $row['name'],
]);
