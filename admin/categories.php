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
$upload_helper = new UploadHelper('../assets/images/categories');

// Delete category
if(isset($_POST['delete']) && isset($_POST['category_id'])) {
    // Check if category is being used
    $query = "SELECT COUNT(*) FROM products WHERE category_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['category_id']]);
    $count = $stmt->fetchColumn();

    if($count > 0) {
        $_SESSION['error'] = "Kategori tidak dapat dihapus karena masih digunakan oleh produk.";
    } else {
        // Get category image
        $query = "SELECT image_url FROM categories WHERE category_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_POST['category_id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        // Delete image if exists
        if($category['image_url']) {
            $upload_helper->delete('../' . $category['image_url']);
        }

        // Delete category
        $query = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_POST['category_id']]);
        
        $_SESSION['success'] = "Kategori berhasil dihapus.";
    }
    header('Location: categories.php');
    exit;
}

// Get all categories with product count
$query = "SELECT c.*, COUNT(p.product_id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.category_id = p.category_id 
          GROUP BY c.category_id 
          ORDER BY c.category_name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Kategori";
include 'includes/admin_header.php';
?>

<?php include 'includes/admin_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manajemen Kategori</h1>
                <a href="category_form.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Kategori
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

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['category_id']; ?></td>
                            <td>
                                <?php if($category['image_url']): ?>
                                    <img src="../<?php echo $category['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($category['category_name']); ?>" 
                                         class="thumbnail">
                                <?php else: ?>
                                    <span class="text-muted">No icon</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo $category['product_count']; ?> produk
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="category_form.php?id=<?php echo $category['category_id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
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
</body>
</html> 