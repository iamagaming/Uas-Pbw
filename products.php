<?php
session_start();
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - Toko Buku Budi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Produk</h2>
            <a href="add_product.php" class="btn btn-primary">Tambah Produk</a>
        </div>

        <div class="row">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php
                        // Ubah path gambar untuk mengambil dari folder uas_reno
                        $imagePath = $row['image'] ? '../uas_reno/img/' . $row['image'] : 'assets/images/no-image.jpg';
                        // Periksa apakah file gambar ada
                        if (!file_exists($imagePath)) {
                            $imagePath = 'assets/images/no-image.jpg';
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>"
                             onerror="this.src='assets/images/no-image.jpg'">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text">
                                <strong>Penulis:</strong> <?php echo htmlspecialchars($row['author']); ?><br>
                                <strong>Kategori:</strong> <?php echo htmlspecialchars($row['category_name']); ?><br>
                                <strong>Harga:</strong> Rp <?php echo number_format($row['price'], 0, ',', '.'); ?><br>
                                <strong>Stok:</strong> <?php echo $row['stock']; ?>
                            </p>
                            <div class="btn-group">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a>
                                <button onclick="confirmDelete(<?php echo $row['id']; ?>, 'product')" class="btn btn-danger">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 