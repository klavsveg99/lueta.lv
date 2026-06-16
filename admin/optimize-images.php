<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$mediaDir = __DIR__ . '/../media';
$results = array();
$optimized = 0;
$skipped = 0;
$failed = 0;

if (isset($_POST['optimize_all']) && is_dir($mediaDir)) {
    $files = glob($mediaDir . '/{*.jpg,*.jpeg,*.png,*.webp}', GLOB_BRACE);
    foreach ($files as $file) {
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

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
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
            <h2><i class="fa-solid fa-bolt"></i> Attēlu optimizēšana</h2>
            <p style="color:var(--text-muted); margin-bottom:20px">Samazina attēlu izmērus, pārvēršot uz WebP formātu. Attēli, kas jau ir mazāki par 1920px platumu, tiks pārvērsti tikai uz WebP bez mērogošanas.</p>
            <?php if (!extension_loaded('gd')): ?>
                <div style="padding:16px; background:#fff3cd; border-radius:var(--radius); color:#856404; margin-bottom:20px">
                    <i class="fa-solid fa-triangle-exclamation"></i> PHP GD nav pieejams. Optimizēšana nav iespējama.
                </div>
            <?php else: ?>
                <form method="post" style="margin-bottom:24px">
                    <button type="submit" name="optimize_all" value="1" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Optimizē...'; this.form.submit();">
                        <i class="fa-solid fa-bolt"></i> Optimizēt visus attēlus
                    </button>
                </form>
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
            <?php elseif (!isset($_POST['optimize_all'])): ?>
                <div style="padding:24px; text-align:center; color:var(--text-muted)">
                    <i class="fa-solid fa-images" style="font-size:32px; margin-bottom:12px; display:block"></i>
                    Nospiediet "Optimizēt visus attēlus", lai sāktu.
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
