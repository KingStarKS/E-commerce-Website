<?php
session_start();
include 'includes/db.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Helper to build cart key from id and size
function cart_key($id, $size) {
    return $id . '_' . $size;
}

// Handle cart actions (add, remove, clear, update quantity)
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header("Location: cart.php");
        exit;
    }

    // For add, remove, update require id and size
    if (isset($_GET['id']) && isset($_GET['size'])) {
        $id = intval($_GET['id']);
        $size = $_GET['size'];
        $key = cart_key($id, $size);

        switch ($action) {
            case 'add':
                $qtyToAdd = 1;
                if (isset($_GET['quantity'])) {
                    $qtyToAdd = intval($_GET['quantity']);
                    if ($qtyToAdd < 1) $qtyToAdd = 1;
                }
                if (isset($_SESSION['cart'][$key])) {
                    $_SESSION['cart'][$key] += $qtyToAdd;
                } else {
                    $_SESSION['cart'][$key] = $qtyToAdd;
                }
                header("Location: cart.php");
                exit;

            case 'remove':
                if (isset($_SESSION['cart'][$key])) {
                    unset($_SESSION['cart'][$key]);
                }
                header("Location: cart.php");
                exit;

            case 'update':
                if (isset($_POST['quantity'])) {
                    $newQty = intval($_POST['quantity']);
                    if ($newQty <= 0) {
                        unset($_SESSION['cart'][$key]);
                    } else {
                        $_SESSION['cart'][$key] = $newQty;
                    }
                }
                header("Location: cart.php");
                exit;
        }
    }
}

// Prepare cart items from session and fetch details
$cart_items = [];
$cart_total = 0;
$stockWarnings = false;

if (!empty($_SESSION['cart'])) {
    $products_map = [];

    foreach ($_SESSION['cart'] as $key => $quantity) {
        list($pid, $psize) = explode('_', $key);
        $products_map[$key] = ['id' => intval($pid), 'size' => $psize, 'quantity' => $quantity];
    }

    $ids = array_unique(array_column($products_map, 'id'));
    $ids_str = implode(',', $ids);

    $sql = "SELECT * FROM products WHERE id IN ($ids_str)";
    $result = mysqli_query($conn, $sql);

    $products_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products_data[$row['id']] = $row;
    }

    // Fetch stock per product+size from product_sizes
    $sizeStocks = [];
    $stmtSizes = $conn->prepare("SELECT product_id, size, stock FROM product_sizes WHERE product_id IN ($ids_str)");
    $stmtSizes->execute();
    $resSizes = $stmtSizes->get_result();
    while ($r = $resSizes->fetch_assoc()) {
        $sizeStocks[$r['product_id']][$r['size']] = intval($r['stock']);
    }
    $stmtSizes->close();

    foreach ($products_map as $key => $item) {
        $id = $item['id'];
        $size = $item['size'];
        $quantity = $item['quantity'];

        if (!isset($products_data[$id])) {
            continue;
        }

        $product = $products_data[$id];
        $availableStock = $sizeStocks[$id][$size] ?? 0;

        // Check stock against quantity in cart
        if ($quantity > $availableStock) {
            $quantity = $availableStock; // reduce quantity to max available
            $_SESSION['cart'][$key] = $quantity; // update session cart
            $stockWarnings = true;
        }

        $subtotal = $product['price'] * $quantity;
        $cart_total += $subtotal;

        $cart_items[] = [
            'key' => $key,
            'id' => $id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'size' => $size,
            'subtotal' => $subtotal,
            'available_stock' => $availableStock,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Your Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <h2>Your Shopping Cart</h2>

    <?php if ($stockWarnings): ?>
        <div class="alert alert-warning">
            Some items had their quantities adjusted due to stock limits.
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">Your cart is empty.</div>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Price ($)</th>
                    <th>Subtotal ($)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['size']) ?></td>
                        <td>
                            <form method="POST" action="cart.php?action=update&id=<?= $item['id'] ?>&size=<?= urlencode($item['size']) ?>" style="margin:0;">
                                <input type="number" 
                                       name="quantity" 
                                       value="<?= $item['quantity'] ?>" 
                                       min="1" 
                                       max="<?= $item['available_stock'] ?>" 
                                       class="form-control" 
                                       style="width: 80px;"
                                       onchange="this.form.submit();" />
                            </form>
                        </td>
                        <td><?= number_format($item['price'], 2) ?></td>
                        <td><?= number_format($item['subtotal'], 2) ?></td>
                        <td>
                            <a href="cart.php?action=remove&id=<?= $item['id'] ?>&size=<?= urlencode($item['size']) ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" class="text-end"><strong>Grand Total</strong></td>
                    <td colspan="2"><strong><?= number_format($cart_total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
        <a href="checkout.php" class="btn btn-success <?= $cart_total > 0 ? '' : 'disabled' ?>">Proceed to Checkout</a>
        <a href="cart.php?action=clear" class="btn btn-warning float-end">Clear Cart</a>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
