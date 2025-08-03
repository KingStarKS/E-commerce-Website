<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit;
}

if (!isset($_GET['order_id'])) {
    echo "Order ID is missing.";
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Step 1: Check if this order belongs to the logged-in user
$sql = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    echo "Invalid order ID.";
    exit;
}

$order = mysqli_fetch_assoc($result);

// Step 2: Fetch order items
$sql_items = "SELECT oi.*, p.name FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = $order_id";
$result_items = mysqli_query($conn, $sql_items);

// Step 3: Fetch user info (name, email)
$sql_user = "SELECT username, email FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Confirmation #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
?>

<div class="container mt-5">
    <h2 class="mb-4">Thank you for your purchase!</h2>
    <p>Your order #<strong><?php echo $order_id; ?></strong> has been confirmed.</p>

    <div class="mb-4">
        <h5>Customer Details</h5>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            while ($item = mysqli_fetch_assoc($result_items)) {
                $total = $item['price'] * $item['quantity'];
                $grand_total += $total;
                echo "<tr>
                        <td>" . htmlspecialchars($item['name']) . "</td>
                        <td>{$item['quantity']}</td>
                        <td>$" . number_format($item['price'], 2) . "</td>
                        <td>$" . number_format($total, 2) . "</td>
                      </tr>";
            }
            ?>
            <tr>
                <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                <td><strong>$<?php echo number_format($grand_total, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <button class="btn btn-outline-primary" onclick="window.print()">🖨️ Print Invoice</button>
    <a href="orders.php" class="btn btn-primary ms-2">Back to Orders</a>
    <a href="products.php" class="btn btn-secondary ms-2">Continue Shopping</a>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
