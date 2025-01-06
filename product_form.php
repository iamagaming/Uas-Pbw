<?php
session_start();
require_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

$product = [
    'product_id' => '',
    'title' => '',
    'author' => '',
    'category_id' => '',
    'price' => '',
    'stock' => '',
    'description' => '',
    'image_url' => ''
];

// Jika mode edit
if(isset($_GET['id'])) {
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Proses form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = 'assets/images/products/';
    $image_url = $product['image_url']; // Tetap gunakan gambar lama jika tidak ada upload baru

    // Handle file upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileName = time() . '_' . $_FILES['image']['name'];
        $targetPath = $uploadDir . $fileName;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_url = $targetPath;
        }
    }

    if(isset($_POST['product_id']) && $_POST['product_id']) {
        // Update existing product
        $query = "UPDATE products SET 
                  title = ?, author = ?, category_id = ?, 
                  price = ?, stock = ?, description = ?, 
                  image_url = ?
                  WHERE product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['title'],
            $_POST['author'],
            $_POST['category_id'],
            $_POST['price'],
            $_POST['stock'],
            $_POST['description'],
            $image_url,
            $_POST['product_id']
        ]);
    } else {
        // Insert new product
        $query = "INSERT INTO products 
                  (title, author, category_id, price, stock, description, image_url) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['title'],
            $_POST['author'],
            $_POST['category_id'],
            $_POST['price'],
            $_POST['stock'],
            $_POST['description'],
            $image_url
        ]);
    }

    header('Location: products.php');
    exit;
}

// Get categories for dropdown
$query = "SELECT * FROM categories ORDER BY category_name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_id'] ? 'Edit' : 'Tambah'; ?> Produk - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h2><?php echo $product['product_id'] ? 'Edit' : 'Tambah'; ?> Produk</h2>
        
        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Judul Novel</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo $product['title']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="author" class="form-label">Penulis</label>
                <input type="text" class="form-control" id="author" name="author" 
                       value="<?php echo $product['author']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"
                                <?php echo $category['category_id'] == $product['category_id'] ? 'selected' : ''; ?>>
                            <?php echo $category['category_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="number" class="form-control" id="price" name="price" 
                       value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stock" name="stock" 
                       value="<?php echo $product['stock']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $product['description']; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Gambar</label>
                <?php if($product['image_url']): ?>
                    <div class="mb-2">
                        <img src="<?php echo $product['image_url']; ?>" alt="Current image" style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="products.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html> 