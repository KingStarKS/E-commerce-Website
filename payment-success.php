<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = null;

// Step 1: Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (user_id, order_date) VALUES (?, NOW())");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create order']);
    exit;
}

// Step 2: Insert into order_items
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    $res = mysqli_query($conn, "SELECT price FROM products WHERE id = $product_id");
    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $price = $row['price'];

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
    }
}

// Step 3: Clear cart
unset($_SESSION['cart']);

// Step 4: Return success with order ID
echo json_encode(['success' => true, 'order_id' => $order_id]);
?>
