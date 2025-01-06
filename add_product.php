<?php
session_start();
require_once "config/database.php";

// Pastikan direktori upload ada
$upload_dir = "assets/images/products/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Proses upload gambar
        $image_url = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $target_dir = "assets/images/products/";
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Validasi file
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("Hanya file JPG, JPEG & PNG yang diizinkan.");
            }
            
            if ($_FILES["image"]["size"] > 5000000) {
                throw new Exception("Ukuran file terlalu besar (maksimal 5MB).");
            }
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $new_filename;
            } else {
                throw new Exception("Gagal mengupload file.");
            }
        }
        
        // Insert data produk
        $query = "INSERT INTO products (title, author, category_id, description, price, stock, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['title'],
            $_POST['author'],
            $_POST['category_id'],
            $_POST['description'],
            $_POST['price'],
            $_POST['stock'],
            $image_url
        ]);
        
        $_SESSION['success'] = "Produk berhasil ditambahkan!";
        header("Location: products.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: add_product.php");
        exit;
    }
}

// Ambil daftar kategori untuk form
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM categories ORDER BY category_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!-- HTML form code remains the same --> 