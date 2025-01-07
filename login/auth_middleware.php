<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Store the requested URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    header('Location: /auth/login.php');
    exit;
}

// Add user information to the page
$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
];
?> 