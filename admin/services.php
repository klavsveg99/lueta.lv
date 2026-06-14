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

$services = $supabase->select('services', array('page' => 'eq.' . $page, 'select' => 'id,title,description,display_order', 'order' => 'display_order.asc'));
if (!is_array($services)) $services = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'save') {
        $ids = $_POST['id'] ?? array();
        $titles = $_POST['title'] ?? array();
        $texts = $_POST['description'] ?? array();
        foreach ($ids as $i => $id) {
            $t = trim($titles[$i] ?? '');
            $txt = trim($texts[$i] ?? '');
            if ($id && $t) {
                $supabase->update('services', array('title' => $t, 'description' => $txt), array('id' => 'eq.' . $id));
            }
        }
        $saved = true;
    } elseif ($action === 'add') {
        $t = trim($_POST['title'] ?? '');
        $txt = trim($_POST['description'] ?? '');
        if ($t) {
            $maxOrder = 0;
            foreach ($services as $svc) {
                if ($svc['display_order'] > $maxOrder) $maxOrder = $svc['display_order'];
            }
            $supabase->insert('services', array('page' => $page, 'title' => $t, 'description' => $txt, 'display_order' => $maxOrder + 1));
            $saved = true;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $supabase->delete('services', array('id' => 'eq.' . $id));
            $saved = true;
        }
    }
    $services = $supabase->select('services', array('page' => 'eq.' . $page, 'select' => 'id,title,description,display_order', 'order' => 'display_order.asc'));
    if (!is_array($services)) $services = array();
}

$page_title = ($lang === 'en') ? 'Services (EN)' : 'Pakalpojumi (LV)';
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
            <a href="?lang=lv<?= isset($_GET['lang']) ? '' : '' ?>" class="tab <?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
            <a href="?lang=en" class="tab <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
        <form method="POST" class="content-form">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <div id="sortable-services">
            <?php foreach ($services as $i => $svc): ?>
                <div class="card" data-id="<?= $svc['id'] ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <h2 style="margin:0"><?= htmlspecialchars($svc['title'] ?: 'Pakalpojums #' . ($i + 1)) ?></h2>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem('<?= $svc['id'] ?>', '<?= $lang ?>')"><i class="fa-solid fa-trash"></i></button>
                    </div>
                    <input type="hidden" name="id[]" value="<?= $svc['id'] ?>">
                    <div class="form-group">
                        <label>Nosaukums</label>
                        <input type="text" name="title[]" value="<?= htmlspecialchars($svc['title']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Apraksts</label>
                        <textarea name="description[]" rows="3"><?= htmlspecialchars($svc['description']) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if (empty($services)): ?>
                <div class="card"><p class="text-muted">Nav pakalpojumu. Vispirms izveidojiet tos datu bāzē.</p></div>
            <?php endif; ?>
            <div class="form-actions page-content" style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Saglabāt visu</button>
            </div>
        </form>
        <div class="inline-form" style="margin:0 var(--page-padding) 16px">
            <form method="POST" style="display:flex;flex-direction:column;gap:12px">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Jauns pakalpojums - Nosaukums</label>
                    <input type="text" name="title" placeholder="Pakalpojuma nosaukums" required>
                </div>
                <div class="form-group">
                    <label>Apraksts</label>
                    <textarea name="description" rows="3" placeholder="Pakalpojuma apraksts"></textarea>
                </div>
                <div><button type="submit" class="btn btn-outline"><i class="fa-solid fa-plus"></i> Pievienot pakalpojumu</button></div>
            </form>
        </div>
    </main>
</div>
<script src="js/admin.js?v=<?= filemtime(__DIR__ . '/js/admin.js') ?>"></script>
<script>
function deleteItem(id, lang) {
    if (!confirm('Dzēst šo pakalpojumu?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('lang', lang);
    fetch('services.php', { method: 'POST', body: fd })
        .then(function() { location.reload(); });
}
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('sortable-services');
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
                fd.append('table', 'services');
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
