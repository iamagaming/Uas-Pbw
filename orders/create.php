<?php
require_once '../templates/header.php';
require_once '../models/Order.php';
require_once '../models/Customer.php';
require_once '../models/Product.php';

$order = new Order();
$customer = new Customer();
$product = new Product();

$customers = $customer->findAll();
$products = $product->findAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = [];
    foreach ($_POST['items'] as $item) {
        if (!empty($item['product_id']) && !empty($item['quantity'])) {
            $items[] = [
                'product_id' => $item['product_id'],
                'quantity' => (int)$item['quantity']
            ];
        }
    }

    $data = [
        'customer_id' => $_POST['customer_id'],
        'items' => $items,
        'notes' => $_POST['notes']
    ];

    $result = $order->createOrder($data);
    
    if ($result->getInsertedCount() > 0) {
        header('Location: index.php');
        exit;
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New Order</h3>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate onsubmit="return validateForm()">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Select a customer</option>
                            <?php foreach ($customers as $item): ?>
                            <option value="<?php echo $item->_id; ?>">
                                <?php echo htmlspecialchars($item->name); ?> (<?php echo htmlspecialchars($item->email); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a customer.</div>
                    </div>

                    <div id="order-items">
                        <h4 class="mb-3">Order Items</h4>
                        <div class="order-item mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Product</label>
                                    <select class="form-select" name="items[0][product_id]" required>
                                        <option value="">Select a product</option>
                                        <?php foreach ($products as $item): ?>
                                        <option value="<?php echo $item->_id; ?>" data-price="<?php echo $item->price; ?>">
                                            <?php echo htmlspecialchars($item->title); ?> ($<?php echo number_format($item->price, 2); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control" name="items[0][quantity]" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger d-block w-100" onclick="removeOrderItem(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary" onclick="addOrderItem()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Order Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Order
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

<script>
let itemCount = 1;

function addOrderItem() {
    const template = `
        <div class="order-item mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Product</label>
                    <select class="form-select" name="items[${itemCount}][product_id]" required>
                        <option value="">Select a product</option>
                        <?php foreach ($products as $item): ?>
                        <option value="<?php echo $item->_id; ?>" data-price="<?php echo $item->price; ?>">
                            <?php echo htmlspecialchars($item->title); ?> ($<?php echo number_format($item->price, 2); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="items[${itemCount}][quantity]" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger d-block w-100" onclick="removeOrderItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('order-items').insertAdjacentHTML('beforeend', template);
    itemCount++;
}

function removeOrderItem(button) {
    button.closest('.order-item').remove();
}
</script>

<?php require_once '../templates/footer.php'; ?> 