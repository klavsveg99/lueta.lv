<?php
session_start();
require_once 'inc/auth.php';
if (isLoggedIn()) { header('Location: content.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (login($u, $p)) {
        /* login() already sets session */
        header('Location: content.php');
        exit;
    }
    $error = 'Nepareizs lietotājvārds vai parole.';
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pieteikšanās</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
</head>
<body>
<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">Lueta<span>.</span></div>
            <h1>Satura pārvaldība</h1>
            <?php if ($error): ?>
                <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Lietotājvārds</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Parole</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Pieteikties</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
