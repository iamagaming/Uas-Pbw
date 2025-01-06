<?php
session_start();
require_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

if(!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

// Ambil detail produk
$query = "SELECT p.*, c.category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          WHERE p.product_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    header('Location: products.php');
    exit;
}

// Proses tambah ke keranjang
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if(!isset($_SESSION['customer_id'])) {
        $_SESSION['redirect_url'] = "product_detail.php?id=" . $_GET['id'];
        header('Location: login.php');
        exit;
    }

    $quantity = (int)$_POST['quantity'];
    if($quantity > 0 && $quantity <= $product['stock']) {
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Cek apakah produk sudah ada di keranjang
        $product_exists = false;
        foreach($_SESSION['cart'] as &$item) {
            if($item['product_id'] == $product['product_id']) {
                $item['quantity'] += $quantity;
                $product_exists = true;
                break;
            }
        }

        if(!$product_exists) {
            $_SESSION['cart'][] = [
                'product_id' => $product['product_id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image_url' => $product['image_url']
            ];
        }

        $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang.";
        header('Location: cart.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-5">
                <?php if($product['image_url']): ?>
                    <img src="<?php echo $product['image_url']; ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                         class="product-image">
                <?php else: ?>
                    <div class="bg-secondary text-white p-5 text-center">
                        No Image Available
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-7">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>
                <p class="text-muted">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <p class="text-muted">Penulis: <?php echo htmlspecialchars($product['author']); ?></p>
                <h3 class="text-primary mb-4">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></h3>
                
                <div class="mb-4">
                    <h5>Deskripsi:</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <?php if($product['stock'] > 0): ?>
                    <form method="POST" action="" class="mb-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <label for="quantity" class="form-label">Jumlah:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="1" min="1" max="<?php echo $product['stock']; ?>">
                            </div>
                            <div class="col-auto">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                                        Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <p class="text-success">Stok: <?php echo $product['stock']; ?></p>
                <?php else: ?>
                    <p class="text-danger">Stok Habis</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 