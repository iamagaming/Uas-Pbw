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

// Get filter parameters
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$author = isset($_GET['author']) ? $_GET['author'] : '';

// Get all categories for filter
$categories = $category->getActiveCategories();

// Get products with filters
if ($author) {
    $products = $product->searchByAuthor($author);
} elseif ($category_id && $search) {
    $products = $product->findAll(
        'category_id = ? AND (title LIKE ? OR description LIKE ?)', 
        [$category_id, "%$search%", "%$search%"]
    );
} elseif ($category_id) {
    $products = $product->findAll('category_id = ?', [$category_id]);
} elseif ($search) {
    $products = $product->findAll(
        'title LIKE ? OR description LIKE ? OR author LIKE ?', 
        ["%$search%", "%$search%", "%$search%"]
    );
} else {
    $products = $product->getAllWithCategory();
}

// Include header
include_once '../templates/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Products</h2>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>" <?php echo $category_id == $cat->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="author" class="form-label">Author</label>
                    <input type="text" class="form-control" id="author" name="author" 
                           value="<?php echo htmlspecialchars($author); ?>" 
                           placeholder="Search by author...">
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by title or description...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No products found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (isset($item->image) && $item->image && file_exists("../uploads/products/{$item->image}")): ?>
                                            <img src="../uploads/products/<?php echo $item->image; ?>" 
                                                 alt="<?php echo htmlspecialchars($item->title); ?>"
                                                 class="img-thumbnail"
                                                 style="max-width: 50px;">
                                        <?php else: ?>
                                            <img src="https://dummyimage.com/50x50/dee2e6/6c757d.jpg" 
                                                 alt="No Image"
                                                 class="img-thumbnail"
                                                 style="max-width: 50px;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $item->id; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($item->title); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="?author=<?php echo urlencode($item->author); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($item->author); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($item->category_name ?? 'Uncategorized'); ?></td>
                                    <td><?php echo $product->formatToRupiah($item->price); ?></td>
                                    <td>
                                        <?php if ($item->stock <= 5): ?>
                                            <span class="text-danger fw-bold"><?php echo $item->stock; ?></span>
                                        <?php else: ?>
                                            <?php echo $item->stock; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item->is_active): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $item->id; ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $item->id; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this product?')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?> 