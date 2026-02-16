<?php
session_start();

// Admin Credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Infinity@2k26');

// Authorized Admin Emails
define('AUTHORIZED_ADMINS', [
    '23x020@psgtech.ac.in',
    '23x022@psgtech.ac.in',
    '23x050@psgtech.ac.in',
    '23x045@psgtech.ac.in',
    '23x019@psgtech.ac.in'
]);

// Publicity Desk Password (for walk-in registration entry)
define('DESK_PASSWORD', 'Desk@Infinity2k26');

// Firebase Config
define('FIREBASE_PROJECT_ID', 'infinity-2k26'); // Replace with your Firebase Project ID

// Registration MySQL Config (reusing existing)
require_once __DIR__ . '/../php/db_config.php';

// Define constants for mysqli compatibility
if (!defined('DB_HOST')) define('DB_HOST', $host);
if (!defined('DB_USER')) define('DB_USER', $user);
if (!defined('DB_PASS')) define('DB_PASS', $pass);
if (!defined('DB_NAME')) define('DB_NAME', $db);

function check_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
?>
