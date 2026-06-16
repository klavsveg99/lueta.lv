<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mediaDir = __DIR__ . '/../media';
$results = array();
$optimized = 0;
$skipped = 0;
$failed = 0;
$debug = array();

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
        if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'))) {
            $images[] = $path;
        }
    }
    closedir($handle);
    sort($images);
    return $images;
}

$allFiles = getAllImages($mediaDir);

if (isset($_POST['optimize_all'])) {
    $debug[] = "Starting optimization...";
    $debug[] = "Media directory: $mediaDir";
    $debug[] = "Directory exists: " . (is_dir($mediaDir) ? 'Yes' : 'NO');
    $debug[] = "GD available: " . (extension_loaded('gd') ? 'Yes' : 'No');
    $debug[] = "Files found by getAllImages: " . count($allFiles);

    $allEntries = @scandir($mediaDir);
    if ($allEntries) {
        $debug[] = "scandir total entries: " . count($allEntries);
        $heroFiles = array_filter($allEntries, function($e) { return strpos($e, 'hero-') === 0; });
        $missisFiles = array_filter($allEntries, function($e) { return strpos($e, 'missis-') === 0; });
        $blogFiles = array_filter($allEntries, function($e) { return strpos($e, 'blog-') === 0; });
        $debug[] = "hero-* files in dir: " . count($heroFiles);
        $debug[] = "missis-* files in dir: " . count($missisFiles);
        $debug[] = "blog-* files in dir: " . count($blogFiles);
        if (!empty($heroFiles)) {
            $sample = array_slice(array_values($heroFiles), 0, 3);
            foreach ($sample as $s) {
                $full = $mediaDir . '/' . $s;
                $debug[] = "  $s -> exists:" . (file_exists($full) ? 'Y' : 'N') . " readable:" . (is_readable($full) ? 'Y' : 'N') . " size:" . filesize($full);
            }
        }
    } else {
        $debug[] = "scandir FAILED";
    }

    foreach ($allFiles as $file) {
        $before = filesize($file);
        $name = basename($file);
        $result = optimizeImage($file);
        $after = filesize($file);
        if ($result) {
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
        <div class="card">
            <h2><i class="fa-solid fa-folder-open"></i> Visi faili mapē /media/</h2>
            <p style="color:var(--text-muted); margin-bottom:16px">Šie ir visi attēlu faili, kas atrodami serverī. Ja šeit nav `hero-` vai `missis-` failu, tie nav serverī (iespējams, dzēsti pie "Reset All").</p>
            <?php if (empty($allFiles)): ?>
                <p style="color:#dc3545">Nav atrasts neviens attēls mapē /media/.</p>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse; margin-bottom:24px">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border)">Faila nosaukums</th>
                            <th style="text-align:right; padding:8px; border-bottom:2px solid var(--border)">Lielums</th>
                            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border)">Tips</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalSize = 0;
                        foreach ($allFiles as $f):
                            $size = filesize($f);
                            $totalSize += $size;
                            $name = basename($f);
                            $ext = strtoupper(pathinfo($f, PATHINFO_EXTENSION));
                            $prefix = '';
                            if (strpos($name, 'hero-') === 0) $prefix = '<span style="color:#007bff">HERO</span> ';
                            elseif (strpos($name, 'missis-') === 0) $prefix = '<span style="color:#e83e8c">MISSIS</span> ';
                            elseif (strpos($name, 'blog-feat-') === 0) $prefix = '<span style="color:#28a745">BLOG FEAT</span> ';
                            elseif (strpos($name, 'blog-img-') === 0) $prefix = '<span style="color:#17a2b8">BLOG IMG</span> ';
                            elseif (strpos($name, 'lueta-') === 0) $prefix = '<span style="color:var(--text-muted)">FALLBACK</span> ';
                        ?>
                        <tr>
                            <td style="padding:6px 8px; border-bottom:1px solid var(--border); font-size:13px; font-family:monospace"><?= $prefix . htmlspecialchars($name) ?></td>
                            <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px"><?= formatSize($size) ?></td>
                            <td style="padding:6px 8px; border-bottom:1px solid var(--border); font-size:13px"><?= $ext ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td style="padding:8px; border-top:2px solid var(--border); font-weight:600">Kopā: <?= count($allFiles) ?> faili</td>
                            <td style="padding:8px; border-top:2px solid var(--border); text-align:right; font-weight:600"><?= formatSize($totalSize) ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php
        $supabase = getSupabase();
        $dbPaths = array();
        foreach (array('hero_images', 'missis_images') as $bk) {
            $rows = $supabase->select('content_blocks', array('page' => 'eq.index', 'block_key' => 'eq.' . $bk, 'select' => 'block_value'));
            if ($rows && !isset($rows['error']) && count($rows) > 0) {
                $decoded = json_decode($rows[0]['block_value'], true);
                if (is_array($decoded)) $dbPaths = array_merge($dbPaths, $decoded);
            }
        }
        ?>
        <div class="card" style="margin-top:24px">
            <h2><i class="fa-solid fa-database"></i> DB attēlu ceļi vs failsistēma</h2>
            <p style="color:var(--text-muted); margin-bottom:16px">Attēli, kas reģistrēti Supabase datubāzē, un vai tie eksistē servera /media/ mapē.</p>
            <?php if (empty($dbPaths)): ?>
                <p style="color:var(--text-muted)">Nav atrasti nekādi attēlu ceļi datubāzē.</p>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px; border-bottom:2px solid var(--border)">Ceļš (DB)</th>
                            <th style="text-align:center; padding:8px; border-bottom:2px solid var(--border)">Eksistē?</th>
                            <th style="text-align:right; padding:8px; border-bottom:2px solid var(--border)">Lielums</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $dbMissing = 0;
                        $dbFound = 0;
                        foreach ($dbPaths as $p):
                            $fullPath = __DIR__ . '/../' . $p;
                            $exists = file_exists($fullPath);
                            if ($exists) { $dbFound++; } else { $dbMissing++; }
                        ?>
                        <tr>
                            <td style="padding:6px 8px; border-bottom:1px solid var(--border); font-size:13px; font-family:monospace"><?= htmlspecialchars($p) ?></td>
                            <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:center; font-size:13px">
                                <?php if ($exists): ?>
                                    <span style="color:#28a745"><i class="fa-solid fa-check"></i></span>
                                <?php else: ?>
                                    <span style="color:#dc3545"><i class="fa-solid fa-xmark"></i></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:6px 8px; border-bottom:1px solid var(--border); text-align:right; font-size:13px"><?= $exists ? formatSize(filesize($fullPath)) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" style="padding:8px; border-top:2px solid var(--border); font-size:13px">
                                <strong>Kopā:</strong> <?= count($dbPaths) ?> ceļi |
                                <span style="color:#28a745"><?= $dbFound ?> eksistē</span> |
                                <span style="color:#dc3545"><?= $dbMissing ?> trūkst</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top:24px">
            <h2><i class="fa-solid fa-bolt"></i> Attēlu optimizēšana</h2>
            <p style="color:var(--text-muted); margin-bottom:20px">Samazina attēlu izmērus un pārvērš uz WebP formātu.</p>
            <?php if (!extension_loaded('gd')): ?>
                <div style="padding:16px; background:#fff3cd; border-radius:var(--radius); color:#856404; margin-bottom:20px">
                    <i class="fa-solid fa-triangle-exclamation"></i> PHP GD nav pieejams. Optimizēšana nav iespējama.
                </div>
            <?php else: ?>
                <form method="post" style="margin-bottom:24px" onsubmit="document.getElementById('optimizeBtn').disabled=true; document.getElementById('optimizeBtn').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Optimizē...';">
                    <input type="hidden" name="optimize_all" value="1">
                    <button type="submit" id="optimizeBtn" class="btn btn-primary">
                        <i class="fa-solid fa-bolt"></i> Optimizēt visus attēlus
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!empty($debug)): ?>
                <div style="margin-bottom:20px; padding:12px; background:#1a1a1a; border-radius:var(--radius); font-family:monospace; font-size:12px; white-space:pre-wrap; color:#0f0; line-height:1.6">
                    <?= htmlspecialchars(implode("\n", $debug)) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($results)): ?>
                <div style="margin-bottom:16px; padding:12px; background:var(--bg2); border-radius:var(--radius); display:flex; gap:24px">
                    <span><strong><?= $optimized ?></strong> optimizēti</span>
                    <span><strong><?= $skipped ?></strong> nav nepieciešami</span>
                    <span><strong><?= $failed ?></strong> kļūdas</span>
                </div>
                <table style="width:100%; border-collapse:collapse">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid var(--border)">Faila nosaukums</th>
                            <th style="text-align:right; padding:8px; border-bottom:1px solid var(--border)">Pirms</th>
                            <th style="text-align:right; padding:8px; border-bottom:1px solid var(--border)">Pēc</th>
                            <th style="text-align:right; padding:8px; border-bottom:1px solid var(--border)">Statuss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td style="padding:8px; border-bottom:1px solid var(--border); font-size:14px"><?= htmlspecialchars($r['name']) ?></td>
                                <td style="padding:8px; border-bottom:1px solid var(--border); text-align:right; font-size:14px"><?= formatSize($r['before']) ?></td>
                                <td style="padding:8px; border-bottom:1px solid var(--border); text-align:right; font-size:14px"><?= formatSize($r['after']) ?></td>
                                <td style="padding:8px; border-bottom:1px solid var(--border); text-align:right; font-size:14px">
                                    <?php if ($r['status'] === 'ok'): ?>
                                        <span style="color:#28a745"><i class="fa-solid fa-check"></i> <?= round((1 - $r['after'] / $r['before']) * 100) ?>% smaller</span>
                                    <?php elseif ($r['status'] === 'skip'): ?>
                                        <span style="color:var(--text-muted)"><i class="fa-solid fa-minus"></i> Nav nepieciešams</span>
                                    <?php else: ?>
                                        <span style="color:#dc3545"><i class="fa-solid fa-xmark"></i> Kļūda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
