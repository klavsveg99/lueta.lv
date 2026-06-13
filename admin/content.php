<?php
session_start();
require_once 'inc/auth.php';
require_once 'inc/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$supabase = getSupabase();
$lang = $_GET['lang'] ?? $_POST['lang'] ?? 'lv';
if (!in_array($lang, ['lv', 'en'])) $lang = 'lv';
$page = ($lang === 'en') ? 'en' : 'index';
$globalPage = 'index'; // Used for shared assets like images
$blocks = getExistingBlocks($supabase, $page);
$saved = false;

// Handle reset all
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_all') {
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
        header('Location: content.php?lang=' . $lang);
        exit;
    }
    // Delete all content_blocks for both pages
    $res = $supabase->delete('content_blocks', array('page' => 'in.(index,en)'));
    if (isset($res['error'])) error_log('Reset: content_blocks delete error: ' . $res['error']);
    // Delete all data from main tables for both pages to avoid duplicates
    $tables = array('services', 'testimonials', 'experiences');
    foreach ($tables as $table) {
        foreach (array('index', 'en') as $page) {
            $res = $supabase->delete($table, array('page' => 'eq.' . $page));
            if (isset($res['error'])) error_log('Reset: ' . $table . ' delete error for ' . $page . ': ' . $res['error']);
        }
    }
    // Delete uploaded images
    $uploadDir = __DIR__ . '/../media';
    if (is_dir($uploadDir)) {
        $files = glob($uploadDir . '/hero-*.*');
        foreach ($files as $file) unlink($file);
        $files = glob($uploadDir . '/missis-*.*');
        foreach ($files as $file) unlink($file);
    }
    // Re-insert default content for services, testimonials, experiences
    $defaultServices = array(
        array('page' => 'index', 'title' => 'Zīmola mārketings', 'description' => 'Veidoju zīmola identitāti, pozicionēšanu un balsi, kas emocionāli saista ar jūsu auditoriju un atšķir tirgū.', 'display_order' => 0),
        array('page' => 'index', 'title' => 'Efektivitātes mārketings', 'description' => 'Izstrādāju datu virzītas kampaņas, kas nodrošina mērāmu ROI digitālajos kanālos - maksas sludinājumus, retargetingu un konversiju optimizāciju.', 'display_order' => 1),
        array('page' => 'index', 'title' => 'Produktu mārketings', 'description' => 'Izstrādāju laišanas tirgū stratēģijas produktiem - ziņojuma struktūras, palaišanas plānus un pārdošanas atbalsta materiālus.', 'display_order' => 2),
        array('page' => 'index', 'title' => 'Mārketinga stratēģija', 'description' => 'Veidoju visaptverošus mārketinga ceļvežus, saskaņotus ar biznesa mērķiem - kanālu izvēli, budžeta sadali un izaugsmes ietvarus.', 'display_order' => 3),
        array('page' => 'index', 'title' => 'Sociālo mediju mārketings', 'description' => 'Izstrādāju satura stratēģijas un kopienas pārvaldību, kas veido iesaistītas auditorijas un vada zīmola aizstāvību.', 'display_order' => 4),
        array('page' => 'index', 'title' => 'Zīmola dizains', 'description' => 'Veidoju vizuālās identitātes sistēmas - logotipus, vadlīnijas un materiālu dizainu - nodrošinot konsekvenci katrā pieskārienā.', 'display_order' => 5),
        array('page' => 'index', 'title' => 'Pasākumu mārketings', 'description' => 'Konceptualizēju un izpildu pasākumus no gala līdz galam - no laišanām līdz korporatīvām pieredzēm un zīmola aktivizācijām.', 'display_order' => 6),
        array('page' => 'index', 'title' => 'Digitālais mārketings', 'description' => 'Nodrošinu holistisku digitālo klātbūtni - SEO, satura mārketingu, e-pasta kampaņas un analītikas virzītu optimizāciju.', 'display_order' => 7),
        array('page' => 'index', 'title' => 'Zīmola konsultācijas', 'description' => 'Sniedzu stratēģiskas konsultācijas zīmola izaicinājumiem - auditus, konkurentu analīzi un rīcībspējīgas rekomendācijas izaugsmei.', 'display_order' => 8),
    );
    foreach ($defaultServices as $s) {
        $supabase->insert('services', $s);
    }

    $defaultTestimonials = array(
        array('page' => 'index', 'text' => 'Sadarbība ar Luetu bija patīkama, komunikācija vienkārša un projekts noritēja gludi. Termiņi tika ievēroti, un viņas profesionālā pieeja bija patiesi iedvesmojoša.', 'author_name' => 'Roberts Kalķis', 'author_role' => 'Projektu vadītājs, Diamond Group', 'display_order' => 0),
        array('page' => 'index', 'text' => 'Strādājot ar Luetu pie sociālo tīklu dizaina un vizuālās stratēģijas, saņēmu iedvesmojošu, radošu un profesionālu sadarbību, kas radīja mūsdienīgu un autentisku dizainu.', 'author_name' => 'Anastasija Ivanova', 'author_role' => 'UI/UX, Grafiskais un Web dizains', 'display_order' => 1),
        array('page' => 'index', 'text' => 'Strādājot ar Luetu Misis Latvija projektā bija viegli un patīkami. Viņa ir cilvēku cilvēks - uzklausa un atbalsta. Kopā filmējām video, un tā bija neaizmirstama pieredze. Satikt tik sirsnīgu cilvēku bija patīkams pārsteigums!', 'author_name' => 'Ulla Perkune', 'author_role' => 'Fotogrāfe/videogrāfe', 'display_order' => 2),
    );
    foreach ($defaultTestimonials as $t) {
        $supabase->insert('testimonials', $t);
    }

    $defaultExperiences = array(
        array('page' => 'index', 'icon' => 'fa-solid fa-layer-group', 'title' => 'Zīmolu vadība', 'description' => 'Attīstu pilnu zīmola identitāti, pozicionēšanas stratēģiju un ilgtermiņa zīmola kapitālu B2B un B2C sektoros.', 'display_order' => 0),
        array('page' => 'index', 'icon' => 'fa-solid fa-microphone-lines', 'title' => 'Korporatīvā komunikācija', 'description' => 'Veidoju iekšējā un ārējā ziņojuma ietvarus, kas saskaņo ieinteresētās puses un stiprina organizācijas reputāciju.', 'display_order' => 1),
        array('page' => 'index', 'icon' => 'fa-solid fa-rocket', 'title' => 'Produktu attīstības', 'description' => 'Vadu starpfunkcionālas komandas no koncepta līdz laišanai tirgū - pārvaldu laika grafikus, budžetus un ieinteresēto pušu saskaņošanu.', 'display_order' => 2),
        array('page' => 'index', 'icon' => 'fa-solid fa-bullhorn', 'title' => 'Pārdošanas aktivizācija', 'description' => 'Izstrādāju kampaņu stratēģijas, kas šķērso mārketingu un pārdošanu - virzu kvalificētus potenciālos klientus un optimizēju konversijas.', 'display_order' => 3),
        array('page' => 'index', 'icon' => 'fa-solid fa-code-branch', 'title' => 'Multi-kreatora kampaņas', 'description' => 'Orķestrēju reklāmas starp vairākiem kreatoriem un kanāliem - nodrošinot zīmola konsekvenci un maksimālu ietekmi.', 'display_order' => 4),
        array('page' => 'index', 'icon' => 'fa-solid fa-bolt', 'title' => 'Uzņēmējdarbība', 'description' => 'Veidoju un mērogoju uzņēmumus ar fokusu uz ilgtspējīgiem zīmola pamatiem, tirgus atšķirību un ilgtermiņa vērtības radīšanu.', 'display_order' => 5),
    );
    foreach ($defaultExperiences as $e) {
        $supabase->insert('experiences', $e);
    }

    // Re-insert default content for about_list (saraksts)
    $defaultAboutList = array(
        array('page' => 'index', 'block_key' => 'about_list', 'block_value' => json_encode(array(
            'Zīmola stratēģija un pozicionēšana',
            'Komunikācija un zīmola identitāte',
            'Klientu piesaistes sistēmas',
            'Estētika, PR un radoši pasākumi'
        ), JSON_UNESCAPED_UNICODE), 'section' => 'about'),
        array('page' => 'en', 'block_key' => 'about_list', 'block_value' => json_encode(array(
            'Brand strategy and positioning',
            'Communication and brand identity',
            'Client acquisition systems',
            'Aesthetics, PR and creative events'
        ), JSON_UNESCAPED_UNICODE), 'section' => 'about'),
    );
    foreach ($defaultAboutList as $b) {
        $supabase->insert('content_blocks', $b);
    }

    header('Location: content.php?lang=' . $lang);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_image') {
    $section = $_POST['section'] ?? '';
    if (($section === 'hero' || $section === 'missis') && isset($_FILES['image'])) {
        $files = $_FILES['image'];
        // Handle both single and multiple uploads
        $fileArray = array();
        if (is_array($files['name'])) {
            // Multiple files
            for ($i = 0; $i < count($files['name']); $i++) {
                $fileArray[] = array(
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                );
            }
        } else {
            // Single file
            $fileArray[] = $files;
        }

        $uploadDir = __DIR__ . '/../media';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $blockKey = $section . '_images';
        $existing = $supabase->select('content_blocks', array(
            'page' => 'eq.' . $globalPage, 'block_key' => 'eq.' . $blockKey, 'select' => 'id,block_value',
        ));
        $paths = array();
        if ($existing && !isset($existing['error']) && count($existing) > 0) {
            $paths = json_decode($existing[0]['block_value'], true);
            if (!is_array($paths)) $paths = array();
        }

        $allowed = array('jpg', 'jpeg', 'png', 'webp', 'gif');
        foreach ($fileArray as $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                $filename = $section . '-' . time() . '-' . substr(md5(mt_rand()), 0, 8) . '.' . $ext;
                $dest = $uploadDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $paths[] = 'media/' . $filename;
                }
            }
        }

        $json = json_encode($paths, JSON_UNESCAPED_UNICODE);
        if ($existing && !isset($existing['error']) && count($existing) > 0) {
            $supabase->update('content_blocks', array('block_value' => $json, 'updated_at' => date('c')), array('page' => 'eq.' . $globalPage, 'block_key' => 'eq.' . $blockKey));
        } else {
            $supabase->insert('content_blocks', array('page' => $globalPage, 'section' => 'images', 'block_key' => $blockKey, 'block_value' => $json));
        }
    }
    header('Location: content.php?section=images&lang=' . $lang);
    exit;
}

// Handle image delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_image') {
    $section = $_POST['section'] ?? '';
    $idx = intval($_POST['index'] ?? -1);
    if (($section === 'hero' || $section === 'missis') && $idx >= 0) {
        $blockKey = $section . '_images';
        $existing = $supabase->select('content_blocks', array(
            'page' => 'eq.' . $globalPage, 'block_key' => 'eq.' . $blockKey, 'select' => 'id,block_value',
        ));
        if ($existing && !isset($existing['error']) && count($existing) > 0) {
            $paths = json_decode($existing[0]['block_value'], true);
            if (is_array($paths) && isset($paths[$idx])) {
                array_splice($paths, $idx, 1);
                $json = json_encode($paths, JSON_UNESCAPED_UNICODE);
                $supabase->update('content_blocks', array('block_value' => $json, 'updated_at' => date('c')), array('page' => 'eq.' . $globalPage, 'block_key' => 'eq.' . $blockKey));
            }
        }
    }
    header('Location: content.php?section=images&lang=' . $lang);
    exit;
}

// Handle reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order') {
    $table = $_POST['table'] ?? '';
    $ids = $_POST['ids'] ?? array();
    if (in_array($table, ['services', 'testimonials', 'experiences'])) {
        foreach ($ids as $idx => $id) {
            $supabase->update($table, array('display_order' => $idx), array('id' => 'eq.' . $id));
        }
    } elseif ($table === 'hero_images' || $table === 'missis_images') {
        $blockKey = $table;
        $existing = $supabase->select('content_blocks', array(
            'page' => 'eq.' . $globalPage, 'block_key' => 'eq.' . $blockKey, 'select' => 'id,block_value',
        ));
        if ($existing && !isset($existing['error']) && count($existing) > 0) {
            $json = json_encode($ids, JSON_UNESCAPED_UNICODE);
            $supabase->update('content_blocks', array('block_value' => $json, 'updated_at' => date('c')), array('page' => 'eq.' . $globalPage, 'block_key' => 'eq.' . $blockKey));
        }
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $defs = getBlockDefinitions();

    // Handle stats save
    if (isset($_POST['stats']) && is_array($_POST['stats'])) {
        for ($i = 1; $i <= 20; $i++) {
            saveContentBlock($supabase, $page, 'stats', 'stat_' . $i . '_count', '');
            saveContentBlock($supabase, $page, 'stats', 'stat_' . $i . '_label', '');
        }
        foreach ($_POST['stats'] as $idx => $stat) {
            $i = $idx + 1;
            saveContentBlock($supabase, $page, 'stats', 'stat_' . $i . '_count', trim($stat['count'] ?? ''));
            saveContentBlock($supabase, $page, 'stats', 'stat_' . $i . '_label', trim($stat['label'] ?? ''));
        }
    }

    // Handle regular fields
    foreach ($defs as $section => $items) {
        if ($section === 'images' || $section === 'stats') continue;
        foreach ($items as $key => $def) {
            if (isset($_POST[$key])) {
                $val = $_POST[$key];
                if ($def['type'] === 'list') {
                    if (is_array($val)) {
                        $val = array_filter(array_map('trim', $val));
                    } else {
                        $val = array_filter(array_map('trim', explode("\n", $val)));
                    }
                    $val = json_encode(array_values($val), JSON_UNESCAPED_UNICODE);
                } else {
                    $val = trim($val);
                }
                saveContentBlock($supabase, $page, $section, $key, $val);
            }
        }
    }
    $saved = true;
    $blocks = getExistingBlocks($supabase, $page);
}

$section_filter = $_GET['section'] ?? 'all';
$defs = getBlockDefinitions();

$page_title = 'Satura redaktors';
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
            <div style="display:flex;gap:8px;align-items:center">
                <button class="btn btn-danger" id="resetAllBtn"><i class="fa-solid fa-trash"></i> Atiestatīt visu</button>
            </div>
        </div>

        <div class="modal-overlay" id="resetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center">
            <div class="modal-card" style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:24px;max-width:400px;width:90%">
                <h2 style="color:var(--danger);margin-bottom:12px"><i class="fa-solid fa-triangle-exclamation"></i> Apstiprināt atiestatīšanu</h2>
                <p style="color:var(--text-muted);margin-bottom:20px">Šī darbība nevar tikt atcelta. Tiks izdzēsti <strong>visi satura ieraksti</b> (abi valodas) un <strong>visi augšupielādētie attēli</b>.</p>
                <form method="POST" style="display:flex;gap:12px;justify-content:flex-end">
                    <input type="hidden" name="action" value="reset_all">
                    <input type="hidden" name="confirm" value="yes">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <button type="button" class="btn btn-outline" id="resetCancelBtn">Atcelt</button>
                    <button type="submit" class="btn btn-danger">Dzēst visu un atiestatīt</button>
                </form>
            </div>
        </div>

        <?php if ($saved): ?>
            <div class="page-content"><div class="msg msg-success">Saglabāts veiksmīgi!</div></div>
        <?php endif; ?>

        <div class="lang-tabs">
            <a href="?lang=lv&amp;section=<?= $section_filter ?>" class="tab <?= $lang === 'lv' ? 'active' : '' ?>">LV</a>
            <a href="?lang=en&amp;section=<?= $section_filter ?>" class="tab <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>

        <div class="page-tabs" id="sectionTabs">
            <a href="?section=all&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'all' ? 'active' : '' ?>">Visi</a>
            <a href="?section=hero&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'hero' ? 'active' : '' ?>">Hero</a>
            <a href="?section=about&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'about' ? 'active' : '' ?>">Par mani</a>
            <a href="?section=stats&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'stats' ? 'active' : '' ?>">Statistika</a>
            <a href="?section=services&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'services' ? 'active' : '' ?>">Pakalpojumi</a>
            <a href="?section=missis&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'missis' ? 'active' : '' ?>">Papildus info</a>
            <a href="?section=experience&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'experience' ? 'active' : '' ?>">Pieredze</a>
            <a href="?section=testimonials&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'testimonials' ? 'active' : '' ?>">Atsauksmes</a>
            <a href="?section=contact&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'contact' ? 'active' : '' ?>">Kontakti</a>
            <a href="?section=footer&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'footer' ? 'active' : '' ?>">Kājene</a>
            <a href="?section=images&amp;lang=<?= $lang ?>" class="tab <?= $section_filter === 'images' ? 'active' : '' ?>">Attēli</a>
        </div>

        <form method="POST" class="content-form">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <?php
            foreach ($defs as $section => $items) {
                if ($section === 'images') continue;
                if ($section_filter !== 'all' && $section_filter !== $section) continue;
                ?>
                <div class="card">
                    <h2><?php
                        $labels = array(
                            'hero' => 'Hero sadaļa',
                            'about' => 'Par mani sadaļa',
                            'stats' => 'Statistika',
                            'services' => 'Pakalpojumu sadaļa',
                            'missis' => 'Papildus info sadaļa',
                            'experience' => 'Pieredzes sadaļa',
                            'testimonials' => 'Atsauksmju sadaļa',
                            'contact' => 'Kontaktu sadaļa',
                            'footer' => 'Kājene',
                        );
                        echo $labels[$section] ?? $section;
                    ?></h2>
                    <?php foreach ($items as $key => $def): ?>
                        <?php if ($def['type'] === 'stats'): ?>
                            <?php
                            $stats = array();
                            for ($i = 1; $i <= 20; $i++) {
                                $cv = $blocks['stat_' . $i . '_count']['block_value'] ?? '';
                                $lv = $blocks['stat_' . $i . '_label']['block_value'] ?? '';
                                if ($cv !== '' || $lv !== '') {
                                    $stats[] = array('count' => $cv, 'label' => $lv);
                                }
                            }
                            if (empty($stats)) {
                                if ($lang === 'en') {
                                    $stats = array(
                                        array('count' => '10', 'label' => 'Years Experience'),
                                        array('count' => '50', 'label' => 'Brands Led'),
                                        array('count' => '100', 'label' => 'Projects Delivered'),
                                    );
                                } else {
                                    $stats = array(
                                        array('count' => '10', 'label' => 'Gadu pieredze'),
                                        array('count' => '50', 'label' => 'Vadīti zīmoli'),
                                        array('count' => '100', 'label' => 'Pabeigti projekti'),
                                    );
                                }
                            }
                            $statCountPh = $lang === 'en' ? 'Number' : 'Skaitlis';
                            $statLabelPh = $lang === 'en' ? 'Title' : 'Nosaukums';
                            ?>
                            <div class="form-group">
                                <label><?= htmlspecialchars($def['label']) ?></label>
                                <div id="stats-container">
                                    <?php foreach ($stats as $idx => $s): ?>
                                        <div class="dynamic-row" style="display:flex;gap:8px;margin-bottom:8px;align-items:center">
                                            <input type="number" name="stats[<?= $idx ?>][count]" value="<?= htmlspecialchars($s['count']) ?>" placeholder="<?= $statCountPh ?>" style="width:120px">
                                            <input type="text" name="stats[<?= $idx ?>][label]" value="<?= htmlspecialchars($s['label']) ?>" placeholder="<?= $statLabelPh ?>" style="flex:1">
                                            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm add-stat-row"><i class="fa-solid fa-plus"></i> Pievienot rindu</button>
                            </div>
                        <?php elseif ($def['type'] === 'list'): ?>
                            <?php
                            $listItems = json_decode($blocks[$key]['block_value'] ?? '[]', true);
                            if (!is_array($listItems)) $listItems = array();
                            if (empty($listItems)) $listItems = array('');
                            $ph = $def['placeholder_' . $lang] ?? $def['placeholder'] ?? '';
                            ?>
                            <div class="form-group">
                                <label><?= htmlspecialchars($def['label']) ?></label>
                                <div class="dynamic-list" id="list-<?= $key ?>">
                                    <?php foreach ($listItems as $idx => $item): ?>
                                        <div class="dynamic-row" style="display:flex;gap:8px;margin-bottom:8px;align-items:center">
                                            <input type="text" name="<?= $key ?>[]" value="<?= htmlspecialchars($item) ?>" placeholder="<?= htmlspecialchars($ph) ?>" style="flex:1">
                                            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm add-list-row" data-list="list-<?= $key ?>" data-placeholder="<?= htmlspecialchars($ph) ?>"><i class="fa-solid fa-plus"></i> Pievienot rindu</button>
                            </div>
                        <?php else: ?>
                            <?php
                            $current = $blocks[$key]['block_value'] ?? '';
                            $ph = $def['placeholder_' . $lang] ?? $def['placeholder'] ?? '';
                            ?>
                            <div class="form-group">
                                <label for="<?= $key ?>"><?= htmlspecialchars($def['label']) ?></label>
                                <?php if ($def['type'] === 'textarea'): ?>
                                    <textarea id="<?= $key ?>" name="<?= $key ?>" rows="3" placeholder="<?= htmlspecialchars($ph) ?>"><?= htmlspecialchars($current) ?></textarea>
                                <?php else: ?>
                                    <input type="<?= $def['type'] === 'number' ? 'number' : 'text' ?>" id="<?= $key ?>" name="<?= $key ?>" value="<?= htmlspecialchars($current) ?>" placeholder="<?= htmlspecialchars($ph) ?>">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php } ?>

            <?php
            if ($section_filter === 'all' || $section_filter === 'images'):
                $globalImages = getExistingBlocks($supabase, $globalPage);
                $heroImages = json_decode($globalImages['hero_images']['block_value'] ?? '[]', true);
                $missisImages = json_decode($globalImages['missis_images']['block_value'] ?? '[]', true);
                if (!is_array($heroImages)) $heroImages = array();
                if (!is_array($missisImages)) $missisImages = array();
            ?>
            <div class="card" style="margin-top:16px">
                <h2><i class="fa-solid fa-images"></i> Hero attēli</h2>
                <div class="gallery-grid" id="heroGallery">
                    <?php foreach ($heroImages as $idx => $path): ?>
                        <div class="gallery-item" data-path="<?= htmlspecialchars($path) ?>">
                            <img src="/<?= htmlspecialchars($path) ?>" alt="">
                            <button type="button" class="gallery-delete" onclick="deleteImage('hero', <?= $idx ?>)"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <h2><i class="fa-solid fa-images"></i> Papildus info attēli</h2>
                <div class="gallery-grid" id="missisGallery">
                    <?php foreach ($missisImages as $idx => $path): ?>
                        <div class="gallery-item" data-path="<?= htmlspecialchars($path) ?>">
                            <img src="/<?= htmlspecialchars($path) ?>" alt="">
                            <button type="button" class="gallery-delete" onclick="deleteImage('missis', <?= $idx ?>)"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions page-content">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Saglabāt visu</button>
            </div>
        </form>

        <?php if ($section_filter === 'all' || $section_filter === 'images'): ?>
        <div class="page-content" style="margin-top:16px">
            <div class="card">
                <form id="heroUploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <input type="hidden" name="section" value="hero">
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label>Hero attēli - Augšupielādēt</label>
                            <input type="file" name="image" accept="image/*" required multiple>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload"></i> Augšupielādēt</button>
                    </div>
                </form>
            </div>
            <div class="card">
                <form id="missisUploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <input type="hidden" name="section" value="missis">
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label>Papildus info attēli - Augšupielādēt</label>
                            <input type="file" name="image" accept="image/*" required multiple>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload"></i> Augšupielādēt</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>

<script>
var currentLang = '<?= $lang ?>';
var statCountPh = currentLang === 'en' ? 'Number' : 'Skaitlis';
var statLabelPh = currentLang === 'en' ? 'Title' : 'Nosaukums';
function deleteImage(section, idx) {
    if (!confirm('Vai tiešām dzēst šo attēlu?')) return;
    var form = new FormData();
    form.append('action', 'delete_image');
    form.append('section', section);
    form.append('index', idx);
    form.append('lang', '<?= $lang ?>');
    fetch('content.php', { method: 'POST', body: form })
        .then(function() { location.reload(); });
}
document.querySelectorAll('[id$="UploadForm"]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var fd = new FormData(this);
        fd.append('action', 'upload_image');
        fetch('content.php', { method: 'POST', body: fd })
            .then(function() { location.reload(); });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    function initGallerySortable(id, table) {
        var el = document.getElementById(id);
        if (el) {
            Sortable.create(el, {
                animation: 150,
                onEnd: function() {
                    var paths = [];
                    el.querySelectorAll('.gallery-item').forEach(function(item) {
                        paths.push(item.dataset.path);
                    });
                    var fd = new FormData();
                    fd.append('action', 'update_order');
                    fd.append('table', table);
                    paths.forEach(function(path, idx) { fd.append('ids[' + idx + ']', path); });
                    fetch('content.php', { method: 'POST', body: fd })
                        .then(function() { console.log('Gallery order updated'); });
                }
            });
        }
    }
    initGallerySortable('heroGallery', 'hero_images');
    initGallerySortable('missisGallery', 'missis_images');

    // Reset all modal
    var resetBtn = document.getElementById('resetAllBtn');
    var resetModal = document.getElementById('resetModal');
    var resetCancel = document.getElementById('resetCancelBtn');
    if (resetBtn && resetModal && resetCancel) {
        resetBtn.addEventListener('click', function() { resetModal.style.display = 'flex'; });
        resetCancel.addEventListener('click', function() { resetModal.style.display = 'none'; });
        resetModal.addEventListener('click', function(e) { if (e.target === resetModal) resetModal.style.display = 'none'; });
    }

    // Dynamic rows: add/remove for stats and lists
    function bindRemoveButtons() {
        document.querySelectorAll('.remove-row').forEach(function(btn) {
            btn.onclick = function() {
                var row = this.closest('.dynamic-row');
                var container = row.parentElement;
                if (container.children.length > 1) {
                    row.remove();
                }
            };
        });
    }
    bindRemoveButtons();

    document.querySelectorAll('.add-stat-row').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var container = document.getElementById('stats-container');
            var idx = container.children.length;
            var row = document.createElement('div');
            row.className = 'dynamic-row';
            row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center';
            row.innerHTML = '<input type="number" name="stats[' + idx + '][count]" placeholder="' + statCountPh + '" style="width:120px">' +
                '<input type="text" name="stats[' + idx + '][label]" placeholder="' + statLabelPh + '" style="flex:1">' +
                '<button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa-solid fa-trash"></i></button>';
            container.appendChild(row);
            bindRemoveButtons();
        });
    });

    document.querySelectorAll('.add-list-row').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var listId = this.getAttribute('data-list');
            var placeholder = this.getAttribute('data-placeholder') || '';
            var container = document.getElementById(listId);
            var row = document.createElement('div');
            row.className = 'dynamic-row';
            row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center';
            var name = listId.replace('list-', '') + '[]';
            row.innerHTML = '<input type="text" name="' + name + '" placeholder="' + placeholder + '" style="flex:1">' +
                '<button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa-solid fa-trash"></i></button>';
            container.appendChild(row);
            bindRemoveButtons();
        });
    });
});
</script>
<script src="js/admin.js?v=<?= filemtime(__DIR__ . '/js/admin.js') ?>"></script>
</body>
</html>
