<?php
$cfg = require __DIR__ . '/../../../private/config.php';

define('ADMIN_USER', $cfg['adminUser']);
define('ADMIN_PASS_HASH', $cfg['adminPassHash']);

define('SUPABASE_URL', $cfg['supabaseUrl']);
define('SUPABASE_ANON_KEY', $cfg['supabaseAnonKey']);
define('SUPABASE_SERVICE_KEY', $cfg['supabaseServiceKey']);

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
