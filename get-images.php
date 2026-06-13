<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$mediaDir = __DIR__ . '/media';
$images = array();
if (is_dir($mediaDir)) {
    $files = glob($mediaDir . '/lueta-*.*');
    foreach ($files as $file) {
        $images[] = 'media/' . basename($file);
    }
}
sort($images);
echo json_encode($images);
