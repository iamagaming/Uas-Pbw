<?php
session_start();
include_once 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

$query = "SELECT image FROM products WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);


$query = "DELETE FROM products WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);

if($stmt->execute()) {
    
    if($product['image'] && file_exists("uploads/" . $product['image'])) {
        unlink("uploads/" . $product['image']);
    }
    header("Location: products.php");
} else {
    echo "Error deleting product.";
}
exit(); 