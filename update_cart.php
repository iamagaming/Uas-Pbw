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
    $quantity = $_POST['quantity'];

    // Cek stok produk
    $query = "SELECT p.stock 
              FROM cart_items c
              JOIN products p ON c.product_id = p.id
              WHERE c.id = :cart_item_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':cart_item_id', $cart_item_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if($quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        exit();
    }

    $query = "UPDATE cart_items 
              SET quantity = :quantity 
              WHERE id = :id AND customer_id = :customer_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':id', $cart_item_id);
    $stmt->bindParam(':customer_id', $_SESSION['user_id']);

    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate keranjang']);
    }
} 