<?php
session_start();

// Admin Credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Infinity@2k26');

// Publicity Desk Password (for walk-in registration entry)
define('DESK_PASSWORD', 'Desk@Infinity2k26');

// Firebase Config
define('FIREBASE_PROJECT_ID', 'infinity-2k26'); // Replace with your Firebase Project ID

// Registration MySQL Config (reusing existing)
require_once __DIR__ . '/../php/db_config.php';

function check_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
?>
