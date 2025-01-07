<?php
require_once '../config/config.php';
require_once '../models/Product.php';
require_once '../models/Category.php';

// Check authentication
if (!is_logged_in()) {
    redirect('auth/login.php');
}

$product = new Product();
$category = new Category();

if (!isset($_GET['id'])) {
    redirect('products/index.php');
}

$id = $_GET['id'];
$productData = $product->findById($id);

if (!$productData) {
    redirect('products/index.php');
}

// Get all active categories for dropdown
$categories = $category->getActiveCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'author' => $_POST['author'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'stock' => $_POST['stock'],
        'category_id' => $_POST['category_id'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if ($product->updateProduct($id, $data, $_FILES['image'] ?? null)) {
        $_SESSION['success'] = "Product updated successfully.";
        redirect('products/index.php');
    } else {
        $error = "Failed to update product.";
    }
}

// Include header
include_once '../templates/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Product</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($productData->title); ?>" required>
                            <div class="invalid-feedback">Please enter a title.</div>
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" class="form-control" id="author" name="author" 
                                   value="<?php echo htmlspecialchars($productData->author); ?>" required>
                            <div class="invalid-feedback">Please enter an author name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->id; ?>" 
                                            <?php echo $productData->category_id == $cat->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($productData->description); ?></textarea>
                            <div class="invalid-feedback">Please enter a description.</div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <?php if ($productData->image): ?>
                                <div class="mb-2">
                                    <img src="../uploads/products/<?php echo $productData->image; ?>" 
                                         alt="<?php echo htmlspecialchars($productData->title); ?>"
                                         class="img-thumbnail"
                                         style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Upload a new product image (optional). Leave empty to keep the current image.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="price" name="price" required
                                               value="<?php echo number_format($productData->price, 0, ',', '.'); ?>"
                                               oninput="formatRupiah(this)">
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid price.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" 
                                           min="0" value="<?php echo $productData->stock; ?>" required>
                                    <div class="invalid-feedback">Please enter a valid stock amount.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo $productData->is_active ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript for Rupiah formatting -->
<script>
function formatRupiah(input) {
    // Remove non-digit characters
    let value = input.value.replace(/\D/g, '');
    
    // Format the number with dots
    if (value !== '') {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    // Update the input value
    input.value = value;
}
</script>

<?php include_once '../templates/footer.php'; ?> 