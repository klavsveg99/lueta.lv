<?php
session_start();
require_once 'inc/auth.php';
require_once 'inc/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$supabase = null;
$lang = 'lv';
$page = 'index';
$php_error = null;
$error = null;
$saved = false;
$blogs = array();

try {
    $supabase = getSupabase();
    $lang = $_GET['lang'] ?? $_POST['lang'] ?? 'lv';
    if (!in_array($lang, ['lv', 'en'])) $lang = 'lv';
    $page = ($lang === 'en') ? 'en' : 'index';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'save') {
            $id = $_POST['id'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $published_at = $_POST['published_at'] ?? date('Y-m-d');
            $featured_image = $_POST['featured_image'] ?? '';

            $data = array(
                'page' => $page, 'title' => $title, 'content' => $content,
                'description' => $description, 'published_at' => $published_at,
                'featured_image' => $featured_image
            );

            if ($id) {
                $supabase->update('blogs', $data, array('id' => 'eq.' . $id));
            } else {
                $data['display_order'] = 0;
                $supabase->insert('blogs', $data);
            }
            $saved = true;
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            if ($id) $supabase->delete('blogs', array('id' => 'eq.' . $id));
            $saved = true;
        } elseif ($action === 'upload_featured') {
            header('Content-Type: application/json');
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'))) {
                    $filename = 'blog-feat-' . time() . '-' . substr(md5(mt_rand()), 0, 8) . '.' . $ext;
                    $dest = __DIR__ . '/../media/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        echo json_encode(array('location' => 'media/' . $filename));
                        exit;
                    }
                }
            }
            http_response_code(400);
            echo json_encode(array('error' => 'Upload failed'));
            exit;
        }
    }

    $blogs = $supabase->select('blogs', array('page' => 'eq.' . $page, 'select' => '*', 'order' => 'published_at.desc'));
    if (!is_array($blogs)) {
        $blogs = array();
    } elseif (isset($blogs['error'])) {
        $php_error = $blogs['error'];
        $blogs = array();
    }
} catch (\Throwable $e) {
    $php_error = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
}

$page_title = ($lang === 'en') ? 'Jaunumi (EN)' : 'Jaunumi (LV)';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
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

        <?php if ($php_error): ?>
            <div class="page-content"><div class="msg msg-error" style="white-space:pre-wrap">PHP Error: <?= htmlspecialchars($php_error) ?></div></div>
        <?php endif; ?>
        <?php if ($saved): ?>
            <div class="page-content"><div class="msg msg-success">Saglabāts veiksmīgi!</div></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="page-content"><div class="msg msg-error">Kļūda: <?= htmlspecialchars($error) ?></div></div>
        <?php endif; ?>

        <div class="lang-tabs">
            <a href="?lang=lv" class="tab <?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
            <a href="?lang=en" class="tab <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>

        <div class="page-content">
            <button class="btn btn-primary" id="addBlogBtn" onclick="openBlogModal()" style="margin-bottom:24px"><i class="fa-solid fa-plus"></i> Pievienot rakstu</button>

            <?php if (count($blogs) > 0): ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="card" style="display:flex;gap:16px;padding:16px;align-items:center;margin-bottom:12px">
                        <?php if (!empty($blog['featured_image'])): ?>
                            <img src="/<?= htmlspecialchars($blog['featured_image']) ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius)">
                        <?php else: ?>
                            <div style="width:80px;height:80px;border-radius:var(--radius);background:var(--bg3);display:flex;align-items:center;justify-content:center;color:var(--text-muted)"><i class="fa-solid fa-image"></i></div>
                        <?php endif; ?>
                        <div style="flex:1">
                            <h3 style="margin:0"><?= htmlspecialchars($blog['title']) ?></h3>
                            <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Publicēts: <?= htmlspecialchars($blog['published_at']) ?></div>
                        </div>
                        <div style="display:flex;gap:8px">
                            <button class="btn btn-outline btn-sm editBlogBtn"
                                data-id="<?= htmlspecialchars($blog['id']) ?>"
                                data-title="<?= htmlspecialchars($blog['title']) ?>"
                                data-description="<?= htmlspecialchars($blog['description'] ?? '') ?>"
                                data-content="<?= htmlspecialchars($blog['content'] ?? '', ENT_QUOTES) ?>"
                                data-published_at="<?= htmlspecialchars($blog['published_at']) ?>"
                                data-featured_image="<?= htmlspecialchars($blog['featured_image'] ?? '') ?>">Rediģēt</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Dzēst šo rakstu?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<div id="blogModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:24px;max-width:800px;width:90%;max-height:90vh;overflow-y:auto">
        <h2 id="modalTitle">Pievienot rakstu</h2>
        <form method="POST" id="blogForm" style="display:flex;flex-direction:column;gap:16px">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="blogId">
            <input type="hidden" name="lang" value="<?= $lang ?>">

            <div class="form-group">
                <label>Virsraksts</label>
                <input type="text" name="title" id="blogTitle" required placeholder="Raksta virsraksts">
            </div>

            <div class="form-group">
                <label>Īss apraksts</label>
                <textarea name="description" id="blogDesc" rows="2" placeholder="Īss apraksts priekš kartiņas"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>Publicēšanas datums</label>
                    <input type="date" name="published_at" id="blogDate" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group" style="flex:1">
                    <label>Galvenais attēls</label>
                    <input type="file" id="featuredFile" accept="image/*" style="display:none">
                    <div style="display:flex;gap:8px;align-items:center">
                        <button type="button" class="btn btn-outline btn-sm" id="featuredFileBtn"><i class="fa-solid fa-image"></i> Izvēlēties</button>
                        <input type="text" name="featured_image" id="featuredImage" readonly placeholder="Nav izvēlēts" style="flex:1">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Saturs (HTML)</label>
                <textarea name="content" id="blogContent" rows="15" style="font-family:monospace;font-size:14px;line-height:1.5;resize:vertical" placeholder="Ievadiet saturu HTML formātā..."></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:16px">
                <button type="button" class="btn btn-outline" id="closeModalBtn" onclick="closeBlogModal()">Atcelt</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Saglabāt</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBlogModal() {
    var m = document.getElementById('blogModal');
    if (m) m.style.display = 'flex';
    document.getElementById('blogId').value = '';
    document.getElementById('blogTitle').value = '';
    document.getElementById('blogDesc').value = '';
    document.getElementById('blogContent').value = '';
    document.getElementById('blogDate').value = '<?= date('Y-m-d') ?>';
    document.getElementById('featuredImage').value = '';
    document.getElementById('modalTitle').innerText = 'Pievienot rakstu';
}
function closeBlogModal() {
    var m = document.getElementById('blogModal');
    if (m) m.style.display = 'none';
}
(function() {
    console.log('[Blogs] Script init');

    document.querySelectorAll('.editBlogBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('blogId').value = this.dataset.id;
            document.getElementById('blogTitle').value = this.dataset.title;
            document.getElementById('blogDesc').value = this.dataset.description;
            document.getElementById('blogDate').value = this.dataset.published_at;
            document.getElementById('featuredImage').value = this.dataset.featured_image;
            document.getElementById('blogContent').value = this.dataset.content || '';
            document.getElementById('modalTitle').innerText = 'Rediģēt rakstu';
            openBlogModal();
        });
    });

    var closeBtn = document.getElementById('closeModalBtn');
    if (closeBtn) closeBtn.addEventListener('click', closeBlogModal);

    var modal = document.getElementById('blogModal');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeBlogModal(); });

    var featBtn = document.getElementById('featuredFileBtn');
    var featFile = document.getElementById('featuredFile');
    if (featBtn && featFile) {
        featBtn.addEventListener('click', function() { featFile.click(); });
        featFile.addEventListener('change', function() {
            if (!this.files.length) return;
            var fd = new FormData();
            fd.append('action', 'upload_featured');
            fd.append('image', this.files[0]);
            fetch('blogs.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) { if (d.location) document.getElementById('featuredImage').value = d.location; })
                .catch(function() { alert('Kļūda augšupielādējot attēlu'); });
        });
    }

    var sbToggle = document.getElementById('sidebarToggle');
    var sbClose = document.getElementById('sidebarClose');
    if (sbToggle) sbToggle.addEventListener('click', function() { document.getElementById('adminSidebar').classList.add('open'); });
    if (sbClose) sbClose.addEventListener('click', function() { document.getElementById('adminSidebar').classList.remove('open'); });
})();
</script>
</body>
</html>
