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

$items = $supabase->select('testimonials', array('page' => 'eq.' . $page, 'select' => 'id,text,author_name,author_role,display_order', 'order' => 'display_order.asc'));
if (!is_array($items)) $items = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'save') {
        $ids = $_POST['id'] ?? array();
        $texts = $_POST['text'] ?? array();
        $authors = $_POST['author_name'] ?? array();
        $roles = $_POST['author_role'] ?? array();
        foreach ($ids as $i => $id) {
            $txt = trim($texts[$i] ?? '');
            $au = trim($authors[$i] ?? '');
            $ro = trim($roles[$i] ?? '');
            if ($id && $txt) {
                $supabase->update('testimonials', array('text' => $txt, 'author_name' => $au, 'author_role' => $ro), array('id' => 'eq.' . $id));
            }
        }
        $saved = true;
    } elseif ($action === 'add') {
        $txt = trim($_POST['text'] ?? '');
        $au = trim($_POST['author_name'] ?? '');
        $ro = trim($_POST['author_role'] ?? '');
        if ($txt) {
            $maxOrder = 0;
            foreach ($items as $it) {
                if ($it['display_order'] > $maxOrder) $maxOrder = $it['display_order'];
            }
            $supabase->insert('testimonials', array('page' => $page, 'text' => $txt, 'author_name' => $au, 'author_role' => $ro, 'display_order' => $maxOrder + 1));
            $saved = true;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $supabase->delete('testimonials', array('id' => 'eq.' . $id));
            $saved = true;
        }
    }
    $items = $supabase->select('testimonials', array('page' => 'eq.' . $page, 'select' => 'id,text,author_name,author_role,display_order', 'order' => 'display_order.asc'));
    if (!is_array($items)) $items = array();
}

$page_title = ($lang === 'en') ? 'Testimonials (EN)' : 'Atsauksmes (LV)';
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
            <div id="sortable-testimonials">
            <?php foreach ($items as $i => $item): ?>
                <div class="card" data-id="<?= $item['id'] ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <h2 style="margin:0"><?= htmlspecialchars($item['author_name'] ?: 'Atsauksme #' . ($i + 1)) ?></h2>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem('<?= $item['id'] ?>', '<?= $lang ?>')"><i class="fa-solid fa-trash"></i></button>
                    </div>
                    <input type="hidden" name="id[]" value="<?= $item['id'] ?>">
                    <div class="form-group">
                        <label>Atsauksmes teksts</label>
                        <textarea name="text[]" rows="4"><?= htmlspecialchars($item['text']) ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Autors</label>
                            <input type="text" name="author_name[]" value="<?= htmlspecialchars($item['author_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Amats</label>
                            <input type="text" name="author_role[]" value="<?= htmlspecialchars($item['author_role']) ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if (empty($items)): ?>
                <div class="card"><p class="text-muted">Nav atsauksmju. Vispirms izveidojiet tās datu bāzē.</p></div>
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
                    <label>Jauna atsauksme - Teksts</label>
                    <textarea name="text" rows="3" placeholder="Atsauksmes teksts" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Autors</label>
                        <input type="text" name="author_name" placeholder="Vārds Uzvārds">
                    </div>
                    <div class="form-group">
                        <label>Amats</label>
                        <input type="text" name="author_role" placeholder="Amats">
                    </div>
                </div>
                <div><button type="submit" class="btn btn-outline"><i class="fa-solid fa-plus"></i> Pievienot atsauksmi</button></div>
            </form>
        </div>
    </main>
</div>
<script src="js/admin.js?v=<?= filemtime(__DIR__ . '/js/admin.js') ?>"></script>
<script>
function deleteItem(id, lang) {
    if (!confirm('Dzēst šo atsauksmi?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('lang', lang);
    fetch('testimonials.php', { method: 'POST', body: fd })
        .then(function() { location.reload(); });
}
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('sortable-testimonials');
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
                fd.append('table', 'testimonials');
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
