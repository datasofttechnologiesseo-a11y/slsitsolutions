<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Send an enquiry notification email.
 * Returns [bool ok, string error]
 */
function send_enquiry_mail(array $enq): array {
    $cfg = app_config('mail');

    $subject = 'New Enquiry from Website — ' . ($enq['name'] ?? 'Unknown');
    $html    = render_enquiry_html($enq);
    $text    = render_enquiry_text($enq);

    if (($cfg['driver'] ?? 'smtp') === 'mail') {
        return send_via_mail($cfg, $subject, $html, $text, $enq['email'] ?? null, $enq['name'] ?? null);
    }
    return send_via_smtp($cfg, $subject, $html, $text, $enq['email'] ?? null, $enq['name'] ?? null);
}

function send_via_smtp(array $cfg, string $subject, string $html, string $text, ?string $replyEmail, ?string $replyName): array {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->Port       = (int)$cfg['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['username'];
        $mail->Password   = $cfg['password'];
        $mail->SMTPSecure = $cfg['encryption'] === 'tls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($cfg['from_email'], $cfg['from_name']);
        $mail->addAddress($cfg['to_email'], $cfg['to_name'] ?? '');
        if ($replyEmail && filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyEmail, $replyName ?? '');
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $text;

        $mail->send();
        return [true, ''];
    } catch (MailException $e) {
        return [false, $mail->ErrorInfo ?: $e->getMessage()];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}

function send_via_mail(array $cfg, string $subject, string $html, string $text, ?string $replyEmail, ?string $replyName): array {
    $boundary = '=_' . bin2hex(random_bytes(8));
    $headers  = [];
    $headers[] = 'From: ' . sprintf('"%s" <%s>', $cfg['from_name'], $cfg['from_email']);
    if ($replyEmail && filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . sprintf('"%s" <%s>', $replyName ?? '', $replyEmail);
    }
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

    $body  = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n" . $text . "\r\n\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n" . $html . "\r\n\r\n";
    $body .= "--{$boundary}--";

    $ok = @mail($cfg['to_email'], $subject, $body, implode("\r\n", $headers));
    return $ok ? [true, ''] : [false, 'mail() returned false'];
}

function render_enquiry_html(array $e): string {
    $rows = [
        'Name'    => $e['name']    ?? '',
        'Company' => $e['company'] ?? '',
        'Email'   => $e['email']   ?? '',
        'Phone'   => $e['phone']   ?? '',
        'Service' => $e['service'] ?? '',
        'IP'      => $e['ip']      ?? '',
        'Submitted' => date('Y-m-d H:i:s'),
    ];
    $html  = '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">';
    $html .= '<div style="background:#0f4c81;color:#fff;padding:18px 22px"><h2 style="margin:0;font-size:18px">New Website Enquiry</h2></div>';
    $html .= '<div style="padding:22px"><table style="width:100%;border-collapse:collapse;font-size:14px">';
    foreach ($rows as $label => $val) {
        if ($val === '' || $val === null) continue;
        $html .= '<tr><td style="padding:6px 0;color:#6b7280;width:110px">'.htmlspecialchars($label).'</td>';
        $html .= '<td style="padding:6px 0;color:#0f172a">'.htmlspecialchars((string)$val).'</td></tr>';
    }
    $html .= '</table>';
    $html .= '<div style="margin-top:18px;padding-top:18px;border-top:1px solid #e5e7eb">';
    $html .= '<div style="color:#6b7280;font-size:13px;margin-bottom:6px">Message</div>';
    $html .= '<div style="white-space:pre-wrap;color:#0f172a;font-size:14px;line-height:1.55">'.nl2br(htmlspecialchars($e['message'] ?? '')).'</div>';
    $html .= '</div></div></div>';
    return $html;
}

function render_enquiry_text(array $e): string {
    $lines = [
        'New Website Enquiry',
        str_repeat('-', 40),
        'Name:    ' . ($e['name']    ?? ''),
        'Company: ' . ($e['company'] ?? ''),
        'Email:   ' . ($e['email']   ?? ''),
        'Phone:   ' . ($e['phone']   ?? ''),
        'Service: ' . ($e['service'] ?? ''),
        'IP:      ' . ($e['ip']      ?? ''),
        'Time:    ' . date('Y-m-d H:i:s'),
        '',
        'Message:',
        $e['message'] ?? '',
    ];
    return implode("\n", $lines);
}
