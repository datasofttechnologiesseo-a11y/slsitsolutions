<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['file']['error'] ?? 'no file';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $err]);
    exit;
}

$f = $_FILES['file'];

// Size cap: 5 MB
$maxBytes = 5 * 1024 * 1024;
if ($f['size'] > $maxBytes) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'File too large (max 5 MB).']);
    exit;
}

// MIME / extension whitelist
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $f['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    http_response_code(415);
    echo json_encode(['success' => false, 'message' => 'Unsupported image type. Allowed: JPG, PNG, WEBP, GIF.']);
    exit;
}

$ext = $allowed[$mime];

// Store with random hashed filename so users can't guess paths
$dir  = __DIR__ . '/../assets/uploads/blog';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$name = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
$dest = $dir . '/' . $name;

if (!move_uploaded_file($f['tmp_name'], $dest)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save file.']);
    exit;
}
@chmod($dest, 0644);

$url = 'assets/uploads/blog/' . $name;
echo json_encode([
    'success' => true,
    'url'     => $url,        // relative to project root — works on public site
    'name'    => $name,
]);
