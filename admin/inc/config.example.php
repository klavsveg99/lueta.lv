<?php
// Admin credentials
define('ADMIN_USER', 'lueta');
// Generate a hash: php -r "echo password_hash('your-password', PASSWORD_BCRYPT);"
define('ADMIN_PASS_HASH', '$2y$10$YourBcryptHashHere');

// Supabase configuration
define('SUPABASE_URL', 'https://your-project-id.supabase.co');
define('SUPABASE_ANON_KEY', 'your-anon-public-key');
define('SUPABASE_SERVICE_KEY', 'your-service-role-key');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
