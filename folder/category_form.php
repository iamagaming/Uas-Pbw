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

$category = [
    'category_id' => '',
    'category_name' => '',
    'description' => '',
    'image_url' => ''
];

// Edit mode
if(isset($_GET['id'])) {
    $query = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$category) {
        header('Location: categories.php');
        exit;
    }
}

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $category_name = trim($_POST['category_name']);
        $description = trim($_POST['description']);
        $image_url = $category['image_url'];

        // Handle image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Delete old image if exists
            if($image_url) {
                $upload_helper->delete('../' . $image_url);
            }
            
            // Upload new image
            $image_path = $upload_helper->upload($_FILES['image'], 'category');
            $image_url = str_replace('../', '', $image_path);
        }

        // Handle image removal
        if(isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if($image_url) {
                $upload_helper->delete('../' . $image_url);
                $image_url = null;
            }
        }

        if(isset($_POST['category_id']) && $_POST['category_id']) {
            // Update existing category
            $query = "UPDATE categories SET 
                     category_name = ?, description = ?, image_url = ?
                     WHERE category_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $category_name, $description, $image_url,
                $_POST['category_id']
            ]);
            $_SESSION['success'] = "Kategori berhasil diperbarui.";
        } else {
            // Insert new category
            $query = "INSERT INTO categories (category_name, description, image_url) 
                     VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $category_name, $description, $image_url
            ]);
            $_SESSION['success'] = "Kategori baru berhasil ditambahkan.";
        }
        
        header('Location: categories.php');
        exit;

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

$page_title = ($category['category_id'] ? 'Edit' : 'Tambah') . ' Kategori';
include 'includes/admin_header.php';
?>

<?php include 'includes/admin_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $category['category_id'] ? 'Edit' : 'Tambah'; ?> Kategori</h1>
                <a href="categories.php" class="btn btn-secondary">Kembali</a>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" 
                                   value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Icon Kategori</label>
                            <?php if($category['image_url']): ?>
                                <div class="product-images">
                                    <div class="product-image-item">
                                        <img src="../<?php echo $category['image_url']; ?>" 
                                             alt="Current image" class="image-preview">
                                        <input type="hidden" name="current_image" 
                                               value="<?php echo $category['image_url']; ?>">
                                        <button type="button" class="btn btn-danger btn-sm delete-image" 
                                                onclick="if(confirm('Hapus foto ini?')) document.getElementById('remove_image').value='1';">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <input type="hidden" name="remove_image" id="remove_image" value="0">
                            <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 5MB</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

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

// Preview image before upload
document.getElementById('image').onchange = function(e) {
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.createElement('div');
            preview.className = 'product-images';
            preview.innerHTML = `
                <div class="product-image-item">
                    <img src="${e.target.result}" class="image-preview">
                    <button type="button" class="btn btn-danger btn-sm delete-image" onclick="removePreview(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            
            var existingPreview = document.querySelector('.product-images');
            if (existingPreview) {
                existingPreview.replaceWith(preview);
            } else {
                this.parentElement.insertBefore(preview, this.nextSibling);
            }
        }.bind(this);
        reader.readAsDataURL(this.files[0]);
    }
}

function removePreview(button) {
    button.closest('.product-images').remove();
    document.getElementById('image').value = '';
    document.getElementById('remove_image').value = '1';
}
</script>
</body>
</html> 