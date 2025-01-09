<?php
session_start();
include_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle form submission untuk update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (kode update profil yang sudah ada) ...
}

// Get user data
$query = "SELECT * FROM customers WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order history
$query = "SELECT o.*, COUNT(oi.id) as total_items 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id
          WHERE o.customer_id = :customer_id 
          GROUP BY o.id
          ORDER BY o.order_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Toko Buku Budi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Profil Section -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Profil Saya</h2>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="profile.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="name" value="<?php echo $user['name']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                <input type="password" class="form-control" name="new_password">
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo $user['phone']; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Profil</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pesanan Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Riwayat Pesanan</h2>
                        
                        <?php if(empty($orders)): ?>
                            <div class="alert alert-info">Belum ada pesanan.</div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($orders as $order): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Pesanan #<?php echo $order['id']; ?></h6>
                                                <small class="text-muted">
                                                    Tanggal: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php echo $order['status'] == 'pending' ? 'warning' : 'success'; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <p class="mb-1">
                                            Total Item: <?php echo $order['total_items']; ?><br>
                                            Total: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                        </p>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                            Lihat Detail
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pesanan -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showOrderDetails(orderId) {
        const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        const contentDiv = document.getElementById('orderDetailContent');
        
        // Fetch order details
        fetch(`get_order_details.php?order_id=${orderId}`)
            .then(response => response.text())
            .then(html => {
                contentDiv.innerHTML = html;
                modal.show();
            })
            .catch(error => {
                contentDiv.innerHTML = 'Terjadi kesalahan saat memuat detail pesanan.';
            });
    }
    </script>
</body>
</html>
