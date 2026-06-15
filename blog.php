<?php
session_start();
$lang = $_GET['lang'] ?? 'lv';
if (!in_array($lang, ['lv', 'en'])) $lang = 'lv';
$page = ($lang === 'en') ? 'en' : 'index';

require_once 'admin/inc/config.php';
require_once 'admin/inc/functions.php';
try {
    $supabase = getSupabase();
} catch (\Throwable $e) {
    header('Location: index.html'); exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.html'); exit;
}

try {
    $post = $supabase->select('blogs', array('id' => 'eq.' . $id, 'select' => '*'));
} catch (\Throwable $e) {
    header('Location: index.html'); exit;
}

if (!$post || empty($post) || isset($post['error'])) {
    header('Location: index.html'); exit;
}
$post = $post[0];

$page_title = $post['title'];
$date_prefix = ($lang === 'en') ? 'Published' : 'Publicēts';
$back_text = ($lang === 'en') ? 'Back to home' : 'Atpakaļ uz sākumu';
$related_title = ($lang === 'en') ? 'Other posts' : 'Citi raksti';

try {
    $related = $supabase->select('blogs', array('page' => 'eq.' . $page, 'select' => 'id,title,featured_image,published_at', 'order' => 'published_at.desc'));
} catch (\Throwable $e) {
    $related = array();
}
if (!is_array($related) || isset($related['error'])) $related = array();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Lueta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
</head>
<body>
    <header id="header">
        <a href="<?= $lang === 'en' ? 'en.html' : 'index.html' ?>" class="logo">Lueta<span>.</span></a>
        <nav id="mainNav">
            <div class="nav-items-grid">
                <?php if ($lang === 'en'): ?>
                    <a href="en.html#about">About</a>
                    <a href="en.html#services">Services</a>
                    <a href="en.html#achievement">Achievement</a>
                    <a href="en.html#experience">Experience</a>
                    <a href="en.html#blog-preview" class="nav-link" id="navBlogLink">Blog</a>
                    <a href="en.html#testimonials">Testimonials</a>
                    <a href="en.html#contact">Contact</a>
                <?php else: ?>
                    <a href="index.html#par">Par</a>
                    <a href="index.html#pakalpojumi">Pakalpojumi</a>
                    <a href="index.html#achievement">Sasniegumi</a>
                    <a href="index.html#pieredze">Pieredze</a>
                    <a href="index.html#blog-preview" class="nav-link" id="navBlogLink">Jaunumi</a>
                    <a href="index.html#testimonials">Atsauksmes</a>
                    <a href="index.html#contact">Kontakti</a>
                <?php endif; ?>
            </div>
            <a href="<?= $lang === 'en' ? 'en.html#contact' : 'index.html#contact' ?>" class="header-cta contact-cta-btn"><?= $lang === 'en' ? 'Get in Touch' : 'Sazināties' ?></a>
            <div class="lang-switch">
                <a href="blog.php?id=<?= $id ?>&lang=lv" class="<?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
                <a href="blog.php?id=<?= $id ?>&lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
            <div class="nav-contact-info">
                <a href="tel:+37120230446" class="nav-contact-link">
                    <i class="fa-solid fa-phone"></i> +371 20230446
                </a>
                <a href="https://wa.me/+37120230446" target="_blank" rel="noopener" class="nav-contact-link nav-whatsapp-link">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </nav>
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="<?= $lang === 'en' ? 'Toggle menu' : 'Izvēlne' ?>">
            <span class="menu-text"><?= $lang === 'en' ? 'Menu' : 'Izvēlne' ?></span>
            <span class="hamburger-bar"></span>
        </button>
        <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    </header>

    <main class="page-content" style="padding:120px 20px 40px; max-width:840px; margin:0 auto">
        <a href="<?= $lang === 'en' ? 'en.html' : 'index.html' ?>" class="btn-outline" style="display:inline-block; margin-bottom:24px; text-decoration:none; text-align:center; width:fit-content"><i class="fa-solid fa-arrow-left"></i> <?= $back_text ?></a>
        
        <article class="blog-post">
            <div class="blog-header" style="margin-bottom:40px; text-align:center">
                <h1 style="font-size:clamp(2rem, 5vw, 3rem); margin-bottom:16px"><?= htmlspecialchars($post['title']) ?></h1>
                <div style="color:var(--text-muted); font-size:14px"><?= $date_prefix ?>: <?= htmlspecialchars($post['published_at']) ?></div>
            </div>
            
            <?php if (!empty($post['featured_image'])): ?>
                <div class="blog-featured-img" style="margin-bottom:40px">
                    <img src="/<?= htmlspecialchars($post['featured_image']) ?>" style="width:100%; border-radius:var(--radius); box-shadow:0 10px 30px rgba(0,0,0,0.1)">
                </div>
            <?php endif; ?>
            
            <div class="blog-content" style="line-height:1.8; font-size:18px; color:var(--text);overflow:hidden">
                <?= $post['content'] ?>
            </div>
        </article>

        <?php if (count($related) > 1): $others = array_filter($related, function($r) use ($id) { return $r['id'] !== $id; }); $others = array_slice($others, 0, 3); ?>
            <section class="related-posts" style="margin-top:60px; border-top:1px solid var(--border); padding-top:40px">
                <h2 style="margin-bottom:24px; text-align:center"><?= $related_title ?></h2>
                <div class="related-grid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px">
                    <?php foreach ($others as $other): ?>
                        <a href="blog.php?id=<?= $other['id'] ?>&lang=<?= $lang ?>" class="related-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; gap:12px; border-radius:var(--radius); overflow:hidden; background:var(--bg2); transition:transform 0.3s" onmouseenter="this.style.transform='translateY(-4px)'" onmouseleave="this.style.transform=''">
                            <?php if (!empty($other['featured_image'])): ?>
                                <img src="/<?= htmlspecialchars($other['featured_image']) ?>" style="width:100%; aspect-ratio:16/9; object-fit:cover">
                            <?php else: ?>
                                <div style="width:100%; aspect-ratio:16/9; background:var(--bg3); display:flex; align-items:center; justify-content:center; color:var(--text-muted)"><i class="fa-solid fa-image" style="font-size:24px"></i></div>
                            <?php endif; ?>
                            <div style="padding:0 16px 16px">
                                <div style="font-size:13px; color:var(--text-muted); margin-bottom:4px"><?= $date_prefix ?>: <?= htmlspecialchars($other['published_at']) ?></div>
                                <div style="font-weight:500"><?= htmlspecialchars($other['title']) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <style>.blog-content img{max-width:100%;height:auto;border-radius:var(--radius)}@media(max-width:768px){.btn-outline{margin-left:auto!important;margin-right:auto!important;display:table!important}}</style>
    <footer style="padding:40px 0; text-align:center; color:var(--text-muted); font-size:14px">
        &copy; <?= date('Y') ?> Lueta Dzirniece. All rights reserved.
    </footer>
    <script src="js/main.js?v=<?= filemtime(__DIR__ . '/js/main.js') ?>"></script>
</body>
</html>
