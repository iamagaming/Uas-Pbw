<?php
session_start();
include_once 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];


$query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] > 0) {
    echo "<script>
            alert('Kategori ini tidak dapat dihapus karena masih memiliki produk terkait.');
            window.location.href = 'categories.php';
          </script>";
    exit();
}


$query = "DELETE FROM categories WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);

if($stmt->execute()) {
    header("Location: categories.php");
} else {
    echo "Error deleting category.";
}
exit(); 