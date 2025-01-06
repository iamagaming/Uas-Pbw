<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_url'] = "checkout.php";
    header('Location: login.php');
    exit;
}

if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get customer data
$query = "SELECT * FROM customers WHERE customer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Process checkout
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // Create order
        $query = "INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'pending')";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['customer_id'], $total]);
        $order_id = $db->lastInsertId();

        // Create order items
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        // Update product stock
        $update_stock = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
        $stmt_stock = $db->prepare($update_stock);

        foreach($_SESSION['cart'] as $item) {
            // Add order item
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);

            // Update stock
            $stmt_stock->execute([
                $item['quantity'],
                $item['product_id']
            ]);
        }

        $db->commit();
        
        // Clear cart
        unset($_SESSION['cart']);
        
        $_SESSION['success'] = "Pesanan berhasil dibuat! Nomor pesanan: " . $order_id;
        header('Location: order_success.php?id=' . $order_id);
        exit;

    } catch(Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">Checkout</h2>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="checkout-form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($customer['name']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat Pengiriman</label>
                                <textarea class="form-control" id="address" name="address" 
                                          rows="3" required><?php echo htmlspecialchars($customer['address']); ?></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <?php echo htmlspecialchars($item['title']); ?>
                                    <small class="text-muted d-block">
                                        <?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                    </small>
                                </div>
                                <div>
                                    Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                        </div>

                        <button type="submit" form="checkout-form" class="btn btn-primary w-100">
                            Buat Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 