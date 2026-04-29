<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/../includes/mailer.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}
if (!csrf_check()) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'CSRF token invalid. Please reload the page.']);
    exit;
}

$to = trim((string)($_POST['to'] ?? ''));
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid recipient email address.']);
    exit;
}

// Build the SMTP config from posted form values (so admin can test BEFORE saving).
// If a value isn't posted, fall back to the saved DB value (or app config).
$saved = mail_config();

$cfg = [
    'driver'     => $_POST['mail_driver']     ?? $saved['driver']     ?? 'smtp',
    'host'       => $_POST['mail_host']       ?? $saved['host']       ?? '',
    'port'       => (int)($_POST['mail_port'] ?? $saved['port']       ?? 465),
    'encryption' => $_POST['mail_encryption'] ?? $saved['encryption'] ?? 'ssl',
    'username'   => $_POST['mail_username']   ?? $saved['username']   ?? '',
    // Password: if blank in form, use saved value (so admin doesn't have to retype it on every test)
    'password'   => ($_POST['mail_password'] ?? '') !== '' ? $_POST['mail_password'] : ($saved['password'] ?? ''),
    'from_email' => $_POST['mail_from_email'] ?? $saved['from_email'] ?? '',
    'from_name'  => $_POST['mail_from_name']  ?? $saved['from_name']  ?? 'Website',
    'to_email'   => $to,
    'to_name'    => 'Test',
];

$user      = admin_user();
$nowStr    = date('Y-m-d H:i:s T');
$subject   = 'SLS IT Solutions — SMTP Test Email';
$preheader = 'If you received this, your SMTP settings are working correctly.';

$html  = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden">';
$html .= '<div style="background:#0f4c81;color:#fff;padding:20px 24px"><h2 style="margin:0;font-size:18px">✓ SMTP Test Successful</h2></div>';
$html .= '<div style="padding:24px;font-size:14px;color:#0f172a;line-height:1.6">';
$html .= '<p>Hello,</p>';
$html .= '<p>This is a test email sent from the <strong>SLS IT Solutions</strong> admin panel to verify that the SMTP configuration is working correctly.</p>';
$html .= '<table style="width:100%;border-collapse:collapse;margin-top:16px;font-size:13px">';
$html .= '<tr><td style="padding:6px 0;color:#64748b;width:120px">Sent at</td><td style="color:#0f172a">' . htmlspecialchars($nowStr) . '</td></tr>';
$html .= '<tr><td style="padding:6px 0;color:#64748b">Triggered by</td><td>' . htmlspecialchars($user['email'] ?? 'admin') . '</td></tr>';
$html .= '<tr><td style="padding:6px 0;color:#64748b">SMTP host</td><td>' . htmlspecialchars($cfg['host']) . ':' . (int)$cfg['port'] . ' (' . htmlspecialchars(strtoupper($cfg['encryption'])) . ')</td></tr>';
$html .= '</table>';
$html .= '<p style="margin-top:18px;color:#64748b;font-size:12px">If you received this email, your contact-form enquiries will be delivered successfully.</p>';
$html .= '</div></div>';

$text = "SLS IT Solutions — SMTP Test\n"
      . str_repeat('-', 36) . "\n"
      . "$preheader\n\n"
      . "Sent at:    $nowStr\n"
      . "Triggered:  " . ($user['email'] ?? 'admin') . "\n"
      . "SMTP host:  " . $cfg['host'] . ':' . (int)$cfg['port'] . ' (' . strtoupper($cfg['encryption']) . ")\n";

[$ok, $err] = send_mail($to, 'Test', $subject, $html, $text, $cfg);

if ($ok) {
    echo json_encode([
        'success' => true,
        'message' => "Test email sent to {$to}. Check the inbox (and spam folder) — it should arrive within seconds.",
    ]);
} else {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'SMTP error: ' . $err,
    ]);
}
