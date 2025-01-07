<?php
session_start();
require_once "../config/database.php";
require_once "includes/upload_helper.php";

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$upload_helper = new UploadHelper('../assets/images/products');

// Delete product
if(isset($_POST['delete']) && isset($_POST['product_id'])) {
    $query = "SELECT image_url FROM products WHERE product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete image if exists
    if($product['image_url']) {
        $upload_helper->delete('../' . $product['image_url']);
    }

    // Delete product
    $query = "DELETE FROM products WHERE product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['product_id']]);
    
    $_SESSION['success'] = "Produk berhasil dihapus.";
    header('Location: products.php');
    exit;
}

// Get all products
$query = "SELECT p.*, c.category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Produk";
include 'includes/admin_header.php';
?>

<?php include 'includes/admin_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manajemen Produk</h1>
                <a href="product_form.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Produk
                </a>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td>
                                <?php if($product['image_url']): ?>
                                    <img src="../<?php echo $product['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                         class="thumbnail">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['title']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($product['stock'] <= 5): ?>
                                    <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                                <?php else: ?>
                                    <?php echo $product['stock']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="product_form.php?id=<?php echo $product['product_id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview image before upload
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = this.parentElement.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'image-preview';
                    this.parentElement.insertBefore(preview, this);
                }
                preview.src = e.target.result;
            }.bind(this);
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
</body>
</html> 