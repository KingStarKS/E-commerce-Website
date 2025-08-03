<?php
session_start();
// include __DIR__ . '/../../includes/db.php';
include '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage-orders.php');
    exit;
}

$order_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    $update = "UPDATE orders SET status='$new_status' WHERE id=$order_id";
    mysqli_query($conn, $update);
    header('Location: manage-orders.php');
    exit;
}

$query = "SELECT * FROM orders WHERE id=$order_id";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Order Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h2>Update Order #<?= $order_id ?> Status</h2>
    <form method="POST">
        <label for="status">Order Status:</label>
        <select name="status" class="form-select" required>
            <option value="pending" <?= $order['STATUS'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= $order['STATUS'] == 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="shipped" <?= $order['STATUS'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= $order['STATUS'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= $order['STATUS'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <br>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="manage-orders.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
