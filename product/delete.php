<?php
require_once '../config/config.php';
require_once '../models/Product.php';

// Check authentication
if (!is_logged_in()) {
    redirect('auth/login.php');
}

if (isset($_GET['id'])) {
    $product = new Product();
    $id = $_GET['id'];
    
    if ($product->delete($id)) {
        $_SESSION['success'] = "Product deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete product.";
    }
}

redirect('products/index.php'); 