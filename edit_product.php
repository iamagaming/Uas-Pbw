<?php
session_start();
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = "UPDATE products 
              SET category_id = :category_id,
                  title = :title,
                  author = :author,
                  price = :price,
                  stock = :stock,
                  description = :description";
    

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uas_reno/img/";
        
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $image = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                
                if (!empty($_POST['old_image'])) {
                    $old_file = $target_dir . $_POST['old_image'];
                    if(file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }
                
                $query .= ", image = :image";
            }
        }
    }
    
    $query .= " WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    
    $stmt->bindParam(':category_id', $_POST['category_id']);
    $stmt->bindParam(':title', $_POST['title']);
    $stmt->bindParam(':author', $_POST['author']);
    $stmt->bindParam(':price', $_POST['price']);
    $stmt->bindParam(':stock', $_POST['stock']);
    $stmt->bindParam(':description', $_POST['description']);
    $stmt->bindParam(':id', $id);
    
    
    if(isset($image)) {
        $stmt->bindParam(':image', $image);
    }
    
    if($stmt->execute()) {
        header("Location: products.php");
        exit();
    }
}

$query = "SELECT * FROM products WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit();
}


$query = "SELECT id, name FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Toko Buku Budi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4">Edit Produk</h2>
        
        <form action="edit_product.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="old_image" value="<?php echo $product['image']; ?>">
            
            <div class="mb-3">
                <label for="category" class="form-label">Kategori</label>
                <select class="form-select" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Judul Buku</label>
                <input type="text" class="form-control" name="title" value="<?php echo $product['title']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="author" class="form-label">Penulis</label>
                <input type="text" class="form-control" name="author" value="<?php echo $product['author']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="number" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stok</label>
                <input type="number" class="form-control" name="stock" value="<?php echo $product['stock']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3"><?php echo $product['description']; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Gambar</label>
                <?php if($product['image']): ?>
                    <div class="mb-2">
                        <img src="../uas_reno/img/<?php echo $product['image']; ?>" alt="Current Image" style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" name="image" onchange="previewImage(this)">
                <img id="imagePreview" src="#" alt="Preview" style="max-width: 200px; display: none;" class="mt-2">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="products.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 