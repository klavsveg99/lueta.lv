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
        $content = $_POST['content'] ?? '';
        $published_at = $_POST['published_at'] ?? date('Y-m-d');
        $featured_image = $_POST['featured_image'] ?? '';

        if ($id) {
            $data = array('title' => $title, 'content' => $content, 'published_at' => $published_at);
            if ($featured_image) $data['featured_image'] = $featured_image;
            $supabase->update('blogs', $data, array('id' => 'eq.' . $id));
        } else {
            $supabase->insert('blogs', array(
                'page' => $page,
                'title' => $title,
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
        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'))) {
                $filename = 'blog-feat-' . time() . '-' . substr(md5(mt_rand()), 0, 8) . '.' . $ext;
                $dest = __DIR__ . '/../media/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    echo 'media/' . $filename;
                    exit;
                }
            }
        }
        http_response_code(400);
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
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                        <img src="/<?= htmlspecialchars($blog['featured_image'] ?? 'media/placeholder.jpg') ?>" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius)">
                        <div style="flex:1">
                            <h3 style="margin:0"><?= htmlspecialchars($blog['title']) ?></h3>
                            <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Publicēts: <?= htmlspecialchars($blog['published_at']) ?></div>
                        </div>
                        <div style="display:flex;gap:8px">
                            <button class="btn btn-outline btn-sm" onclick="editBlog(<?= htmlspecialchars(json_encode($blog)) ?>)">Rediģēt</button>
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
        <form method="POST" style="display:flex;flex-direction:column;gap:16px">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="blogId">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            
            <div class="form-group">
                <label>Virsraksts</label>
                <input type="text" name="title" id="blogTitle" required placeholder="Raksta virsraksts">
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
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('featuredFile').click()">// <i class="fa-solid fa-image"></i> Izvēlēties</button>
                        <input type="text" name="featured_image" id="featuredImage" readonly placeholder="Neko izvēlēts" style="flex:1">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Saturs</label>
                <textarea name="content" id="blogContent"></textarea>
            </div>
            
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:16px">
                <button type="button" class="btn btn-outline" id="closeModal">Atcelt</button>
                <button type="submit" class="btn btn-primary">Saglabāt</button>
            </div>
        </form>
    </div>
</div>

<script>
tinymce.init({
    selector: '#blogContent',
    plugins: 'image link lists',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
    menubar: false,
    height: 400,
    branding: false,
    images_upload_url: 'blogs.php', // We handle the upload in the same file
});

document.getElementById('addBlogBtn').addEventListener('click', function() {
    document.getElementById('blogId').value = '';
    document.getElementById('blogTitle').value = '';
    document.getElementById('blogDate').value = '<?= date('Y-m-d') ?>';
    document.getElementById('featuredImage').value = '';
    tinymce.get('blogContent').setContent('');
    document.getElementById('modalTitle').innerText = 'Pievienot rakstu';
    document.getElementById('blogModal').style.display = 'flex';
});

function editBlog(blog) {
    document.getElementById('blogId').value = blog.id;
    document.getElementById('blogTitle').value = blog.title;
    document.getElementById('blogDate').value = blog.published_at;
    document.getElementById('featuredImage').value = blog.featured_image;
    tinymce.get('blogContent').setContent(blog.content);
    document.getElementById('modalTitle').innerText = 'Rediģēt rakstu';
    document.getElementById('blogModal').style.display = 'flex';
}

document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('blogModal').style.display = 'none';
});

document.getElementById('featuredFile').addEventListener('change', function() {
    if (!this.files.length) return;
    var fd = new FormData();
    fd.append('action', 'upload_featured');
    fd.append('image', this.files[0]);
    fetch('blogs.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(path => {
            document.getElementById('featuredImage').value = path;
        })
        .catch(err => alert('Kļūda augšupielādējot attēlu'));
});

document.getElementById('sidebarToggle').addEventListener('click', () => document.getElementById('adminSidebar').classList.add('open'));
document.getElementById('sidebarClose').addEventListener('click', () => document.getElementById('adminSidebar').classList.remove('open'));
</script>
</body>
</html>
