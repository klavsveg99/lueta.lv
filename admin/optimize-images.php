<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

set_time_limit(300);
$mediaDir = __DIR__ . '/../media';
$results = array();
$optimized = 0;
$skipped = 0;
$failed = 0;

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function getAllImages($dir) {
    $images = array();
    if (!is_dir($dir)) return $images;
    $handle = opendir($dir);
    if (!$handle) return $images;
    while (($entry = readdir($handle)) !== false) {
        if ($entry === '.' || $entry === '..') continue;
        $path = $dir . '/' . $entry;
        if (is_dir($path)) continue;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif')) && filesize($path) > 0) {
            $images[] = $path;
        }
    }
    closedir($handle);
    sort($images);
    return $images;
}

$allFiles = getAllImages($mediaDir);

if (isset($_POST['optimize_all'])) {
    foreach ($allFiles as $file) {
        $before = filesize($file);
        $name = basename($file);
        $result = optimizeImage($file);
        $newFile = is_string($result) ? $result : $file;
        $after = file_exists($newFile) ? filesize($newFile) : $before;
        if ($result === 'skip') {
            $results[] = array('name' => $name, 'before' => $before, 'after' => $before, 'status' => 'skip');
            $skipped++;
        } elseif ($result) {
            if ($after < $before) {
                $results[] = array('name' => $name, 'before' => $before, 'after' => $after, 'status' => 'ok');
                $optimized++;
            } else {
                $results[] = array('name' => $name, 'before' => $before, 'after' => $after, 'status' => 'skip');
                $skipped++;
            }
        } else {
            $results[] = array('name' => $name, 'before' => $before, 'after' => $before, 'status' => 'fail');
            $failed++;
        }
    }
    $allFiles = getAllImages($mediaDir);
}

$supabase = getSupabase();
$dbPaths = array();
$dbMissing = 0;
$dbFound = 0;
foreach (array('hero_images', 'missis_images') as $bk) {
    $rows = $supabase->select('content_blocks', array('page' => 'eq.index', 'block_key' => 'eq.' . $bk, 'select' => 'block_value'));
    if ($rows && !isset($rows['error']) && count($rows) > 0) {
        $decoded = json_decode($rows[0]['block_value'], true);
        if (is_array($decoded)) $dbPaths = array_merge($dbPaths, $decoded);
    }
}
foreach ($dbPaths as $p) {
    $fp = $mediaDir . '/' . basename($p);
    if (file_exists($fp) && filesize($fp) > 100) { $dbFound++; } else { $dbMissing++; }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attēlu optimizēšana - Lueta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
</head>
<body>
    <header class="admin-header">
        <div class="header-left">
            <a href="index.php" class="logo">Lueta<span>.</span></a>
            <h1>Attēlu optimizēšana</h1>
        </div>
        <a href="index.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Atpakaļ</a>
    </header>
    <main class="page-content" style="padding:var(--page-padding)">
        <?php if (!extension_loaded('gd')): ?>
        <div class="card">
            <div style="padding:16px; background:#fff3cd; border-radius:var(--radius); color:#856404">
                <i class="fa-solid fa-triangle-exclamation"></i> PHP GD nav pieejams. Optimizēšana nav iespējama.
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fa-solid fa-folder-open"></i> Faili /media/ mapē (<?= count($allFiles) ?>)</h2>
            <table style="width:100%; border-collapse:collapse; margin-top:12px">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border)">Faila nosaukums</th>
                        <th style="text-align:right; padding:8px; border-bottom:2px solid var(--border)">Lielums</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalSize = 0; foreach ($allFiles as $f): $size = filesize($f); $totalSize += $size; $name = basename($f); ?>
                    <tr>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); font-size:13px; font-family:monospace"><?= htmlspecialchars($name) ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px"><?= formatSize($size) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td style="padding:8px; border-top:2px solid var(--border); font-weight:600">Kopā: <?= count($allFiles) ?> faili</td>
                        <td style="padding:8px; border-top:2px solid var(--border); text-align:right; font-weight:600"><?= formatSize($totalSize) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (!empty($dbPaths)): ?>
        <div class="card" style="margin-top:24px">
            <h2><i class="fa-solid fa-database"></i> Datubāzes ceļi vs failsistēma</h2>
            <p style="color:var(--text-muted); margin-bottom:12px; font-size:14px">
                <span style="color:#28a745"><strong><?= $dbFound ?></strong> eksistē</span> |
                <span style="color:#dc3545"><strong><?= $dbMissing ?></strong> trūkst</span>
                — trūkstošie jāpārūpējas caur admin paneli → Attēli.
            </p>
            <table style="width:100%; border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border)">Ceļš</th>
                        <th style="text-align:center; padding:8px; border-bottom:2px solid var(--border)">Statuss</th>
                        <th style="text-align:right; padding:8px; border-bottom:2px solid var(--border)">Lielums</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dbPaths as $p): $fp = $mediaDir . '/' . basename($p); $exists = file_exists($fp) && filesize($fp) > 100; ?>
                    <tr>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); font-size:13px; font-family:monospace"><?= htmlspecialchars($p) ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:center; font-size:13px">
                            <?php if ($exists): ?><span style="color:#28a745"><i class="fa-solid fa-check"></i></span>
                            <?php else: ?><span style="color:#dc3545"><i class="fa-solid fa-xmark"></i></span><?php endif; ?>
                        </td>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px"><?= $exists ? formatSize(filesize($fp)) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (extension_loaded('gd')): ?>
        <div class="card" style="margin-top:24px">
            <h2><i class="fa-solid fa-bolt"></i> Optimizēt</h2>
            <p style="color:var(--text-muted); margin-bottom:20px">Pārvērš uz WebP un samazina izmērus. Attēli ≤1920px platumā un ≤500KB netiek mainīti.</p>
            <form method="post" style="margin-bottom:20px" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Optimizē...';">
                <input type="hidden" name="optimize_all" value="1">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-bolt"></i> Optimizēt visus (<?= count($allFiles) ?>)</button>
            </form>
            <?php if (!empty($results)): ?>
            <div style="margin-bottom:12px; padding:10px; background:var(--bg2); border-radius:var(--radius); display:flex; gap:20px; font-size:14px">
                <span><strong><?= $optimized ?></strong> optimizēti</span>
                <span><strong><?= $skipped ?></strong> nav nepieciešami</span>
                <span><strong><?= $failed ?></strong> kļūdas</span>
            </div>
            <table style="width:100%; border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border)">Fails</th>
                        <th style="text-align:right; padding:8px; border-bottom:1px solid var(--border)">Pirms</th>
                        <th style="text-align:right; padding:8px; border-bottom:1px solid var(--border)">Pēc</th>
                        <th style="text-align:right; padding:8px; border-bottom:1px solid var(--border)">Statuss</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); font-size:13px"><?= htmlspecialchars($r['name']) ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px"><?= formatSize($r['before']) ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px"><?= formatSize($r['after']) ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px">
                            <?php if ($r['status'] === 'ok'): ?><span style="color:#28a745">-<?= round((1 - $r['after'] / $r['before']) * 100) ?>%</span>
                            <?php elseif ($r['status'] === 'skip'): ?><span style="color:var(--text-muted)">—</span>
                            <?php else: ?><span style="color:#dc3545">kļūda</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
