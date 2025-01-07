<?php
session_start();
require_once "config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buat direktori untuk gambar kategori jika belum ada
$upload_dir = "assets/images/categories/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Query untuk mengambil kategori dengan error handling
try {
    $query = "SELECT c.category_id, c.category_name, c.description, 
             COUNT(p.product_id) as book_count 
             FROM categories c 
             LEFT JOIN products p ON c.category_id = p.category_id 
             GROUP BY c.category_id, c.category_name, c.description 
             ORDER BY c.category_name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
} catch(PDOException $e) {
    die("Error query: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Buku - Novel Budiono</title>
    
    <!-- CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-small py-5">
        <div class="container text-center">
            <h1 class="fade-in-section">Kategori Buku</h1>
            <p class="lead">Temukan buku favoritmu berdasarkan kategori</p>
        </div>
    </section>

    <!-- Setelah section hero, tambahkan tombol Tambah Kategori -->
    <section class="categories-grid py-5">
        <div class="container">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Tambah tombol Add Category -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Daftar Kategori</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle"></i> Tambah Kategori
                </button>
            </div>

            <!-- Modal Tambah Kategori -->
            <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Kategori Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="add_category.php" method="POST" class="needs-validation" novalidate>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Nama Kategori</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                                    <div class="invalid-feedback">
                                        Nama kategori harus diisi
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing categories grid code -->
            <div class="row">
                <?php while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 col-lg-3 mb-4" data-aos="fade-up">
                    <div class="category-card">
                        <a href="products.php?category=<?php echo $category['category_id']; ?>" 
                           class="text-decoration-none">
                            <div class="card category-item h-100">
                                <div class="card-body text-center">
                                    <div class="category-image-wrapper">
                                        <i class="bi bi-book fs-1 category-icon"></i>
                                    </div>
                                    
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </h5>
                                    
                                    <p class="card-text">
                                        <span class="badge bg-primary">
                                            <?php echo $category['book_count']; ?> Buku
                                        </span>
                                    </p>
                                    
                                    <?php if (!empty($category['description'])): ?>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars($category['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="category-hover">
                                        <span class="btn btn-outline-primary btn-sm">Lihat Koleksi</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html> 