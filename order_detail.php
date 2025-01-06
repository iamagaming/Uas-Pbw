<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_url'] = "order_detail.php?id=" . $_GET['id'];
    header('Location: login.php');
    exit;
}

if(!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get order details
$query = "SELECT o.*, c.name, c.email, c.phone, c.address 
          FROM orders o 
          JOIN customers c ON o.customer_id = c.customer_id 
          WHERE o.order_id = ? AND o.customer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id'], $_SESSION['customer_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$query = "SELECT oi.*, p.title, p.image_url 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.product_id 
          WHERE oi.order_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order['order_id']; ?> - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Pesanan #<?php echo $order['order_id']; ?></h2>
            <a href="orders.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Tanggal Pesanan:</strong></p>
                                <p><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Status:</strong></p>
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
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Informasi Pengiriman:</h6>
                            <p class="mb-1"><?php echo htmlspecialchars($order['name']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['email']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['phone']); ?></p>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                        </div>

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
                                    <?php foreach($items as $item): ?>
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
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Pesanan Dibuat</h6>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                                    </small>
                                </div>
                            </div>
                            <!-- Add more timeline items based on order status -->
                        </div>
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