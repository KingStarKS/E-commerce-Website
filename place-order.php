<?php
session_start();
include 'includes/db.php';

// For simplicity, we use user_id = 1 (replace with actual logged-in user ID)
$user_id = 1;

if (empty($_SESSION['cart'])) {
    echo "Your cart is empty. <a href='products.php'>Go shopping</a>.";
    exit;
}

// Calculate total
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Insert order
$sql_order = "INSERT INTO orders (user_id, total_price) VALUES (?, ?)";
$stmt = $conn->prepare($sql_order);
$stmt->bind_param("id", $user_id, $total_price);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert order items
$sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
$stmt_item = $conn->prepare($sql_item);

foreach ($_SESSION['cart'] as $product_id => $item) {
    $stmt_item->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
    $stmt_item->execute();
}

$stmt_item->close();

// Clear cart
$_SESSION['cart'] = [];

echo "<h2>Order placed successfully!</h2>";
echo "<p>Thank you for your purchase.</p>";
echo "<a href='products.php'>Continue Shopping</a>";
?>
