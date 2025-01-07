<?php
require_once '../templates/header.php';
require_once '../models/Order.php';

$order = new Order();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$orderData = $order->findById($id);

if (!$orderData) {
    header('Location: index.php');
    exit;
}

$customer = $order->getCustomerDetails($orderData->customer_id);
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Order Details</h3>
                <div class="btn-group">
                    <?php if ($orderData->status === 'pending'): ?>
                    <a href="update-status.php?id=<?php echo $orderData->_id; ?>&status=processing" class="btn btn-success">
                        <i class="fas fa-check"></i> Process Order
                    </a>
                    <?php endif; ?>
                    <?php if ($orderData->status === 'processing'): ?>
                    <a href="update-status.php?id=<?php echo $orderData->_id; ?>&status=completed" class="btn btn-success">
                        <i class="fas fa-check-double"></i> Complete Order
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Order Information</h5>
                        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($orderData->order_number); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php 
                                echo match($orderData->status) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($orderData->status); ?>
                            </span>
                        </p>
                        <p><strong>Date:</strong> <?php echo $orderData->created_at->toDateTime()->format('Y-m-d H:i'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer->name); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer->email); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer->phone); ?></p>
                    </div>
                </div>

                <h5>Order Items</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderData->items as $item): ?>
                            <?php $product = $order->getProductDetails($item->product_id); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product->title); ?></td>
                                <td>$<?php echo number_format($item->price, 2); ?></td>
                                <td><?php echo $item->quantity; ?></td>
                                <td class="text-end">$<?php echo number_format($item->subtotal, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($orderData->total_amount, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($orderData->notes)): ?>
                <div class="mt-4">
                    <h5>Order Notes</h5>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($orderData->notes)); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?> 