<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$mediaDir = __DIR__ . '/../media';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_recovered') {
    header('Content-Type: application/json');
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'))) {
            $section = $_POST['section'] ?? 'hero';
            $filename = $section . '-' . time() . '-' . substr(md5(mt_rand()), 0, 8) . '.' . $ext;
            $dest = $mediaDir . '/' . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $optimized = optimizeImage($dest);
                if ($optimized && $optimized !== $dest) { $filename = basename($optimized); }
                echo json_encode(array('ok' => true, 'path' => 'media/' . $filename));
                exit;
            }
        }
    }
    echo json_encode(array('ok' => false));
    exit;
}

$supabase = getSupabase();
$missing = array();
foreach (array('hero_images', 'missis_images') as $bk) {
    $rows = $supabase->select('content_blocks', array('page' => 'eq.index', 'block_key' => 'eq.' . $bk, 'select' => 'block_value'));
    if ($rows && !isset($rows['error']) && count($rows) > 0) {
        $decoded = json_decode($rows[0]['block_value'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $p) {
                $fp = $mediaDir . '/' . basename($p);
                if (!file_exists($fp) || filesize($fp) < 100) {
                    $section = strpos($p, 'hero-') === 0 ? 'hero' : 'missis';
                    $missing[] = array('path' => $p, 'section' => $section);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attēlu atgūšana - Lueta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <style>
        .recovery-log { background:#1a1a1a; color:#0f0; padding:16px; border-radius:var(--radius); font-family:monospace; font-size:12px; max-height:400px; overflow-y:auto; white-space:pre-wrap; line-height:1.6; }
        .progress-bar { height:6px; background:var(--border); border-radius:3px; overflow:hidden; margin:12px 0; }
        .progress-fill { height:100%; background:var(--accent); width:0%; transition:width 0.3s; }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="header-left">
            <a href="index.php" class="logo">Lueta<span>.</span></a>
            <h1>Attēlu atgūšana</h1>
        </div>
        <a href="optimize-images.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Atpakaļ</a>
    </header>
    <main class="page-content" style="padding:var(--page-padding)">
        <div class="card">
            <h2><i class="fa-solid fa-download"></i> Atgūt trūkstošos attēlus</h2>
            <p style="color:var(--text-muted); margin-bottom:16px">
                Šī lapa izmanto jūsu pārlūku, lai lejupielādētu attēlus no CDN kešatmiņas un augšupielādētu tos atpakaļ serverī.
                <br><strong>Darbojas tikai kamēr attēli vēl ir pārlūka/CDN kešatmiņā.</strong>
            </p>

            <?php if (empty($missing)): ?>
                <div style="padding:16px; background:#d4edda; border-radius:var(--radius); color:#155724">
                    <i class="fa-solid fa-check"></i> Visi attēli jau eksistē serverī.
                </div>
            <?php else: ?>
                <p style="margin-bottom:16px">Trūkstošie attēli: <strong><?= count($missing) ?></strong></p>
                <button id="recoverBtn" class="btn btn-primary" onclick="startRecovery()">
                    <i class="fa-solid fa-download"></i> Atgūt visus (<?= count($missing) ?>)
                </button>
                <div class="progress-bar" id="progressBar" style="display:none"><div class="progress-fill" id="progressFill"></div></div>
                <div id="log" class="recovery-log" style="display:none; margin-top:16px"></div>
            <?php endif; ?>
        </div>
    </main>
    <script>
    var missing = <?= json_encode($missing) ?>;
    var recovered = 0;
    var failed = 0;

    function log(msg, color) {
        var el = document.getElementById('log');
        el.style.display = 'block';
        var line = document.createElement('div');
        line.style.color = color || '#0f0';
        line.textContent = msg;
        el.appendChild(line);
        el.scrollTop = el.scrollHeight;
    }

    function updateProgress(done, total) {
        var pct = Math.round((done / total) * 100);
        document.getElementById('progressFill').style.width = pct + '%';
    }

    function startRecovery() {
        document.getElementById('recoverBtn').disabled = true;
        document.getElementById('recoverBtn').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Atgūst...';
        document.getElementById('progressBar').style.display = 'block';
        log('Sākt atgūšanu... ' + missing.length + ' attēli');

        var chain = Promise.resolve();
        missing.forEach(function(item, idx) {
            chain = chain.then(function() {
                return recoverImage(item, idx + 1, missing.length);
            });
        });
        chain.then(function() {
            log('');
            log('Gatavs! Atgūti: ' + recovered + ', Kļūdas: ' + failed, '#0f0');
            document.getElementById('recoverBtn').innerHTML = '<i class="fa-solid fa-check"></i> Pabeigts';
        });
    }

    function recoverImage(item, current, total) {
        var url = '/media/' + item.path.replace('media/', '');
        log('[' + current + '/' + total + '] ' + item.path + ' ...', '#888');

        return fetch(url)
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.blob();
            })
            .then(function(blob) {
                if (blob.size < 100) throw new Error('Too small: ' + blob.size + ' bytes');
                var fd = new FormData();
                fd.append('action', 'upload_recovered');
                fd.append('section', item.section);
                fd.append('file', blob, item.path.split('/').pop());
                return fetch('recover-images.php', { method: 'POST', body: fd });
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.ok) {
                    recovered++;
                    log('  ✓ ' + d.path, '#28a745');
                } else {
                    failed++;
                    log('  ✗ Upload failed', '#dc3545');
                }
                updateProgress(current, total);
            })
            .catch(function(e) {
                failed++;
                log('  ✗ ' + e.message, '#dc3545');
                updateProgress(current, total);
            });
    }
    </script>
</body>
</html>
