<?php
session_start();
require_once "../config/database.php";

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'customers' => $db->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
    'orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'revenue' => $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn()
];

// Get recent orders
$query = "SELECT o.*, c.name as customer_name 
          FROM orders o 
          JOIN customers c ON o.customer_id = c.customer_id 
          ORDER BY o.order_date DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get low stock products
$query = "SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Dashboard</h1>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Produk</h5>
                                <h2 class="card-text"><?php echo $stats['products']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Pelanggan</h5>
                                <h2 class="card-text"><?php echo $stats['customers']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Total Pesanan</h5>
                                <h2 class="card-text"><?php echo $stats['orders']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Pendapatan</h5>
                                <h2 class="card-text">Rp <?php echo number_format($stats['revenue'], 0, ',', '.'); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Pesanan Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Pelanggan</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$order['status']]; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Stok Menipis</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($low_stock as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        <?php echo $product['stock']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 