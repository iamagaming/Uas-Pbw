<?php
session_start();
include_once 'config/database.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    echo "Akses ditolak";
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get order items
$query = "SELECT oi.*, p.title, p.image 
          FROM order_items oi
          JOIN products p ON oi.product_id = p.id
          JOIN orders o ON oi.order_id = o.id
          WHERE oi.order_id = :order_id AND o.customer_id = :customer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $_GET['order_id']);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($items)) {
    echo "Pesanan tidak ditemukan";
    exit();
}
?>

<div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $item['image'] ? '../uas_reno/img/' . $item['image'] : 'assets/images/no-image.jpg'; ?>" 
                                 alt="<?php echo $item['title']; ?>"
                                 style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                            <?php echo $item['title']; ?>
                        </div>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 