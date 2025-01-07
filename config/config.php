<?php
// Base URL configuration
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Uas_pbw';
define('BASE_URL', $base_url);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'novel_budiono');

// Site configuration
define('SITE_NAME', 'Novel Budiono');
define('SITE_DESCRIPTION', 'Your premier book store');

// Path configuration
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
define('VIEW_PATH', ROOT_PATH . 'views' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to get base URL
function base_url($path = '') {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

// Function to redirect
function redirect($path = '') {
    $url = base_url($path);
    header("Location: $url");
    exit;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to require authentication
function require_auth() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('auth/login.php');
    }
}

// Debug function
function debug($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}
?> 