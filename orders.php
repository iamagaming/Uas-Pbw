<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_url'] = "orders.php";
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get orders
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
          FROM orders o 
          WHERE o.customer_id = ? 
          ORDER BY o.order_date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['customer_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">Riwayat Pesanan</h2>

        <?php if(empty($orders)): ?>
            <div class="alert alert-info">
                Anda belum memiliki pesanan. 
                <a href="products.php" class="alert-link">Belanja sekarang</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Jumlah Item</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['item_count']; ?> item</td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $status_class = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $status_text = [
                                    'pending' => 'Menunggu',
                                    'processing' => 'Diproses',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $status_class[$order['status']]; ?>">
                                    <?php echo $status_text[$order['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" 
                                   class="btn btn-sm btn-primary">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 