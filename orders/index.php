<?php
require_once '../templates/header.php';
require_once '../models/Order.php';

$order = new Order();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get orders based on filters
if ($search) {
    $orders = $order->search($search);
} else {
    $orders = $order->findAll();
}

// Filter by status if specified
if ($status) {
    $orders = array_filter($orders, function($item) use ($status) {
        return $item->status === $status;
    });
}

// Status options for filter
$statusOptions = ['pending', 'processing', 'completed', 'cancelled'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Orders</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Order
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusOptions as $option): ?>
                        <option value="<?php echo $option; ?>" <?php echo $status === $option ? 'selected' : ''; ?>>
                            <?php echo ucfirst($option); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $item): ?>
                    <?php $customer = $order->getCustomerDetails($item->customer_id); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item->order_number); ?></td>
                        <td><?php echo htmlspecialchars($customer->name); ?></td>
                        <td>$<?php echo number_format($item->total_amount, 2); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($item->status) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($item->status); ?>
                            </span>
                        </td>
                        <td><?php echo $item->created_at->toDateTime()->format('Y-m-d H:i'); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $item->_id; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($item->status === 'pending'): ?>
                            <a href="edit.php?id=<?php echo $item->_id; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (in_array($item->status, ['pending', 'processing'])): ?>
                            <a href="update-status.php?id=<?php echo $item->_id; ?>&status=cancelled" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to cancel this order?')"
                               data-bs-toggle="tooltip" 
                               title="Cancel">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No orders found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?> 