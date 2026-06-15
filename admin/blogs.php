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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $content = $_POST['content'] ?? '';
        $published_at = $_POST['published_at'] ?? date('Y-m-d');
        $featured_image = $_POST['featured_image'] ?? '';

        if ($id) {
            $data = array('title' => $title, 'description' => $description, 'content' => $content, 'published_at' => $published_at);
            if ($featured_image) $data['featured_image'] = $featured_image;
            $supabase->update('blogs', $data, array('id' => 'eq.' . $id));
        } else {
            $supabase->insert('blogs', array(
                'page' => $page,
                'title' => $title,
                'description' => $description,
                'content' => $content,
                'published_at' => $published_at,
                'featured_image' => $featured_image,
                'display_order' => 0
            ));
        }
        $saved = true;
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) $supabase->delete('blogs', array('id' => 'eq.' . $id));
        $saved = true;
    } elseif ($action === 'upload_featured') {
        header('Content-Type: application/json');
        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif')) && $file['error'] === UPLOAD_ERR_OK) {
                $filename = 'blog-feat-' . time() . '-' . substr(md5(mt_rand()), 0, 8) . '.' . $ext;
                $dest = __DIR__ . '/../media/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    echo json_encode(array('location' => 'media/' . $filename));
                    exit;
                }
            }
        }
        http_response_code(400);
        echo json_encode(array('error' => 'Upload failed'));
        exit;
    } elseif ($action === 'upload_image') {
        header('Content-Type: application/json');
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif')) && $file['error'] === UPLOAD_ERR_OK) {
                $filename = 'blog-content-' . time() . '-' . substr(md5(mt_rand()), 0, 8) . '.' . $ext;
                $dest = __DIR__ . '/../media/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    echo json_encode(array('url' => '/' . 'media/' . $filename));
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
if (!is_array($blogs)) $blogs = array();

$page_title = ($lang === 'en') ? 'Blog (EN)' : 'Blogi (LV)';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
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

        <div class="page-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
                <h2>Raksti</h2>
                <button class="btn btn-primary" id="addBlogBtn"><i class="fa-solid fa-plus"></i> Pievienot rakstu</button>
            </div>

            <div class="blog-list">
                <?php foreach ($blogs as $blog): ?>
                    <div class="card" style="display:flex;gap:16px;padding:16px;align-items:center">
                        <?php if (!empty($blog['featured_image'])): ?>
                            <img src="/<?= htmlspecialchars($blog['featured_image']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius)">
                        <?php else: ?>
                            <div style="width:80px;height:80px;border-radius:var(--radius);background:var(--bg3);display:flex;align-items:center;justify-content:center;color:var(--text-muted)"><i class="fa-solid fa-image"></i></div>
                        <?php endif; ?>
                        <div style="flex:1">
                            <h3 style="margin:0"><?= htmlspecialchars($blog['title']) ?></h3>
                            <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Publicēts: <?= htmlspecialchars($blog['published_at']) ?></div>
                        </div>
                        <div style="display:flex;gap:8px">
                            <button class="btn btn-outline btn-sm edit-blog-btn" data-id="<?= htmlspecialchars($blog['id']) ?>" data-title="<?= htmlspecialchars($blog['title']) ?>" data-description="<?= htmlspecialchars($blog['description'] ?? '') ?>" data-content='<?= htmlspecialchars($blog['content'] ?? '', ENT_QUOTES) ?>' data-published_at="<?= htmlspecialchars($blog['published_at']) ?>" data-featured_image="<?= htmlspecialchars($blog['featured_image'] ?? '') ?>">Rediģēt</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Dzēst šo rakstu?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($blogs)): ?>
                    <div class="card"><p class="text-muted">Nav rakstu. Pievienojiet pirmo!</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<div class="modal-overlay" id="blogModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center">
    <div class="modal-card" style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:24px;max-width:800px;width:90%;max-height:90vh;overflow-y:auto">
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
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="file" id="featuredFile" accept="image/*" style="display:none">
                        <button type="button" class="btn btn-outline btn-sm" id="featuredFileBtn"><i class="fa-solid fa-image"></i> Izvēlēties</button>
                        <input type="text" name="featured_image" id="featuredImage" readonly placeholder="Nav izvēlēts" style="flex:1">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Saturs</label>
                <div id="quillEditor" style="min-height:200px;background:#fff;border:1px solid var(--border);border-radius:var(--radius)"></div>
            </div>
            <input type="hidden" name="content" id="blogContent">

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:16px">
                <button type="button" class="btn btn-outline" id="closeModal">Atcelt</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Saglabāt</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#quillEditor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

var quillImageHandler = function() {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    input.onchange = function() {
        var file = input.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('action', 'upload_image');
        fd.append('file', file);
        fetch('blogs.php', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.url) {
                    var range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', data.url);
                }
            });
    };
};
quill.getModule('toolbar').addHandler('image', quillImageHandler);

document.getElementById('addBlogBtn').addEventListener('click', function() {
    document.getElementById('blogId').value = '';
    document.getElementById('blogTitle').value = '';
    document.getElementById('blogDesc').value = '';
    document.getElementById('blogDate').value = '<?= date('Y-m-d') ?>';
    document.getElementById('featuredImage').value = '';
    quill.setContents([]);
    document.getElementById('modalTitle').innerText = 'Pievienot rakstu';
    document.getElementById('blogModal').style.display = 'flex';
});

document.querySelectorAll('.edit-blog-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('blogId').value = this.dataset.id;
        document.getElementById('blogTitle').value = this.dataset.title;
        document.getElementById('blogDesc').value = this.dataset.description;
        document.getElementById('blogDate').value = this.dataset.published_at;
        document.getElementById('featuredImage').value = this.dataset.featured_image;
        quill.root.innerHTML = this.dataset.content || '';
        document.getElementById('modalTitle').innerText = 'Rediģēt rakstu';
        document.getElementById('blogModal').style.display = 'flex';
    });
});

document.getElementById('blogForm').addEventListener('submit', function() {
    document.getElementById('blogContent').value = quill.root.innerHTML;
});

document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('blogModal').style.display = 'none';
});
document.getElementById('blogModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});

document.getElementById('featuredFileBtn').addEventListener('click', function() {
    document.getElementById('featuredFile').click();
});
document.getElementById('featuredFile').addEventListener('change', function() {
    if (!this.files.length) return;
    var fd = new FormData();
    fd.append('action', 'upload_featured');
    fd.append('image', this.files[0]);
    fetch('blogs.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) { if (data.location) document.getElementById('featuredImage').value = data.location; })
        .catch(function() { alert('Kļūda augšupielādējot attēlu'); });
});

document.getElementById('sidebarToggle').addEventListener('click', function() { document.getElementById('adminSidebar').classList.add('open'); });
document.getElementById('sidebarClose').addEventListener('click', function() { document.getElementById('adminSidebar').classList.remove('open'); });
</script>
</body>
</html>
