<?php
session_start();
require_once 'inc/auth.php';
require_once 'inc/functions.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$supabase = getSupabase();
$submissions = $supabase->select('contact_submissions', array(
    'select' => '*',
    'order' => 'created_at.desc',
));
if (!is_array($submissions)) $submissions = array();

$page_title = 'Kontaktforma';
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

        <div class="page-content" style="padding-top:20px">
            <?php if (empty($submissions)): ?>
                <div class="card"><p class="text-muted">Nav iesniegumu.</p></div>
            <?php else: ?>
                <div class="list-table">
                    <div class="list-table-header">
                        <span style="flex:1">Datums</span>
                        <span style="flex:1">Vārds</span>
                        <span style="flex:1">E-pasts</span>
                        <span style="flex:1">Tālrunis</span>
                        <span style="flex:1">Zīmols</span>
                        <span style="flex:2">Apraksts</span>
                    </div>
                    <?php foreach ($submissions as $row): ?>
                        <div class="list-table-row">
                            <span style="flex:1"><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></span>
                            <span style="flex:1"><?= htmlspecialchars($row['name'] ?? '') ?></span>
                            <span style="flex:1"><?= htmlspecialchars($row['email'] ?? '') ?></span>
                            <span style="flex:1"><?= htmlspecialchars($row['phone'] ?? '') ?></span>
                            <span style="flex:1"><?= htmlspecialchars($row['brand_name'] ?? '') ?></span>
                            <span style="flex:2"><?= htmlspecialchars($row['company_description'] ?? '') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="js/admin.js?v=<?= filemtime(__DIR__ . '/js/admin.js') ?>"></script>
</body>
</html>
