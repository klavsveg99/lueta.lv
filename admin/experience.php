<?php
session_start();
require_once 'inc/auth.php';
require_once 'inc/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$supabase = getSupabase();
$lang = $_GET['lang'] ?? $_POST['lang'] ?? 'lv';
if (!in_array($lang, ['lv', 'en'])) $lang = 'lv';
$page = ($lang === 'en') ? 'en' : 'index';
$saved = false;

$items = $supabase->select('experiences', array('page' => 'eq.' . $page, 'select' => 'id,icon,title,description,display_order', 'order' => 'display_order.asc'));
if (!is_array($items)) $items = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'save') {
        $ids = $_POST['id'] ?? array();
        $icons_post = $_POST['icon'] ?? array();
        $titles = $_POST['title'] ?? array();
        $texts = $_POST['description'] ?? array();
        foreach ($ids as $i => $id) {
            $ic = trim($icons_post[$i] ?? '');
            $t = trim($titles[$i] ?? '');
            $txt = trim($texts[$i] ?? '');
            if ($id && $t) {
                $supabase->update('experiences', array('icon' => $ic, 'title' => $t, 'description' => $txt), array('id' => 'eq.' . $id));
            }
        }
        $saved = true;
    } elseif ($action === 'add') {
        $ic = trim($_POST['icon'] ?? '');
        $t = trim($_POST['title'] ?? '');
        $txt = trim($_POST['description'] ?? '');
        if ($t) {
            $maxOrder = 0;
            foreach ($items as $it) {
                if ($it['display_order'] > $maxOrder) $maxOrder = $it['display_order'];
            }
            $supabase->insert('experiences', array('page' => $page, 'icon' => $ic ?: 'fa-solid fa-layer-group', 'title' => $t, 'description' => $txt, 'display_order' => $maxOrder + 1));
            $saved = true;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $supabase->delete('experiences', array('id' => 'eq.' . $id));
            $saved = true;
        }
    }
}

$icons = array(
    'fa-solid fa-layer-group' => 'Layers',
    'fa-solid fa-microphone-lines' => 'Mic',
    'fa-solid fa-rocket' => 'Rocket',
    'fa-solid fa-bullhorn' => 'Bullhorn',
    'fa-solid fa-code-branch' => 'Branch',
    'fa-solid fa-bolt' => 'Bolt',
    'fa-solid fa-chart-line' => 'Chart',
    'fa-solid fa-handshake' => 'Handshake',
    'fa-solid fa-lightbulb' => 'Lightbulb',
    'fa-solid fa-compass' => 'Compass',
    'fa-solid fa-pen-nib' => 'Pen',
    'fa-solid fa-palette' => 'Palette',
    'fa-solid fa-camera' => 'Camera',
    'fa-solid fa-briefcase' => 'Briefcase',
    'fa-solid fa-users' => 'Users',
);

$page_title = ($lang === 'en') ? 'Experience (EN)' : 'Pieredze (LV)';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
<div class="admin-layout">
    <?php include 'inc/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <div>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                <h1><?= $page_title ?></h1>
            </div>
        </div>

        <?php if ($saved): ?>
            <div class="page-content"><div class="msg msg-success">Saglabāts veiksmīgi!</div></div>
        <?php endif; ?>

        <div class="lang-tabs">
            <a href="?lang=lv" class="tab <?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
            <a href="?lang=en" class="tab <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
        <form method="POST" class="content-form">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <input type="hidden" name="action" value="save">
            <div id="sortable-experience">
            <?php foreach ($items as $i => $item): ?>
                <div class="card" data-id="<?= $item['id'] ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <h2 style="margin:0"><?= htmlspecialchars($item['title'] ?: 'Pieredze #' . ($i + 1)) ?></h2>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Dzēst šo pieredzi?')">
                            <input type="hidden" name="lang" value="<?= $lang ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                    <input type="hidden" name="id[]" value="<?= $item['id'] ?>">
    
                    <div class="form-group">
                        <label>Ikonas izvēle</label>
                        <input type="hidden" name="icon[]" class="icon-input" value="<?= $item['icon'] ?>">
                        <div class="icon-picker">
                            <?php foreach ($icons as $fa => $label): ?>
                                <div class="icon-option <?= $item['icon'] === $fa ? 'active' : '' ?>" data-icon="<?= $fa ?>">
                                    <i class="<?= $fa ?>"></i>
                                    <span><?= $label ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
    
                    <div class="form-group">
                        <label>Nosaukums</label>
                        <input type="text" name="title[]" value="<?= htmlspecialchars($item['title']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Apraksts</label>
                        <textarea name="description[]" rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if (empty($items)): ?>
                <div class="card"><p class="text-muted">Nav pieredzes ierakstu. Vispirms izveidojiet tos datu bāzē.</p></div>
            <?php endif; ?>
            <div class="form-actions" style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Saglabāt visu</button>
                <button type="submit" name="action" value="add" class="btn btn-outline"><i class="fa-solid fa-plus"></i> Pievienot pieredzi</button>
            </div>
        </form>
    </main>
</div>
<script src="js/admin.js?v=<?= filemtime(__DIR__ . '/js/admin.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('sortable-experience');
    if (el) {
        Sortable.create(el, {
            animation: 150,
            onEnd: function() {
                var ids = [];
                el.querySelectorAll('.card').forEach(function(card) {
                    ids.push(card.dataset.id);
                });
                var fd = new FormData();
                fd.append('action', 'update_order');
                fd.append('table', 'experiences');
                ids.forEach(function(id, idx) { fd.append('ids[' + idx + ']', id); });
                fetch('content.php', { method: 'POST', body: fd })
                    .then(function() { console.log('Order updated'); });
            }
        });
    }
});
</script>
</body>
</html>
