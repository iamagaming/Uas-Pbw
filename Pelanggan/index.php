<?php
require_once '../templates/header.php';
require_once '../models/Customer.php';

$customer = new Customer();
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    $customers = $customer->search($search);
} else {
    $customers = $customer->findAll();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customers</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Customer
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item->name); ?></td>
                        <td><?php echo htmlspecialchars($item->email); ?></td>
                        <td><?php echo htmlspecialchars($item->phone); ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo $customer->getOrderCount($item->_id); ?>
                            </span>
                        </td>
                        <td>
                            $<?php echo number_format($customer->getTotalSpent($item->_id), 2); ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $item->_id; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $item->_id; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure? This will not delete associated orders.')" 
                               data-bs-toggle="tooltip" 
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No customers found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?> 