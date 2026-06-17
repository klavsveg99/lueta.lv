<?php
$file = $_GET['f'] ?? '';

if (!$file || strpos($file, '/') !== false || strpos($file, '..') !== false) {
    http_response_code(400);
    exit('Invalid filename');
}

$dirs = array(__DIR__ . '/../media', __DIR__ . '/media');
$path = null;
foreach ($dirs as $dir) {
    $candidate = $dir . '/' . $file;
    if (file_exists($candidate) && is_file($candidate)) { $path = $candidate; break; }
}

if (!$path) {
    http_response_code(404);
    exit('Not found');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = array(
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
);
$contentType = $types[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . filesize($path));
readfile($path);
