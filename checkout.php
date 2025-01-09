<?php
session_start();
include_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil data keranjang
$query = "SELECT c.*, p.title, p.price, p.stock 
          FROM cart_items c
          JOIN products p ON c.product_id = p.id
          WHERE c.customer_id = :customer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Hitung total
$total = 0;
foreach($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Proses checkout
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // Buat order baru
        $query = "INSERT INTO orders (customer_id, total_amount, status) 
                  VALUES (:customer_id, :total_amount, 'pending')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':customer_id', $_SESSION['user_id']);
        $stmt->bindParam(':total_amount', $total);
        $stmt->execute();
        $order_id = $db->lastInsertId();

        // Tambahkan item order
        foreach($cart_items as $item) {
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();

            // Update stok produk
            $query = "UPDATE products 
                      SET stock = stock - :quantity 
                      WHERE id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->execute();
        }

        // Kosongkan keranjang
        $query = "DELETE FROM cart_items WHERE customer_id = :customer_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':customer_id', $_SESSION['user_id']);
        $stmt->execute();

        $db->commit();
        header("Location: order_success.php?order_id=" . $order_id);
        exit();
    } catch(Exception $e) {
        $db->rollBack();
        $error = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Buku Budi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4">Checkout</h2>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Detail Pesanan</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cart_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['title']; ?></td>
                                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Pembayaran</h5>
                        <p class="card-text">
                            <strong>Total Pembayaran:</strong><br>
                            Rp <?php echo number_format($total, 0, ',', '.'); ?>
                        </p>
                        <form action="checkout.php" method="POST">
                            <button type="submit" class="btn btn-primary w-100">Konfirmasi Pesanan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 