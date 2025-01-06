<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_url'] = "cart.php";
    header('Location: login.php');
    exit;
}

// Update quantity
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $product_id => $quantity) {
        foreach($_SESSION['cart'] as &$item) {
            if($item['product_id'] == $product_id) {
                $item['quantity'] = max(1, min((int)$quantity, 99));
                break;
            }
        }
    }
    $_SESSION['success'] = "Keranjang berhasil diperbarui.";
    header('Location: cart.php');
    exit;
}

// Remove item
if(isset($_GET['remove']) && isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $key => $item) {
        if($item['product_id'] == $_GET['remove']) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
    $_SESSION['success'] = "Produk berhasil dihapus dari keranjang.";
    header('Location: cart.php');
    exit;
}

// Calculate total
$total = 0;
if(isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">Keranjang Belanja</h2>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
            <div class="alert alert-info">
                Keranjang belanja Anda kosong. 
                <a href="products.php" class="alert-link">Belanja sekarang</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if($item['image_url']): ?>
                                            <img src="<?php echo $item['image_url']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                 style="width: 50px; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </div>
                                </td>
                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="99" class="form-control" style="width: 80px;">
                                </td>
                                <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['product_id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" name="update_cart" class="btn btn-secondary">
                        Update Keranjang
                    </button>
                    <a href="checkout.php" class="btn btn-primary">
                        Lanjut ke Pembayaran
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 