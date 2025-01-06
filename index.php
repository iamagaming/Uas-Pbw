<?php
session_start();
require_once "config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Cek koneksi database
if(!$db) {
    die("Koneksi database tidak tersedia");
}

// Pastikan direktori upload ada
$upload_dir = "assets/images/products/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Buku Budi - Toko Buku Online</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/animations.css">

    <!-- AOS CSS for scroll animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Swiper CSS for carousels -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
</head>
<body>
    <?php if(!isset($_SESSION['page_loaded'])): ?>
    <!-- Loading Animation -->
    <div class="loading">
        <div class="loading-spinner"></div>
    </div>
    <?php 
        $_SESSION['page_loaded'] = true;
    endif; ?>

    <!-- Animated Background -->
    <div class="animated-bg">
        <ul class="circles">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
    </div>

    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="fade-in-section">Selamat Datang di Toko Buku Budi</h1>
            <p class="lead mb-4 fade-in-section">Temukan koleksi novel terbaik untuk menemani waktu membacamu</p>
            <a href="#featured-products" class="btn btn-primary btn-lg fade-in-section">Lihat Koleksi</a>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics py-5 bg-gradient">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-card fade-in-section">
                        <i class="bi bi-book-half display-4 mb-3"></i>
                        <h3 class="counter">1000+</h3>
                        <p>Judul Buku</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card fade-in-section">
                        <i class="bi bi-people display-4 mb-3"></i>
                        <h3 class="counter">5000+</h3>
                        <p>Pelanggan Setia</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card fade-in-section">
                        <i class="bi bi-star display-4 mb-3"></i>
                        <h3 class="counter">4.8</h3>
                        <p>Rating Toko</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card fade-in-section">
                        <i class="bi bi-truck display-4 mb-3"></i>
                        <h3 class="counter">10000+</h3>
                        <p>Pesanan Selesai</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="categories py-5">
        <div class="container">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <h2 class="fade-in-section">Kategori Buku</h2>
                <a href="categories_list.php" class="btn btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="row">
                <?php
                try {
                    // Ubah query untuk hanya mengambil kolom yang ada
                    $query_categories = "SELECT c.category_id, c.category_name, 
                                       COUNT(p.product_id) as product_count 
                                       FROM categories c 
                                       LEFT JOIN products p ON c.category_id = p.category_id 
                                       GROUP BY c.category_id, c.category_name 
                                       ORDER BY product_count DESC 
                                       LIMIT 6";
                    $stmt_categories = $db->prepare($query_categories);
                    $stmt_categories->execute();
                    
                    while ($category = $stmt_categories->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="category-card text-center" data-aos="fade-up">
                        <a href="products.php?category=<?php echo $category['category_id']; ?>" 
                           class="text-decoration-none">
                            <div class="card category-item">
                                <div class="card-body">
                                    <div class="category-image-wrapper">
                                        <i class="bi bi-book fs-1 category-icon"></i>
                                    </div>
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <span class="badge bg-primary">
                                            <?php echo $category['product_count']; ?> Buku
                                        </span>
                                    </p>
                                    <div class="category-hover">
                                        <span class="btn btn-outline-primary btn-sm">Lihat Semua</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <?php 
                    endwhile;
                } catch(PDOException $e) {
                    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Latest Arrivals -->
    <section class="latest-arrivals py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4 fade-in-section">Baru Datang</h2>
            <div class="row">
                <?php
                try {
                    $query_latest = "SELECT p.product_id, p.title, p.author, p.price, p.description, p.image_url,
                                    c.category_name 
                                    FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.category_id 
                                    ORDER BY p.created_at DESC LIMIT 4";
                    $stmt_latest = $db->prepare($query_latest);
                    $stmt_latest->execute();
                    
                    while ($product = $stmt_latest->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="col-md-3 mb-4">
                    <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="text-decoration-none">
                        <div class="card product-card h-100 fade-in-section">
                            <div class="card-img-wrapper">
                                <div class="product-image">
                                    <?php 
                                    if (!empty($product['image_url'])) {
                                        $image_path = "assets/images/products/" . $product['image_url'];
                                        if (file_exists($image_path)) {
                                            ?>
                                            <img src="<?php echo $image_path; ?>" 
                                                 class="card-img-top" 
                                                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                                                 onerror="this.src='assets/images/no-image.jpg';">
                                            <?php
                                        } else {
                                            ?>
                                            <div class="no-image-placeholder">
                                                <i class="bi bi-book fs-1"></i>
                                                <p class="mt-2 mb-0">No Image</p>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="no-image-placeholder">
                                            <i class="bi bi-book fs-1"></i>
                                            <p class="mt-2 mb-0">No Image</p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php if (!empty($product['category_name'])): ?>
                                    <div class="category-badge">
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <?php if (!empty($product['author'])): ?>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($product['author']); ?></p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                    <span class="btn btn-outline-primary btn-sm">Detail</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php 
                    endwhile;
                } catch(PDOException $e) {
                    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials py-5">
        <div class="container">
            <h2 class="text-center mb-5 fade-in-section">Apa Kata Mereka?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card fade-in-section">
                        <div class="testimonial-content">
                            <i class="bi bi-quote display-4 mb-3"></i>
                            <p class="mb-4">Koleksi bukunya lengkap dan update. Pengiriman cepat dan buku selalu dalam kondisi baik.</p>
                            <div class="testimonial-author">
                                <h5>John Doe</h5>
                                <p class="text-muted">Pembaca Setia</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card fade-in-section">
                        <div class="testimonial-content">
                            <i class="bi bi-quote display-4 mb-3"></i>
                            <p class="mb-4">Harga bersaing dan sering ada promo menarik. Customer service ramah dan responsif.</p>
                            <div class="testimonial-author">
                                <h5>Jane Smith</h5>
                                <p class="text-muted">Pecinta Novel</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card fade-in-section">
                        <div class="testimonial-content">
                            <i class="bi bi-quote display-4 mb-3"></i>
                            <p class="mb-4">Website mudah digunakan dan proses pembelian sangat simpel. Recommended!</p>
                            <div class="testimonial-author">
                                <h5>Mike Johnson</h5>
                                <p class="text-muted">Kolektor Buku</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter py-5 bg-gradient">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h2 class="mb-4 fade-in-section">Dapatkan Info Terbaru</h2>
                    <p class="mb-4 fade-in-section">Berlangganan newsletter kami untuk mendapatkan update terbaru tentang buku dan promo menarik!</p>
                    <form class="newsletter-form fade-in-section">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Masukkan email Anda">
                            <button class="btn btn-primary" type="submit">Berlangganan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured-products" class="py-5">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="mb-4 fade-in-section">Produk Unggulan</h2>
            </div>
            <div class="product-grid">
                <?php
                try {
                    $query = "SELECT p.product_id, p.title, p.author, p.price, p.description, p.image_url,
                             c.category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.category_id 
                             ORDER BY p.created_at DESC LIMIT 8";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    
                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="product-card">
                    <div class="card h-100">
                        <div class="card-img-wrapper">
                            <div class="product-image">
                                <?php 
                                $image_path = !empty($product['image_url']) ? 
                                    "assets/images/products/" . $product['image_url'] : 
                                    "assets/images/no-image.jpg";
                                    
                                if (!empty($product['image_url']) && file_exists("assets/images/products/" . $product['image_url'])) : 
                                ?>
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                                         onerror="this.src='assets/images/no-image.jpg'">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <i class="bi bi-book fs-1"></i>
                                        <p class="mt-2 mb-0">No Image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($product['category_name'])): ?>
                                <div class="category-badge">
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate">
                                <?php echo htmlspecialchars($product['title']); ?>
                            </h5>
                            <?php if (!empty($product['author'])): ?>
                                <p class="card-subtitle mb-2 text-muted">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo htmlspecialchars($product['author']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="card-text text-muted flex-grow-1">
                                <?php 
                                $description = !empty($product['description']) ? $product['description'] : 'Tidak ada deskripsi';
                                echo htmlspecialchars(substr($description, 0, 100)) . '...'; 
                                ?>
                            </p>
                            <div class="card-footer bg-transparent border-0 mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price fw-bold">
                                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </span>
                                    <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                } catch(PDOException $e) {
                    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Footer Scripts -->
    <!-- jQuery first, then Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS JavaScript -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Swiper JavaScript -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Initialize Swiper for testimonials
        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });

        // Counter Animation
        function animateCounter(element) {
            const target = parseInt(element.textContent);
            let count = 0;
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps

            const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(count);
                }
            }, 16);
        }

        // Intersection Observer for counter animation
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.counter');
                    counters.forEach(counter => animateCounter(counter));
                    counterObserver.unobserve(entry.target);
                }
            });
        });

        // Observe statistics section
        const statsSection = document.querySelector('.statistics');
        if (statsSection) {
            counterObserver.observe(statsSection);
        }

        // Newsletter Form Handling
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = this.querySelector('input[type="email"]').value;
                // Add your newsletter subscription logic here
                alert('Terima kasih telah berlangganan newsletter kami!');
                this.reset();
            });
        }

        // Loading Animation
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loading');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html> 