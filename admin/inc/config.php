<?php
define('ADMIN_USER', 'lueta');
define('ADMIN_PASS_HASH', 'REPLACE_PASS_HASH');

define('SUPABASE_URL', 'https://nyrzjdotaxacvjomthll.supabase.co');
define('SUPABASE_ANON_KEY', 'REPLACE_ANON_KEY');
define('SUPABASE_SERVICE_KEY', 'REPLACE_SERVICE_KEY');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
