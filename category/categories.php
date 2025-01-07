<?php
session_start();
require_once "config/database.php";


$database = new Database();
$db = $database->getConnection();

if(isset($_POST['delete']) && isset($_POST['category_id'])) {
    
    $check_query = "SELECT COUNT(*) FROM products WHERE category_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$_POST['category_id']]);
    $count = $check_stmt->fetchColumn();

    if($count == 0) {
        $query = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_POST['category_id']]);
        $_SESSION['success'] = "Kategori berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Kategori tidak dapat dihapus karena masih digunakan oleh produk.";
    }
    header('Location: categories.php');
    exit;
}


$query = "SELECT c.*, COUNT(p.product_id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.category_id = p.category_id 
          GROUP BY c.category_id 
          ORDER BY c.category_name";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Kategori</h2>
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
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo $row['product_count']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="category_form.php?id=<?php echo $row['category_id']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                    <input type="hidden" name="category_id" value="<?php echo $row['category_id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 