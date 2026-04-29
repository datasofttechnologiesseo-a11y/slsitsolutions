<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '') {
    echo json_encode(['tags' => []]);
    exit;
}

$stmt = db()->prepare(
    'SELECT id, name, slug FROM blog_tags WHERE name LIKE ? OR slug LIKE ? ORDER BY name LIMIT 10'
);
$like = "%{$q}%";
$stmt->execute([$like, $like]);
$rows = $stmt->fetchAll();

echo json_encode(['tags' => $rows]);
