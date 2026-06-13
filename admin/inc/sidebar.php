<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">Lueta<span>.</span></div>
        <button class="sidebar-close" id="sidebarClose">&times;</button>
    </div>
    <nav class="sidebar-nav">
        <a href="content.php" class="<?= strpos($_SERVER['PHP_SELF'], 'content.php') !== false ? 'active' : '' ?>">Satura redaktors</a>
        <a href="services.php?lang=lv" class="<?= strpos($_SERVER['PHP_SELF'], 'services.php') !== false ? 'active' : '' ?>">Pakalpojumi</a>
        <a href="testimonials.php?lang=lv" class="<?= strpos($_SERVER['PHP_SELF'], 'testimonials.php') !== false ? 'active' : '' ?>">Atsauksmes</a>
        <a href="experience.php?lang=lv" class="<?= strpos($_SERVER['PHP_SELF'], 'experience.php') !== false ? 'active' : '' ?>">Pieredze</a>
        <a href="submissions.php" class="<?= strpos($_SERVER['PHP_SELF'], 'submissions.php') !== false ? 'active' : '' ?>">Kontaktforma</a>
        <hr>
        <a href="/" target="_blank">Skatīt vietni</a>
        <a href="logout.php">Iziet</a>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
