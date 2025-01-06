<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['customer_id']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get order details
$query = "SELECT o.*, c.name 
          FROM orders o 
          JOIN customers c ON o.customer_id = c.customer_id 
          WHERE o.order_id = ? AND o.customer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id'], $_SESSION['customer_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="text-center mb-5">
            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
            <h2 class="mt-3">Terima Kasih!</h2>
            <p class="lead">Pesanan Anda telah berhasil dibuat.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Detail Pesanan:</h5>
                        <p class="mb-1">Nomor Pesanan: <strong>#<?php echo $order['order_id']; ?></strong></p>
                        <p class="mb-1">Total: <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></p>
                        <p class="mb-4">Status: 
                            <span class="badge bg-warning">Menunggu Pembayaran</span>
                        </p>

                        <div class="d-grid gap-2">
                            <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" 
                               class="btn btn-primary">
                                Lihat Detail Pesanan
                            </a>
                            <a href="products.php" class="btn btn-outline-secondary">
                                Lanjut Belanja
                            </a>
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