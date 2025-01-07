<?php
require_once '../models/Customer.php';

if (isset($_GET['id'])) {
    $customer = new Customer();
    $id = $_GET['id'];
    
    // Note: This will not delete associated orders
    $result = $customer->delete($id);
}

header('Location: index.php');
exit; 