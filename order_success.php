<?php
session_start();
include_once 'config/database.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order_id = $_GET['order_id'];

// Ambil detail order
$query = "SELECT o.*, c.name, c.email, c.address, c.phone
          FROM orders o
          JOIN customers c ON o.customer_id = c.id
          WHERE o.id = :order_id AND o.customer_id = :customer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $order_id);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Toko Buku Budi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4">Pesanan Berhasil!</h2>
                        <div class="alert alert-success">
                            Terima kasih telah berbelanja di Toko Buku Budi.
                            Nomor pesanan Anda: #<?php echo $order_id; ?>
                        </div>
                        <p>Total pembayaran: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                        <p>Status: <?php echo ucfirst($order['status']); ?></p>
                        <hr>
                        <h5>Detail Pengiriman:</h5>
                        <p>
                            <?php echo $order['name']; ?><br>
                            <?php echo $order['address']; ?><br>
                            Telp: <?php echo $order['phone']; ?><br>
                            Email: <?php echo $order['email']; ?>
                        </p>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 