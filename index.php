<?php
session_start();
include_once 'config/database.php';


$database = new Database();
$db = $database->getConnection();


if(!$db) {
    die("Koneksi database gagal!");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Buku Budi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Toko Buku Budi</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Kategori</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Keluar</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section py-5 text-center text-white bg-primary">
        <div class="container">
            <h1>Selamat Datang di Toko Buku Budi</h1>
            <p class="lead">Temukan berbagai koleksi buku terbaik untuk Anda</p>
        </div>
    </div>

    <!-- Content -->
    <div class="container my-5">
        <div class="row">
            <?php
            try {
                // Get featured products
                $query = "SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          ORDER BY p.id DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                
                if($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        // Ubah path gambar untuk mengambil dari folder uas_reno
                        $imagePath = $row['image'] ? '../uas_reno/img/' . $row['image'] : 'assets/images/no-image.jpg';
                        // Periksa apakah file gambar ada
                        if (!file_exists($imagePath)) {
                            $imagePath = 'assets/images/no-image.jpg';
                        }
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
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
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">Belum ada produk yang ditampilkan.</div></div>';
                }
            } catch(PDOException $e) {
                echo '<div class="col-12"><div class="alert alert-danger">Terjadi kesalahan: ' . $e->getMessage() . '</div></div>';
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Tentang Toko Buku Budi</h5>
                    <p>Toko buku terlengkap dengan koleksi buku terbaik untuk Anda.</p>
                </div>
                <div class="col-md-6">
                    <h5>Kontak</h5>
                    <p>Email: info@tokobukubudi.com<br>
                    Telp: (021) 1234567</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 