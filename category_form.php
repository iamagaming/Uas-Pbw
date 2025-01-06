<?php
session_start();
require_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

$category = [
    'category_id' => '',
    'category_name' => '',
    'description' => ''
];

// Jika mode edit
if(isset($_GET['id'])) {
    $query = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Proses form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']);
    $description = trim($_POST['description']);

    if(empty($category_name)) {
        $_SESSION['error'] = "Nama kategori harus diisi.";
    } else {
        if(isset($_POST['category_id']) && $_POST['category_id']) {
            // Update existing category
            $query = "UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$category_name, $description, $_POST['category_id']]);
            $_SESSION['success'] = "Kategori berhasil diperbarui.";
        } else {
            // Insert new category
            $query = "INSERT INTO categories (category_name, description) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$category_name, $description]);
            $_SESSION['success'] = "Kategori baru berhasil ditambahkan.";
        }
        header('Location: categories.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category['category_id'] ? 'Edit' : 'Tambah'; ?> Kategori - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h2><?php echo $category['category_id'] ? 'Edit' : 'Tambah'; ?> Kategori</h2>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
            
            <div class="mb-3">
                <label for="category_name" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="category_name" name="category_name" 
                       value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                <div class="invalid-feedback">
                    Nama kategori harus diisi.
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="categories.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <!-- Include footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html> 