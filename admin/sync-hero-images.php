<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$mediaDir = __DIR__ . '/../media';
$supabase = getSupabase();

$heroFiles = array();
$missisFiles = array();
foreach (glob($mediaDir . '/hero-*.*') as $f) { $heroFiles[] = 'media/' . basename($f); }
foreach (glob($mediaDir . '/missis-*.*') as $f) { $missisFiles[] = 'media/' . basename($f); }
usort($heroFiles, 'strcmp');
usort($missisFiles, 'strcmp');

echo "Found on disk: " . count($heroFiles) . " hero, " . count($missisFiles) . " missis\n";

function upsert($supabase, $key, $value) {
    $existing = $supabase->select('content_blocks', array('page' => 'eq.index', 'block_key' => 'eq.' . $key, 'select' => 'id'));
    $payload = array('block_value' => json_encode($value));
    if ($existing && !isset($existing['error']) && count($existing) > 0) {
        $supabase->update('content_blocks', $payload, array('page' => 'eq.index', 'block_key' => 'eq.' . $key));
        echo "  Updated: $key\n";
    } else {
        $supabase->insert('content_blocks', array_merge($payload, array('page' => 'index', 'block_key' => $key)));
        echo "  Inserted: $key\n";
    }
}

upsert($supabase, 'hero_images', $heroFiles);
upsert($supabase, 'missis_images', array());

echo "\nDone! Hero: " . count($heroFiles) . " images active, Missis: 0\n";
echo "Files:\n";
foreach ($heroFiles as $f) echo "  $f\n";
