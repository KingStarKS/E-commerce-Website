<?php
session_start();
include 'includes/db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Prepare cart items
$cart_items = [];
$grandTotal = 0;

// The cart keys are like "productid_size", e.g., "12_M"
$productIds = [];
foreach ($_SESSION['cart'] as $key => $quantity) {
    list($pid, $size) = explode('_', $key);
    $productIds[] = intval($pid);
}
$productIds = array_unique($productIds);
if (empty($productIds)) {
    // no valid products
    header('Location: cart.php');
    exit;
}

$ids_str = implode(',', $productIds);
$sql = "SELECT * FROM products WHERE id IN ($ids_str)";
$result = mysqli_query($conn, $sql);

// Build product data map
$products_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products_data[$row['id']] = $row;
}

// Build cart items array with quantity, size, subtotal
foreach ($_SESSION['cart'] as $key => $quantity) {
    list($pid, $size) = explode('_', $key);
    $pid = intval($pid);

    if (!isset($products_data[$pid])) continue;

    $product = $products_data[$pid];
    $subtotal = $product['price'] * $quantity;
    $grandTotal += $subtotal;

    $cart_items[] = [
        'id' => $pid,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'size' => $size,
        'subtotal' => $subtotal,
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- PayPal SDK with your client ID -->
    <script src="https://www.paypal.com/sdk/js?client-id=AYkM-GQlSRQl-VFalE73hLib3d4L6clpI6uXuqxdAB1GIYNfVJ9JU4cYov-mBsj2DjawJ4iCljjZyB_C&currency=USD"></script>
</head>
<body>

<div class="container mt-5">
    <h2>Checkout</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th><th>Size</th><th>Qty</th><th>Price</th><th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4"><strong>Grand Total</strong></td>
                <td><strong>$<?= number_format($grandTotal, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- PayPal Button -->
    <div id="paypal-button-container"></div>
</div>

<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?= number_format($grandTotal, 2, '.', '') ?>'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            // Save order to backend
            fetch('payment-success.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    orderID: data.orderID,
                    payerID: data.payerID,
                    details: details
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.href = "order-confirmation.php?order_id=" + data.order_id;
                  } else {
                      alert('Payment succeeded, but order saving failed.');
                  }
              });
        });
    }
}).render('#paypal-button-container');
</script>

</body>
</html>
