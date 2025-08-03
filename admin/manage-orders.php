<?php
session_start();
include '../includes/db.php';

// Admin protection
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Get all orders (join with users to show customer info)
$query = "SELECT orders.id, orders.user_id, orders.total, orders.STATUS, orders.created_at, users.username AS customer 
          FROM orders 
          JOIN users ON orders.user_id = users.id 
          ORDER BY orders.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2>All Orders</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total ($)</th>
                <th>Status</th>
                <th>Placed On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($order = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['customer']) ?></td>
                <td><?= $order['total'] ?></td>
                <td><?= ucfirst($order['STATUS']) ?></td>
                <td><?= $order['created_at'] ?></td>
                <td>
                    <a href="update-order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">Update</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
