<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Honeypot — bots fill hidden fields, humans don't
if (!empty($_POST['website']) || !empty($_POST['hp_email'])) {
    echo json_encode(['success' => true, 'message' => 'Thank you.']);
    exit;
}

// Rate limit: max 5 submissions per IP per hour
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
try {
    $stmt = db()->prepare(
        'SELECT COUNT(*) AS c FROM enquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)'
    );
    $stmt->execute([$ip]);
    if ((int)$stmt->fetch()['c'] >= 5) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many submissions. Please try again later.']);
        exit;
    }
} catch (\Throwable $e) {
    // continue — don't block on rate-limit failure
}

$name    = trim((string)($_POST['name']    ?? ''));
$company = trim((string)($_POST['company'] ?? ''));
$email   = trim((string)($_POST['email']   ?? ''));
$phone   = trim((string)($_POST['phone']   ?? ''));
$service = trim((string)($_POST['service'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

$errors = [];
if ($name === '' || mb_strlen($name) > 150) $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (mb_strlen($message) > 5000) $errors[] = 'Message is too long.';
if (mb_strlen($phone) > 40)     $errors[] = 'Phone number is too long.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$enq = [
    'name'       => $name,
    'company'    => $company ?: null,
    'email'      => $email,
    'phone'      => $phone ?: null,
    'service'    => $service ?: null,
    'message'    => $message ?: null,
    'ip'         => $ip,
    'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
];

try {
    $stmt = db()->prepare(
        'INSERT INTO enquiries (name, company, email, phone, service, message, ip, user_agent)
         VALUES (:name, :company, :email, :phone, :service, :message, :ip, :user_agent)'
    );
    $stmt->execute($enq);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save your message. Please try again.']);
    exit;
}

// Send mail (non-fatal — enquiry already saved)
[$mailOk, $mailErr] = send_enquiry_mail($enq);

echo json_encode([
    'success'  => true,
    'message'  => 'Thank you! We\'ve received your enquiry and will respond within 2-4 business hours.',
    'mailed'   => $mailOk,
]);
