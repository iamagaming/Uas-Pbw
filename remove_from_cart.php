<?php
session_start();
include_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $cart_item_id = $_POST['cart_item_id'];

    $query = "DELETE FROM cart_items 
              WHERE id = :id AND customer_id = :customer_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $cart_item_id);
    $stmt->bindParam(':customer_id', $_SESSION['user_id']);

    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus item']);
    }
} 