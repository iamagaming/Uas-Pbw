<?php
require_once '../models/Order.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $order = new Order();
    $id = $_GET['id'];
    $status = $_GET['status'];
    
    // Validate status
    $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $validStatuses)) {
        $result = $order->updateStatus($id, $status);
    }
}

header('Location: index.php');
exit; 